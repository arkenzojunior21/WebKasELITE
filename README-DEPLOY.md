# KasELITE — paket awal deploy Vercel

Paket ini memindahkan endpoint PHP ke `api/`, menambahkan runtime PHP komunitas Vercel, dan mengganti kredensial database yang ditulis langsung di kode dengan environment variables.

## PENTING — jangan langsung publikasikan

Versi sumber saat ini tidak memiliki halaman login/autentikasi. Siapa pun yang mengetahui URL berpotensi membuka data anggota dan mengubah status pembayaran. Tambahkan autentikasi dan proteksi seluruh endpoint mutasi sebelum digunakan secara publik. File SQL sumber juga berisi data pribadi anggota; jangan commit atau membagikannya secara publik.

## 1. Siapkan database MySQL online

Vercel menjalankan PHP sebagai fungsi serverless, bukan server PHP/MySQL tradisional. Siapkan database MySQL/MariaDB terkelola yang menerima koneksi dari aplikasi Vercel. Jangan mengandalkan database lokal di komputer. Pastikan provider mendukung koneksi TLS bila diwajibkan dan mengizinkan koneksi dari deployment Vercel.

Buat database baru dan tabel berdasarkan skema `anggota`, `minggu_kas`, dan `pembayaran` dari `db_kaselite.sql` milikmu. Untuk keamanan, import skema saja dahulu; jangan mengimpor data pribadi anggota ke database publik tanpa izin dan kebutuhan yang jelas. Jangan jalankan `DROP DATABASE` dari dump lama pada database yang sudah berisi data.

## 2. Tambahkan environment variables di Vercel

Di project Vercel buka **Settings → Environment Variables** lalu tambahkan:

- `DB_HOST` — host database dari provider
- `DB_PORT` — biasanya `3306`
- `DB_NAME` — nama database
- `DB_USER` — username database
- `DB_PASSWORD` — password database

Jangan masukkan password ke kode, `vercel.json`, Git, atau chat. Setelah menambahkan variabel, redeploy project.

## 3. Deploy

1. Ekstrak ZIP ini.
2. Masukkan folder project ke repository GitHub privat.
3. Di Vercel pilih **Add New → Project**, import repository tersebut.
4. Framework Preset: **Other**. Root Directory: folder yang berisi `vercel.json`.
5. Tambahkan environment variables pada langkah sebelumnya.
6. Deploy, lalu periksa halaman utama dan semua menu.

Runtime PHP yang dipakai adalah community runtime `vercel-php@0.9.0`, bukan runtime PHP resmi Vercel. Lihat dokumentasi runtime di https://github.com/vercel-community/php dan konfigurasi runtime Vercel di https://vercel.com/docs/project-configuration/vercel-json.

## Batasan yang diketahui

- Paket ini belum diuji dengan akun Vercel/database milikmu; deploy belum dilakukan dari sini.
- Autentikasi belum ditambahkan; jangan gunakan sebagai aplikasi kas publik sebelum diperbaiki.
- `kirim_wa.php` hanya membuat pesan dan membuka tautan WhatsApp (`wa.me`); itu bukan pengiriman otomatis melalui WhatsApp API.
- Database harus online dan dapat diakses dari runtime serverless. Koneksi ke database lama mungkin ditolak provider.
- Periksa seluruh fitur, hak akses, dan data setelah deployment; jangan gunakan data pribadi asli untuk pengujian publik.
