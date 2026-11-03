# Katalog TEFA

Katalog TEFA adalah aplikasi untuk mempromosikan produk Teaching Factory. Pengguna harus masuk untuk melihat katalog, membuat katalog, dan melaporkan produk. Admin dapat meninjau laporan dan mengelola katalog.

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`, lalu isi `APP_KEY` dengan menjalankan `php artisan key:generate`.
2. Jalankan `composer install` dan `npm install`.
3. Jalankan migrasi database dengan `php artisan migrate`.
4. Buat tautan penyimpanan foto dengan `php artisan storage:link`.
5. Jalankan `npm run build`, lalu `php artisan serve`.

Untuk menjalankan mode pengembangan dengan Vite, gunakan `npm run dev` di terminal terpisah.

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
