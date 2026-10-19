# ERP Manufacture

Aplikasi ERP untuk alur kerja manufaktur dan percetakan digital. Aplikasi ini menghubungkan penjualan, quotation, persediaan gudang, purchase order, material requisition, SPK, produksi/manufaktur, serta proses keuangan dalam satu aplikasi web.

Dokumentasi ini dibuat berdasarkan implementasi yang ada di repository saat ini.

## Fitur Utama

- Login, logout, session, dan pembatasan data berdasarkan cabang.
- Dashboard: jumlah purchase order, barang masuk hari ini, total stok, dan purchase order terbaru.
- Manajemen user, role, dan cabang.
- Master data barang dan stok gudang.
- Purchase order: tambah, cari, ubah, hapus, dan cetak.
- Quotation/penawaran: item barang, customer, cabang, jumlah, total, terms, status, approve/reject, request plat, kode film, dan PDF.
- Pemeriksaan stok saat membuat quotation.
- Material requisition beserta item bahan, approval, rejection, dan transaksi.
- Alur SPK lintas gudang, produksi, manufaktur, dan keuangan.
- Invoice, faktur, surat jalan, quotation, dan dokumen SPK dalam PDF.
- Pembayaran dan pengambilan detail invoice melalui API keuangan.
- Laporan hasil jadi dan waste untuk manufaktur.
- Endpoint JSON untuk frontend dan integrasi sistem lain.
- Event RequestPlatUpdated untuk pembaruan request plat.
- Integrasi Python/Playwright untuk pencarian affiliator.

## Peran Pengguna

| Role | Akses utama |
| --- | --- |
| admin | Dashboard, penjualan, gudang, user/cabang, requisition, dan akuntansi |
| penjualan | Purchase order dan area penjualan |
| sales | Quotation dan area penjualan |
| pricing | Quotation dan area penjualan |
| pracetak | Quotation dan proses pra-cetak |
| gudang | Stok gudang, request plat, dan SPK gudang |
| production | SPK produksi |
| manufacture | SPK manufaktur |
| akuntansi | Transaksi dan SPK keuangan |

Data user non-pusat biasanya difilter dengan nilai cabang user yang sedang login. User dengan cabang Pusat dapat melihat data lintas cabang pada modul yang mendukungnya.

## Alur Bisnis

1. Sales/penjualan membuat quotation.
2. Sistem dapat mengecek stok warehouse.
3. Quotation dikirim, disetujui, atau ditolak.
4. Jika diperlukan, quotation membuat request plat/film untuk gudang.
5. Gudang menyetujui request dan sistem membentuk kode master film.
6. Quotation dapat dikirim menjadi SPK gudang.
7. Gudang menerima dan meneruskan SPK ke produksi atau proses berikutnya.
8. Produksi menetapkan PIC, menerima SPK, lalu meneruskannya.
9. Manufaktur mencatat hasil jadi dan waste.
10. Keuangan menerima SPK, menghasilkan nomor invoice, memproses pembayaran, dan mencetak invoice/surat jalan.

Material requisition dibuat dengan item barang dari gudang, lalu diproses melalui approval atau rejection dan dicatat sebagai transaksi terkait.

## Teknologi

- PHP 8.2+
- Laravel 12
- Laravel Sanctum 4
- PostgreSQL
- Blade, Tailwind CSS, dan Vite 8
- Dompdf untuk PDF
- Python dan Playwright untuk pencarian affiliator
- Docker dan Nginx untuk deployment

## Struktur Project

~~~text
app/                    Event, controller, model, dan provider
bootstrap/              Bootstrap Laravel
config/                 Konfigurasi aplikasi
database/               Factory, migration, dan seeder
public/                 Entry point, JavaScript publik, dan asset statis
python/                 Integrasi Python/Playwright
resources/views/        Blade view dan template PDF
routes/web.php          Route web dan endpoint internal JSON
routes/api.php          API berprefix /api
tests/                  Unit test dan feature test
~~~

Controller utama: UserController, warehouseController, SalesController, QuotationController, PurchaseOrderController, RequisitionController, SPKController, dan FinanceController.

## Persyaratan

- PHP ^8.2
- Composer
- Node.js dan npm
- PostgreSQL
- Ekstensi PHP: pdo, pdo_pgsql, pgsql, dan gd
- Python sesuai kebutuhan integrasi affiliator
- Browser Playwright jika endpoint affiliator digunakan

## Instalasi Lokal

~~~bash
git clone <url-repository>
cd erp_manufacture
composer install
copy .env.example .env       # Windows
# cp .env.example .env       # Linux/macOS
php artisan key:generate
npm install
~~~

Buat database PostgreSQL lalu isi koneksi database pada .env:

~~~dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=manufacture
DB_USERNAME=root
DB_PASSWORD=
~~~

Jalankan migrasi dan, bila diperlukan, seeder:

~~~bash
php artisan migrate
php artisan db:seed
~~~

.env tidak boleh di-commit karena dapat berisi password dan secret.

## Menjalankan Aplikasi

Development lengkap:

~~~bash
composer run dev
~~~

Script tersebut menjalankan server Laravel, queue listener, dan Vite. Alternatif manual:

~~~bash
php artisan serve
npm run dev
~~~

Akses default: http://127.0.0.1:8000.

Build asset production:

~~~bash
npm run build
~~~

## Docker

Project menyediakan Dockerfile, docker-compose.yml, dan konfigurasi Nginx.

- app: PHP-FPM 8.2 dengan ekstensi PostgreSQL dan GD.
- web: Nginx pada port host 8001.

~~~bash
docker compose build
docker compose up -d
~~~

Pastikan network Docker eksternal erp_lmg_laravel sudah tersedia dan database dapat diakses dari container. Aplikasi tersedia pada http://localhost:8001.

## Modul Aplikasi

### Dashboard

Route /dashboard menampilkan ringkasan PO, stok masuk hari ini, total jumlah stok, dan lima PO terbaru. Data difilter berdasarkan cabang kecuali user berada di cabang Pusat.

### User dan cabang

Route resource /users menyediakan daftar user dengan pencarian nama/email, filter role dan cabang, pagination, tambah, ubah, dan hapus. Password disimpan menggunakan hash Laravel.

### Warehouse

Route resource /warehouse menyediakan daftar stok, pencarian barang, filter cabang, tambah barang, ubah jumlah/harga/satuan, dan hapus barang. Tersedia pula halaman request plat dan SPK gudang.

### Sales dan quotation

Route resource /sales mengelola data sales. Route resource /quotations menangani quotation, approval/rejection, pemeriksaan stok, request plat, pembuatan film, pengiriman quotation, dan export PDF.

### Purchase order

Route resource /purchase-order mengelola PO dan detail barang. Dokumen yang tersedia: PO, quotation, invoice, faktur, dan surat jalan.

### Material requisition dan transaksi

Route /requisition menangani permintaan material, item, approval, rejection, update, delete, dan transaksi. Halaman /transactions menampilkan transaksi keuangan.

### SPK

SPK dibagi menjadi:

- spk_warehouse: SPK gudang.
- spk_production: SPK produksi dengan PIC.
- spk_manufacture: SPK manufaktur.
- spk_finance: SPK keuangan dan nomor invoice.
- laporan_manufacture: hasil jadi dan waste.

Setiap tahap memiliki status dan endpoint accept/cancel yang relevan. Daftar SPK aktif, seluruh SPK, dan jumlah pending difilter berdasarkan role serta cabang.

### Keuangan

FinanceController menyediakan detail invoice/SPK dan pencatatan pembayaran. SPK finance dapat menghasilkan invoice pelanggan dan surat jalan dalam PDF.

## Endpoint Utama

### Web

| Method | Path | Keterangan |
| --- | --- | --- |
| GET | / | Halaman login |
| POST | /login | Login |
| POST | /logout | Logout |
| GET | /dashboard | Dashboard |
| GET/POST/PUT/DELETE | /users, /warehouse, /sales, /requisition, /quotations, /purchase-order | Resource CRUD |
| GET | /transactions | Daftar transaksi |
| GET | /purchase-order/{id}/print | Cetak PO |
| GET | /quotations/{id}/pdf | Cetak quotation |
| GET | /gudang/spk/{spk}/pdf | PDF SPK gudang |
| GET | /production/spk/{spk}/pdf | PDF SPK produksi |
| GET | /finance/spk/{spk}/pdf | PDF SPK finance |
| GET | /invoice/spk/{spk}/pdf | PDF invoice pelanggan |
| GET | /finance/surat-jalan-pdf/{spk} | PDF surat jalan |

### API (routes/api.php)

Base URL: /api.

| Method | Path | Keterangan |
| --- | --- | --- |
| GET | /user | User terautentikasi via Sanctum |
| GET | /quotations/{cabang} | Quotation berdasarkan cabang |
| GET | /inventories/{cabang} | Inventory berdasarkan cabang |
| POST | /warehouse/spk/{id} | Kirim quotation menjadi SPK gudang |
| POST | /production/spk/{id} | Kirim ke produksi |
| POST | /finance/spk/{id} | Kirim ke finance |
| GET | /users/production | Daftar user produksi berdasarkan cabang |
| GET | /finance/spk-detail/{id} | Detail invoice/SPK finance |
| POST | /finance/payment/{id} | Catat pembayaran |
| POST | /spk-manufacture/laporan/{id} | Kirim laporan manufaktur |
| POST | /cari-affiliator | Jalankan pencarian affiliator melalui Python |

Endpoint internal tambahan terdapat di routes/web.php dengan prefix /api/gudang, /api/production, /api/finance, dan /api/manufacture. Endpoint tersebut dipakai halaman Blade dan sebagian besar memakai middleware auth.

## Database

Migrasi utama membuat:

- users, sessions, cache, jobs, personal_access_tokens
- warehouse
- purchase_order, purchase_order_detail
- sales
- quotations
- material_requisitions, requisition_items
- transactions
- request_plat
- spk_warehouse, spk_production, spk_finance, spk_manufacture
- laporan_manufacture

Relasi penting menggunakan foreign key dan beberapa relasi memakai cascade delete. Jalankan migrasi pada database kosong sebelum menggunakan modul aplikasi.

## PDF dan Dokumen

Template berada di resources/views/pdf dan resources/views/spk. Pembuatan PDF menggunakan barryvdh/laravel-dompdf. Dokumen di-stream ke browser melalui endpoint terkait dan tidak disimpan permanen secara default.

## Integrasi Python

Endpoint POST /api/cari-affiliator menjalankan python/buka_browser.py menggunakan Python dan Playwright.

Payload:

~~~json
{
  "keyword": "skincare",
  "minimal_follower": 5000,
  "maximal_follower": 100000,
  "target": 100
}
~~~

Validasi memastikan maximal_follower tidak lebih kecil dari minimal_follower dan target minimal bernilai 1. Pada deployment Linux, controller saat ini mengharapkan executable /var/www/venv/bin/python dan script /var/www/python/buka_browser.py; sesuaikan path jika deployment berbeda.

Jangan commit credential atau data login. File python/credentials.json harus dikelola sebagai secret deployment dan ditinjau sebelum project dipublikasikan.

## Testing

~~~bash
php artisan test
composer run test
~~~

Test bawaan berada di tests/Unit dan tests/Feature. Tambahkan test untuk workflow, authorization role, filter cabang, transisi status, dan pembuatan dokumen.

## Catatan Pengembangan

- Periksa routes/web.php dan routes/api.php ketika menambah atau mengubah API.
- Gunakan php artisan route:list untuk melihat route aktual.
- Setelah mengubah konfigurasi, jalankan php artisan optimize:clear.
- Untuk queue database, pastikan tabel queue sudah dimigrasikan dan jalankan php artisan queue:listen.
- Production: set APP_DEBUG=false, gunakan secret aman, aktifkan HTTPS, batasi akses API, dan jangan expose .env.
- Penamaan warehouseController diawali huruf kecil; pertahankan konsistensi atau lakukan refactor terencana agar kompatibel lintas filesystem.

## Lisensi

Project menggunakan struktur Laravel dan dependensi sesuai composer.json. Lisensi aplikasi internal mengikuti kebijakan pemilik project.

