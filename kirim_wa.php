<?php
require_once 'koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit(); }

$a = $conn->query("SELECT * FROM anggota WHERE id=$id")->fetch_assoc();
if (!$a) { header('Location: index.php'); exit(); }

$belum = $conn->query("
    SELECT mk.tanggal, mk.nominal, mk.keterangan
    FROM pembayaran p
    JOIN minggu_kas mk ON p.minggu_id=mk.id
    WHERE p.anggota_id=$id AND p.status='belum_bayar'
    ORDER BY mk.tanggal ASC
")->fetch_all(MYSQLI_ASSOC);

$total = array_sum(array_column($belum,'nominal'));

if ($total === 0) {
    header("Location: index.php?msg=".urlencode('✅ '.$a['nama'].' tidak punya tunggakan!')."&type=success");
    exit();
}

// Format nomor HP → 62xxx
$no_hp = preg_replace('/[^0-9]/', '', $a['no_hp']);
if (substr($no_hp,0,1)==='0') $no_hp = '62'.substr($no_hp,1);

// Buat rincian per Sabtu
$rincian = '';
foreach ($belum as $b) {
    $rincian .= '  • Sabtu, ' . date('d/m/Y', strtotime($b['tanggal'])) . ' — Rp ' . number_format($b['nominal'],0,',','.') . "\n";
}

$pesan =
    "Halo *{$a['nama']}* 👋\n\n" .
    "Ini pengingat dari bendahara *KasELITE*.\n\n" .
    "📌 *Kelas/Jurusan:* {$a['kelas']} - {$a['jurusan']}\n\n" .
    "💸 *Rincian Tunggakan Kas (tiap Sabtu):*\n" .
    $rincian . "\n" .
    "📊 *Total:* *Rp " . number_format($total,0,',','.') . "* (" . count($belum) . " Sabtu)\n\n" .
    "Mohon segera dilunasi ya! 🙏\n" .
    "Terima kasih 😊\n\n" .
    "_— KasELITE | " . date('d/m/Y') . "_";

// Simpan log
$p_db = $conn->real_escape_string($pesan);
$conn->query("CREATE TABLE IF NOT EXISTS log_notif (
    id INT AUTO_INCREMENT PRIMARY KEY, anggota_id INT,
    pesan TEXT, dikirim DATE,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE
)");
$conn->query("INSERT INTO log_notif (anggota_id,pesan,dikirim) VALUES ($id,'$p_db',CURDATE())");

header("Location: https://wa.me/{$no_hp}?text=" . urlencode($pesan));
exit();
?>
