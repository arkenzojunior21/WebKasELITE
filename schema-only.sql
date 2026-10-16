-- KasELITE schema-only migration. Select/create your database in the database provider first.
-- No member records included. Review before running on a database with existing tables.
-- ============================================
-- DATABASE: KasELITE
-- 23 Anggota | Kas tiap Sabtu Feb-Juli 2026
-- ============================================

-- ============================================
-- TABEL ANGGOTA
-- ============================================
CREATE TABLE anggota (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    kelas      VARCHAR(10)  NOT NULL,
    jurusan    VARCHAR(50)  NOT NULL,
    no_hp      VARCHAR(20)  NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL MINGGU KAS
-- ============================================
CREATE TABLE minggu_kas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    tanggal     DATE         NOT NULL,
    nominal     INT          NOT NULL DEFAULT 3000,
    keterangan  VARCHAR(100) DEFAULT 'Kas Mingguan',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL PEMBAYARAN
-- ============================================
CREATE TABLE pembayaran (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id  INT NOT NULL,
    minggu_id   INT NOT NULL,
    status      ENUM('belum_bayar','sudah_bayar') DEFAULT 'belum_bayar',
    tgl_bayar   DATE NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (minggu_id)  REFERENCES minggu_kas(id) ON DELETE CASCADE,
    UNIQUE KEY uq_bayar (anggota_id, minggu_id)
);
