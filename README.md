# Katalog TEFA

Katalog TEFA adalah aplikasi untuk mempromosikan produk Teaching Factory. Pengguna harus masuk untuk melihat katalog, membuat katalog, dan melaporkan produk. Admin dapat meninjau laporan dan mengelola katalog.

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`, lalu isi `APP_KEY` dengan menjalankan `php artisan key:generate`.
2. Jalankan `composer install` dan `npm install`.
3. Jalankan migrasi database dengan `php artisan migrate`.
4. Buat tautan penyimpanan foto dengan `php artisan storage:link`.
5. Jalankan `npm run build`, lalu `php artisan serve`.

Untuk menjalankan mode pengembangan dengan Vite, gunakan `npm run dev` di terminal terpisah.

## Deploy ke Vercel

Proyek ini menggunakan Laravel, jadi deploy harus memakai **Container Runtime**, bukan output situs statis `dist`. `Dockerfile.vercel`, `Caddyfile`, dan `vercel.json` menyiapkan PHP/FrankenPHP, build aset Vite, dan mengarahkan request ke Laravel.

Pada project Vercel yang sudah ada:

1. Pastikan **Root Directory** menunjuk ke folder repo ini.
2. Atur **Framework Preset** ke **Container**. Hapus override **Output Directory** `dist` jika masih terpasang.
3. Tambahkan environment variables:
   - `APP_KEY` — buat satu kali dengan `php artisan key:generate --show`; simpan nilainya sebagai secret di Vercel.
   - `APP_URL` — URL deployment.
   - `APP_ENV=production`, `APP_DEBUG=false`, `LOG_CHANNEL=stderr`.
   - `DB_CONNECTION=pgsql` dan `DB_URL` dari database PostgreSQL terkelola.
   - `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`.
   - `FILESYSTEM_DISK=s3` dan kredensial bucket S3-compatible. Untuk Cloudflare R2, isi `AWS_ENDPOINT`, `AWS_URL` (domain publik bucket), `AWS_BUCKET`, `AWS_DEFAULT_REGION=auto`, `AWS_USE_PATH_STYLE_ENDPOINT=true`, `AWS_ACCESS_KEY_ID`, dan `AWS_SECRET_ACCESS_KEY`.
4. Jalankan migrasi pada database deployment sebagai langkah release terpisah: `php artisan migrate --force`. Jangan menjalankan migrasi saat container mulai, karena beberapa instance bisa hidup bersamaan.
5. Push perubahan ke branch Git yang terhubung untuk membuat deployment preview.

SQLite dan disk lokal tetap digunakan untuk pengembangan lokal. File pada filesystem container Vercel tidak persisten, jadi jangan gunakan disk lokal untuk foto produksi. Jangan simpan kredensial produksi atau `.env` di Git.

## Membuat akun admin

Isi nilai berikut di `.env` sebelum menjalankan seeder:

```dotenv
ADMIN_NAME="Admin Katalog"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD="gunakan-kata-sandi-yang-kuat"
```

Jalankan `php artisan db:seed`. Pendaftaran pengguna biasa tidak dapat memberikan hak admin. Admin masuk melalui halaman login yang sama, lalu dapat membuka dashboard admin dari navigasi.

## Fitur

- Login diperlukan untuk melihat katalog, membuat katalog, atau melapor.
- Katalog mendukung hingga 8 foto per produk dan foto dapat ditambah atau dihapus saat edit.
- Galeri dengan lebih dari 2 foto berganti otomatis setiap 4 detik dan memiliki kontrol manual.
- Penjual dapat membagikan titik lokasi saat ini dengan izin browser atau menuliskan alamat lengkap; tombol lokasi pada katalog membuka titik/alamat tersebut di Google Maps.
- Admin dapat menandai laporan sebagai ditinjau atau ditolak, serta menghapus katalog.
