-- ============================================
-- DATABASE: KasELITE
-- 23 Anggota | Kas tiap Sabtu Feb-Juli 2026
-- ============================================

DROP DATABASE IF EXISTS db_kaselite;
CREATE DATABASE db_kaselite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_kaselite;

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

-- ============================================
-- DATA 23 ANGGOTA 
-- ============================================
INSERT INTO anggota (nama, kelas, jurusan, no_hp) VALUES
('Amir Zaky Kustoro',                'X',   'TEI',    '083192750150'),
('Daffaa Rizky Saputra',             'X',   'TKR 1',  '082227725926'),
('Elma Elysia Shafira',              'X',   'TEI',    '0895397814040'),
('Fransiskus Xaverius Siola Junior', 'X',   'RPL 1',  '085319120440'),
('Gendhis D Nia',                    'X',   'TITL 1', '089604163290'),
('Hafizea Anra Barra',               'X',   'TEI',    '085808793932'),
('Muhammad Ayyub',                   'X',   'TEI',    '087850545175'),
('Muhammad Zidane Yanuar Arifin',    'X',   'TEI',    '0882009298762'),
('Nadif Bratadikara Putra Haryanto', 'X',   'TEI',    '081216976462'),
('Qonitah Zalfah',                   'X',   'TKP 1',  '085815444753'),
('Renata Aulia Yusuf',               'X',   'RPL 1',  '085755207358'),
('Abdurrahman Luqman Al-Hakim',      'XI',  'TEI',    '0881026533826'),
('Abhista Nareswari Sugiyanto',      'XI',  'TEI',    '081775122133'),
('Berliana Fitri Aulia',             'XI',  'RPL 2',  '082136925117'),
('Eva Luna Putri Janur',             'XI',  'RPL 2',  '0881026603933'),
('Mei Adinda Luckytasari',           'XI',  'TAv 2',  '083134535874'),
('M. Haikal Ubaidillhaq',            'XI',  'TEI',    '088805052191'),
('Mahmud Azzam',                     'XI',  'TAv 2',  '085235574682'),
('Muhammad Ibnu Ridho',              'XI',  'TEI',    '0895379227347'),
('Neil Sawabul Jazil',               'XI',  'RPL 2',  '085806653602'),
('Raditiya Vitsaya Darma',           'XI',  'RPL 2',  '085326610706'),
('Satya Novario Putranto',           'XI',  'RPL 2',  '08970497248'),
('Tunggul Cakra Wisesa',             'XI',  'TEI',    '082132604974');

-- ============================================
-- 23 HARI SABTU: FEBRUARI - JULI 2026
-- Februari: hanya 14 & 28 Feb
-- ============================================
INSERT INTO minggu_kas (tanggal, nominal, keterangan) VALUES
-- FEBRUARI 2026 
('2026-02-14', 3000, 'Kas Sabtu - 14 Februari 2026'),
('2026-02-28', 3000, 'Kas Sabtu - 28 Februari 2026'),
-- MARET 2026 (4 Sabtu)
('2026-03-07', 3000, 'Kas Sabtu - 07 Maret 2026'),
('2026-03-14', 3000, 'Kas Sabtu - 14 Maret 2026'),
('2026-03-21', 3000, 'Kas Sabtu - 21 Maret 2026'),
('2026-03-28', 3000, 'Kas Sabtu - 28 Maret 2026'),
-- APRIL 2026 (4 Sabtu)
('2026-04-04', 3000, 'Kas Sabtu - 04 April 2026'),
('2026-04-11', 3000, 'Kas Sabtu - 11 April 2026'),
('2026-04-18', 3000, 'Kas Sabtu - 18 April 2026'),
('2026-04-25', 3000, 'Kas Sabtu - 25 April 2026'),
-- MEI 2026 (5 Sabtu)
('2026-05-02', 3000, 'Kas Sabtu - 02 Mei 2026'),
('2026-05-09', 3000, 'Kas Sabtu - 09 Mei 2026'),
('2026-05-16', 3000, 'Kas Sabtu - 16 Mei 2026'),
('2026-05-23', 3000, 'Kas Sabtu - 23 Mei 2026'),
('2026-05-30', 3000, 'Kas Sabtu - 30 Mei 2026'),
-- JUNI 2026 (4 Sabtu)
('2026-06-06', 3000, 'Kas Sabtu - 06 Juni 2026'),
('2026-06-13', 3000, 'Kas Sabtu - 13 Juni 2026'),
('2026-06-20', 3000, 'Kas Sabtu - 20 Juni 2026'),
('2026-06-27', 3000, 'Kas Sabtu - 27 Juni 2026'),
-- JULI 2026 (4 Sabtu)
('2026-07-04', 3000, 'Kas Sabtu - 04 Juli 2026'),
('2026-07-11', 3000, 'Kas Sabtu - 11 Juli 2026'),
('2026-07-18', 3000, 'Kas Sabtu - 18 Juli 2026'),
('2026-07-25', 3000, 'Kas Sabtu - 25 Juli 2026');

-- ============================================
-- BUAT RECORD PEMBAYARAN (semua belum bayar)
-- ============================================
INSERT INTO pembayaran (anggota_id, minggu_id, status)
SELECT a.id, m.id, 'belum_bayar'
FROM anggota a CROSS JOIN minggu_kas m;
