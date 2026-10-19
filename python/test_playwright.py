print("STEP 1: Python berhasil dijalankan", flush=True)

import asyncio
print("STEP 2: asyncio berhasil", flush=True)

from playwright.async_api import async_playwright
print("STEP 3: Playwright berhasil di-import", flush=True)

async def main():

    print("STEP 4: Mulai Playwright", flush=True)
#
    async with async_playwright() as p:
#
        print("STEP 5: Playwright aktif", flush=True)
#
        browser = await p.chromium.launch(
            headless=False
        )
#
        print("STEP 6: Browser berhasil dibuka", flush=True)
#
        page = await browser.new_page()

        print("STEP 7: Page berhasil dibuat", flush=True)#
        await page.goto(
            "https://www.tiktok.com",
            timeout=60000
        )
#
        print("STEP 8: TikTok berhasil dibuka", flush=True)

        await asyncio.sleep(10)
#
        await browser.close()

        print("STEP 9: Browser ditutup", flush=True)

asyncio.run(main())