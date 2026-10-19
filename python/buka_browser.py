import asyncio
import json
import random
import re
import sys
import time
import urllib.parse

from playwright.async_api import (
    async_playwright,
    TimeoutError as PlaywrightTimeoutError,
    TargetClosedError,
)


# Batas proses harus lebih rendah dari timeout proxy 120 detik.
BATAS_WAKTU_PROSES = 100
MAX_SCROLL_LIMIT = 100


def kirim_json(status, message, data=None, **tambahan):
    response = {
        "status": status,
        "message": message,
        "data": data or [],
        **tambahan,
    }

    sys.stdout.reconfigure(encoding="utf-8")
    print(json.dumps(response, ensure_ascii=False))


def log_error(message):
    print(message, file=sys.stderr, flush=True)


def ubah_format_angka(teks_angka):
    if not teks_angka:
        return 0

    teks = (
        str(teks_angka)
        .strip()
        .upper()
        .replace(",", ".")
        .replace(" ", "")
    )

    try:
        if "K" in teks:
            return int(float(teks.replace("K", "")) * 1_000)

        if "M" in teks:
            return int(float(teks.replace("M", "")) * 1_000_000)

        if "B" in teks:
            return int(float(teks.replace("B", "")) * 1_000_000_000)

        return int(float(teks))

    except (ValueError, TypeError):
        return 0


def cek_indikator_indonesia(bio, nickname):
    teks_gabungan = f"{bio or ''} {nickname or ''}".lower()

    indikator_indonesia = [
        "indonesia",
        "jakarta",
        "bandung",
        "surabaya",
        "medan",
        "bali",
        "jawa",
        "jabar",
        "jateng",
        "jatim",
        "sumatera",
        "sulawesi",
        "kalimantan",
        "indo",
        "endorse",
        "pemesanan",
        "shopee",
        "tiktokshop",
        "+62",
    ]

    return any(
        indikator in teks_gabungan
        for indikator in indikator_indonesia
    )


def deteksi_kategori(bio, nickname):
    teks = f"{bio or ''} {nickname or ''}".lower()

    kategori = {
        "Beauty & Healthy": [
            "beauty",
            "healthy",
            "health",
            "vitamin",
            "supplement",
            "wellness",
            "nutrition",
        ],
        "Bodycare": [
            "bodycare",
            "body care",
            "body lotion",
            "soap",
            "sabun",
            "parfum",
            "perfume",
            "deodorant",
            "handbody",
        ],
        "Fashion": [
            "fashion",
            "ootd",
            "outfit",
            "style",
            "dress",
            "hijab",
            "baju",
            "sepatu",
            "tas",
        ],
        "Food & Beverages": [
            "food",
            "kuliner",
            "makan",
            "snack",
            "coffee",
            "kopi",
            "tea",
            "minuman",
            "drink",
            "recipe",
            "resep",
            "masak",
        ],
        "Hair care": [
            "hair",
            "haircare",
            "hair care",
            "shampoo",
            "conditioner",
            "hair tonic",
            "hair oil",
            "rambut",
        ],
        "Lifestyle": [
            "daily",
            "vlog",
            "lifestyle",
            "travel",
            "travelling",
            "review",
            "tips",
        ],
        "Mom & Kids": [
            "mom",
            "mama",
            "mommy",
            "ibu",
            "baby",
            "anak",
            "kids",
            "parenting",
        ],
        "Skincare": [
            "skincare",
            "skin care",
            "serum",
            "toner",
            "facial wash",
            "cleanser",
            "moisturizer",
            "suncreen",
            "sunscreen",
            "acne",
            "glowing",
        ],
        "Sport & Outdoor": [
            "gym",
            "fitness",
            "workout",
            "running",
            "cycling",
            "basket",
            "football",
            "badminton",
            "sport",
            "outdoor",
            "hiking",
        ],
    }

    skor = {
        nama: sum(1 for kata in kata_kunci if kata in teks)
        for nama, kata_kunci in kategori.items()
    }

    hasil = max(skor, key=skor.get)

    if skor[hasil] == 0:
        return "Lifestyle"

    return hasil


def deteksi_domisili(teks):
    daftar_kota = [
        "Jakarta",
        "Bandung",
        "Bogor",
        "Bekasi",
        "Depok",
        "Tangerang",
        "Surabaya",
        "Semarang",
        "Yogyakarta",
        "Solo",
        "Malang",
        "Medan",
        "Makassar",
        "Palembang",
        "Bali",
    ]

    teks = (teks or "").lower()

    for kota in daftar_kota:
        if kota.lower() in teks:
            return kota

    return "-"


def estimasi_gmv(follower, likes=0):
    aov_produk = 50_000

    if likes > 0:
        perkiraan_terjual = int(likes * 0.008)
    else:
        perkiraan_terjual = int(follower * 0.02)

    if perkiraan_terjual < 10 and follower > 1_000:
        perkiraan_terjual = int(follower * 0.01)

    return max(0, perkiraan_terjual * aov_produk)


def hitung_engagement_rate(total_likes, followers):
    if followers <= 0:
        return 0.0

    estimasi_er = (total_likes / followers) * 100
    perkiraan_jumlah_video = 30
    er_per_post = estimasi_er / perkiraan_jumlah_video

    return round(min(er_per_post, 25.0), 2)


async def ambil_text(page, selector, default=""):
    try:
        locator = page.locator(selector)

        if await locator.count() == 0:
            return default

        return (await locator.first.inner_text(timeout=3_000)).strip()

    except Exception:
        return default


async def tutup_popup(page):
    if page.is_closed():
        return

    selectors = [
        "button[aria-label='Close']",
        "[data-e2e='modal-close-inner-button']",
        "button:has-text('Not now')",
        "button:has-text('Close')",
        "button:has-text('Tutup')",
        "svg[class*='close']",
    ]

    for selector in selectors:
        try:
            locator = page.locator(selector)

            if await locator.count() > 0:
                await locator.first.click(timeout=1_000)
                await page.wait_for_timeout(500)
                return

        except Exception:
            continue


async def scroll_ke_bawah_otomatis(page):
    if page.is_closed():
        return

    try:
        await page.evaluate(
            """
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'instant'
            });
            """
        )

        await page.mouse.wheel(0, 2_500)
        await page.wait_for_timeout(1_500)

    except Exception as error:
        log_error(f"Gagal melakukan scroll: {error}")


async def buka_halaman_pencarian(page, search_url):
    try:
        await page.goto(
            search_url,
            wait_until="domcontentloaded",
            timeout=45_000,
        )

        await page.wait_for_timeout(4_000)
        await tutup_popup(page)

        return True

    except PlaywrightTimeoutError:
        if page.is_closed():
            raise RuntimeError(
                "Halaman TikTok tertutup ketika navigasi mengalami timeout."
            )

        try:
            html = await page.content()

            if len(html) < 1_000:
                raise RuntimeError(
                    "TikTok tidak memberikan konten halaman yang memadai."
                )

            log_error(
                "Navigasi pencarian timeout, tetapi halaman sudah memiliki "
                "konten. Proses tetap dilanjutkan."
            )

            return True

        except TargetClosedError as error:
            raise RuntimeError(
                f"Halaman TikTok tertutup setelah timeout: {error}"
            )

    except TargetClosedError as error:
        raise RuntimeError(
            f"Browser atau halaman TikTok tertutup tiba-tiba: {error}"
        )


async def cari_username_baru(page, username_sudah_dicek):
    if page.is_closed():
        return None

    try:
        links = page.locator("a[href*='/@']")
        jumlah_link = await links.count()

        # Batasi jumlah link yang diperiksa dalam sekali iterasi.
        jumlah_link = min(jumlah_link, 200)

        for index in range(jumlah_link):
            try:
                href = await links.nth(index).get_attribute("href")

                if not href:
                    continue

                match = re.search(
                    r"/@([a-zA-Z0-9_.-]+)",
                    href,
                )

                if not match:
                    continue

                username = match.group(1)

                if username not in username_sudah_dicek:
                    return username

            except Exception:
                continue

    except TargetClosedError:
        return None

    except Exception as error:
        log_error(f"Gagal membaca daftar username: {error}")

    return None


async def proses_profile(
    context,
    profile_page,
    username,
    min_follower,
    max_follower,
):
    profile_url = f"https://www.tiktok.com/@{username}"

    if profile_page is None or profile_page.is_closed():
        profile_page = await context.new_page()

    try:
        await profile_page.goto(
            profile_url,
            wait_until="domcontentloaded",
            timeout=15_000,
        )

        await profile_page.wait_for_timeout(1_500)
        await tutup_popup(profile_page)

        nickname = await ambil_text(
            profile_page,
            "[data-e2e='user-title']",
            "N/A",
        )

        followers_str = await ambil_text(
            profile_page,
            "[data-e2e='followers-count']",
            "0",
        )

        likes_str = await ambil_text(
            profile_page,
            "[data-e2e='likes-count']",
            "0",
        )

        bio = await ambil_text(
            profile_page,
            "[data-e2e='user-bio']",
            "",
        )

        jumlah_follower = ubah_format_angka(followers_str)
        jumlah_likes = ubah_format_angka(likes_str)

        is_indonesia = cek_indikator_indonesia(
            bio,
            nickname,
        )

        is_follower_ok = (
            min_follower
            <= jumlah_follower
            <= max_follower
        )

        if not is_indonesia or not is_follower_ok:
            return profile_page, None

        engagement_rate = hitung_engagement_rate(
            jumlah_likes,
            jumlah_follower,
        )

        kategori = deteksi_kategori(
            bio,
            nickname,
        )

        domisili = deteksi_domisili(
            f"{nickname} {bio}"
        )

        gmv = estimasi_gmv(
            jumlah_follower,
            jumlah_likes,
        )

        data_baru = {
            "Username": username,
            "Profile URL": profile_url,
            "Nama": nickname,
            "Bio": bio,
            "Followers": jumlah_follower,
            "Total Likes": jumlah_likes,
            "ER": engagement_rate,
            "Kategori": kategori,
            "GMV": gmv,
            "Domisili": domisili,
        }

        return profile_page, data_baru

    except PlaywrightTimeoutError:
        log_error(f"Timeout membuka profil @{username}")
        return profile_page, None

    except TargetClosedError:
        log_error(f"Halaman profil @{username} tertutup tiba-tiba")

        try:
            profile_page = await context.new_page()
        except Exception:
            profile_page = None

        return profile_page, None

    except Exception as error:
        log_error(
            f"Gagal memproses profil @{username}: {error}"
        )

        return profile_page, None


async def main():
    try:
        raw_input = sys.stdin.read()
        payload = json.loads(raw_input) if raw_input.strip() else {}

    except json.JSONDecodeError as error:
        kirim_json(
            "error",
            f"Payload JSON tidak valid: {error}",
        )
        return

    except Exception as error:
        kirim_json(
            "error",
            f"Gagal membaca payload dari STDIN: {error}",
        )
        return

    try:
        keyword_input = str(
            payload.get("keyword", "skincare")
        ).strip()

        target_kandidat = max(
            1,
            int(payload.get("target", 10)),
        )

        min_follower = max(
            0,
            int(payload.get("minimal_follower", 5_000)),
        )

        max_follower = max(
            min_follower,
            int(payload.get("maximal_follower", 100_000)),
        )

    except (ValueError, TypeError) as error:
        kirim_json(
            "error",
            f"Parameter pencarian tidak valid: {error}",
        )
        return

    encoded_query = urllib.parse.quote(
        keyword_input,
        safe="",
    )

    search_url = (
        f"https://www.tiktok.com/search?q={encoded_query}"
    )

    data_lolos_filter = []
    username_sudah_dicek = set()
    scroll_count = 0
    waktu_mulai = time.monotonic()

    try:
        async with async_playwright() as playwright:
            browser = await playwright.chromium.launch(
                headless=True,
                args=[
                    "--no-sandbox",
                    "--disable-setuid-sandbox",
                    "--disable-dev-shm-usage",
                    "--disable-gpu",
                    "--disable-blink-features=AutomationControlled",
                ],
            )

            context = await browser.new_context(
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/131.0.0.0 Safari/537.36"
                ),
                viewport={
                    "width": 1280,
                    "height": 800,
                },
                locale="id-ID",
                timezone_id="Asia/Jakarta",
            )

            context.set_default_timeout(8_000)

            search_page = await context.new_page()
            profile_page = await context.new_page()

            await buka_halaman_pencarian(
                search_page,
                search_url,
            )

            while (
                len(data_lolos_filter) < target_kandidat
                and scroll_count < MAX_SCROLL_LIMIT
            ):
                durasi = time.monotonic() - waktu_mulai

                if durasi >= BATAS_WAKTU_PROSES:
                    break

                if search_page.is_closed():
                    log_error(
                        "Halaman pencarian tertutup. Proses dihentikan."
                    )
                    break

                username_target = await cari_username_baru(
                    search_page,
                    username_sudah_dicek,
                )

                if not username_target:
                    scroll_count += 1

                    await scroll_ke_bawah_otomatis(
                        search_page
                    )

                    await tutup_popup(search_page)
                    continue

                username_sudah_dicek.add(
                    username_target
                )

                profile_page, data_baru = await proses_profile(
                    context=context,
                    profile_page=profile_page,
                    username=username_target,
                    min_follower=min_follower,
                    max_follower=max_follower,
                )

                if data_baru:
                    data_lolos_filter.append(data_baru)

                if (
                    len(data_lolos_filter)
                    >= target_kandidat
                ):
                    break

                await asyncio.sleep(
                    random.uniform(0.5, 1.0)
                )

            await browser.close()

    except Exception as error:
        durasi = round(
            time.monotonic() - waktu_mulai,
            2,
        )

        # Tetap kembalikan JSON agar koneksi Laravel/n8n
        # tidak menerima respons kosong.
        kirim_json(
            "error",
            f"Proses pencarian gagal: {error}",
            data_lolos_filter,
            partial=bool(data_lolos_filter),
            duration_seconds=durasi,
        )
        return

    durasi = round(
        time.monotonic() - waktu_mulai,
        2,
    )

    mencapai_batas_waktu = (
        durasi >= BATAS_WAKTU_PROSES
    )

    if mencapai_batas_waktu:
        message = (
            f"Proses dihentikan pada batas waktu. "
            f"Berhasil mendapatkan "
            f"{len(data_lolos_filter)} kandidat."
        )
    else:
        message = (
            f"Berhasil mendapatkan "
            f"{len(data_lolos_filter)} kandidat affiliator."
        )

    kirim_json(
        "success",
        message,
        data_lolos_filter,
        partial=mencapai_batas_waktu,
        duration_seconds=durasi,
        checked_users=len(username_sudah_dicek),
        target=target_kandidat,
    )


if __name__ == "__main__":
    asyncio.run(main())