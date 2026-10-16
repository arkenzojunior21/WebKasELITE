<?php
require_once 'koneksi.php';
$active_page = 'minggu';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $tgl     = $conn->real_escape_string($_POST['tanggal']);
        $nominal = (int)($_POST['nominal'] ?? 3000);
        $ket     = $conn->real_escape_string($_POST['keterangan'] ?? 'Kas Mingguan');

        // Validasi harus hari Sabtu
        $hari = date('N', strtotime($tgl)); // 6=Sabtu
        if ($hari != 6) {
            $msg = urlencode('❌ Tanggal harus hari Sabtu!');
        } else {
            $conn->query("INSERT INTO minggu_kas (tanggal,nominal,keterangan) VALUES ('$tgl',$nominal,'$ket')");
            $nid = $conn->insert_id;
            foreach ($conn->query("SELECT id FROM anggota")->fetch_all(MYSQLI_ASSOC) as $a)
                $conn->query("INSERT IGNORE INTO pembayaran (anggota_id,minggu_id,status) VALUES ({$a['id']},$nid,'belum_bayar')");
            $msg = urlencode('✅ Jadwal Sabtu ditambahkan! Semua anggota otomatis tercatat.');
        }

    } elseif ($action === 'hapus') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM minggu_kas WHERE id=$id");
        $msg = urlencode('🗑️ Jadwal Sabtu dihapus.');
    }

    header("Location: minggu.php?msg=" . ($msg ?? ''));
    exit();
}

$minggu_list = $conn->query("
    SELECT mk.*,
        SUM(CASE WHEN p.status='sudah_bayar' THEN 1 ELSE 0 END) AS sudah_count,
        SUM(CASE WHEN p.status='belum_bayar' THEN 1 ELSE 0 END) AS belum_count
    FROM minggu_kas mk
    LEFT JOIN pembayaran p ON mk.id=p.minggu_id
    GROUP BY mk.id ORDER BY mk.tanggal ASC
")->fetch_all(MYSQLI_ASSOC);

$total_anggota = (int)$conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Sabtu — KasELITE</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once '_sidebar.php'; ?>
<main class="main">
    <div class="page-header">
        <h1>📅 Jadwal Sabtu</h1>
        <p>Daftar tanggal kas mingguan · hanya hari Sabtu · Rp3.000/Sabtu</p>
    </div>

    <!-- FORM TAMBAH -->
    <div class="section" style="margin-bottom:20px">
        <div class="section-header">
            <div class="section-title">➕ Tambah Jadwal Sabtu Baru</div>
            <span style="font-size:.75rem;color:var(--muted)">⚠️ Sistem hanya menerima tanggal hari Sabtu</span>
        </div>
        <div style="padding:20px 22px">
            <form method="POST">
                <input type="hidden" name="action" value="tambah">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:13px;align-items:end">
                    <div class="fg" style="margin:0">
                        <label>Tanggal (Sabtu)</label>
                        <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>">
                        <div class="fg-hint">Hanya tanggal Sabtu yang diterima</div>
                    </div>
                    <div class="fg" style="margin:0">
                        <label>Nominal (Rp)</label>
                        <input type="number" name="nominal" value="3000" min="0" required>
                    </div>
                    <div class="fg" style="margin:0">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Kas Sabtu - ...">
                    </div>
                    <button type="submit" class="btn btn-accent">+ Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">📋 Daftar Jadwal Sabtu</div>
            <span style="font-size:.78rem;color:var(--muted)"><?= count($minggu_list) ?> jadwal terdaftar</span>
        </div>
        <div class="section-scroll">
        <table>
            <thead>
                <tr>
                    <th class="tl">#</th>
                    <th class="tl">Tanggal</th>
                    <th class="tl">Bulan</th>
                    <th class="tl">Keterangan</th>
                    <th>Nominal</th>
                    <th>Sudah Bayar</th>
                    <th>Belum Bayar</th>
                    <th>Progress</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $prev_bln = null;
                foreach ($minggu_list as $i => $m):
                    $bln = (int)date('n', strtotime($m['tanggal']));
                    $pct = $total_anggota > 0 ? round(($m['sudah_count']/$total_anggota)*100) : 0;
                    $fc  = $pct>=80 ? 'hi' : ($pct>=40 ? 'mid' : 'lo');
                    $is_new_month = $bln !== $prev_bln;
                    $prev_bln = $bln;
                ?>
                <?php if ($is_new_month): ?>
                <tr>
                    <td colspan="9" style="background:var(--s3);color:var(--accent);font-weight:700;font-size:.8rem;letter-spacing:.8px;padding:8px 16px;border-top:2px solid rgba(245,200,66,.3)">
                        📅 <?= $nama_bulan[$bln] ?> 2026
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="tl" style="color:var(--muted)"><?= $i+1 ?></td>
                    <td class="tl">
                        <div style="font-weight:600">Sabtu, <?= date('d', strtotime($m['tanggal'])) ?></div>
                    </td>
                    <td class="tl" style="color:var(--muted)"><?= $nama_bulan[$bln] ?> 2026</td>
                    <td class="tl" style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($m['keterangan']) ?></td>
                    <td style="color:var(--accent);font-weight:700">Rp <?= number_format($m['nominal'],0,',','.') ?></td>
                    <td><span class="t-lunas"><?= $m['sudah_count'] ?> orang</span></td>
                    <td>
                        <span style="color:<?= $m['belum_count']>0?'var(--danger)':'var(--muted)' ?>;font-weight:<?= $m['belum_count']>0?700:400 ?>">
                            <?= $m['belum_count'] ?> orang
                        </span>
                    </td>
                    <td>
                        <div class="prog-wrap">
                            <div class="prog-bar"><div class="prog-fill <?= $fc ?>" style="width:<?= $pct ?>%"></div></div>
                            <div class="prog-pct"><?= $pct ?>%</div>
                        </div>
                    </td>
                    <td>
                        <form method="POST" style="display:inline"
                            onsubmit="return confirm('Hapus jadwal Sabtu <?= date('d/m/Y',strtotime($m['tanggal'])) ?>?\nData pembayaran minggu ini ikut terhapus.')">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

<div class="toast success <?= $msg?'show':'' ?>" id="toast"><?= htmlspecialchars(urldecode($msg)) ?></div>
<script><?php if($msg): ?>setTimeout(()=>document.getElementById('toast').classList.remove('show'),4000);<?php endif; ?></script>
</body>
</html>
