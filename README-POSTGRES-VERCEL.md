# KasELITE — PostgreSQL / Neon + Vercel

## Apa yang berubah
- Koneksi MySQL hard-coded diganti dengan `DATABASE_URL` dari Neon.
- Koneksi menggunakan PDO PostgreSQL.
- Query upsert pembayaran dan DDL log WhatsApp disesuaikan ke PostgreSQL.
- Query agregasi disesuaikan untuk aturan `GROUP BY` PostgreSQL.
- `schema-postgresql.sql` berisi skema dan data awal yang ada di SQL asli (23 anggota, 23 jadwal Sabtu, record pembayaran awal).

## Penting sebelum deploy
1. Backup database lama. Jangan hapus database Aiven/MySQL sampai data diverifikasi.
2. Di Neon SQL Editor, jalankan `schema-postgresql.sql` **hanya pada database baru/kosong**. Jangan jalankan berulang pada database berisi data karena akan konflik.
3. Di Vercel Project → Settings → Environment Variables, tambahkan `DATABASE_URL` dengan connection string Neon penuh, untuk Production (dan Preview jika perlu). Pertahankan variabel DB_* lama sampai aplikasi sudah berhasil diuji.
4. Pastikan runtime PHP Vercel yang dipakai mendukung ekstensi `pdo_pgsql`. Jika runtime tidak menyediakannya, request akan gagal dan perlu runtime/build yang menyertakan ekstensi tersebut.
5. Commit dan push semua file termasuk `vercel.json`, folder `api/`, dan schema ke GitHub; lalu redeploy.

## Data lama
File schema menyertakan data starter dari `db_kaselite.sql`, bukan ekspor transaksi aktual dari server database MySQL online. Data yang mungkin sudah berubah di MySQL (pembayaran terbaru, anggota baru/edit, jadwal tambahan) harus diekspor dan dimigrasikan terpisah. Jangan menganggap semua data live sudah berpindah otomatis.

## Environment
- `DATABASE_URL`: Neon connection string, simpan sebagai secret di Vercel. Jangan commit ke GitHub.

## Catatan keamanan
- Password database lama pernah tertulis langsung di source MySQL asli. Rotasi password tersebut jika masih aktif.
- Aplikasi ini belum memiliki autentikasi admin; jangan memasukkan data sensitif atau membagikan URL publik tanpa menambahkan autentikasi.
- WhatsApp pada versi ini tetap membuka `wa.me`; ini bukan pengiriman otomatis melalui WhatsApp API.
