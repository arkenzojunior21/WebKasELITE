<?php
require_once __DIR__ . '/koneksi.php';

$anggota_id = (int)($_POST['anggota_id'] ?? 0);
$minggu_id  = (int)($_POST['minggu_id']  ?? 0);
$new_status = ($_POST['new_status'] ?? '') === 'sudah_bayar' ? 'sudah_bayar' : 'belum_bayar';
$tgl_bayar  = $new_status === 'sudah_bayar' ? "'" . date('Y-m-d') . "'" : "NULL";

$sql = "INSERT INTO pembayaran (anggota_id, minggu_id, status, tgl_bayar)
        VALUES ($anggota_id, $minggu_id, '$new_status', $tgl_bayar)
        ON CONFLICT (anggota_id, minggu_id) DO UPDATE SET status=EXCLUDED.status, tgl_bayar=EXCLUDED.tgl_bayar";

$conn->query($sql);

$msg = $new_status === 'sudah_bayar'
    ? urlencode('✅ Ditandai sudah bayar · ' . date('d/m/Y'))
    : urlencode('↩️ Pembayaran dibatalkan.');

header("Location: index.php?msg=$msg&type=success");
exit();
?>
