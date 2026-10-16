<?php
require_once 'koneksi.php';
$active_page = 'dashboard';

// ── Ambil data ──
$minggu_list  = $conn->query("SELECT * FROM minggu_kas ORDER BY tanggal ASC")->fetch_all(MYSQLI_ASSOC);
$anggota_list = $conn->query("SELECT * FROM anggota ORDER BY kelas, jurusan, nama")->fetch_all(MYSQLI_ASSOC);
$bayar_raw    = $conn->query("SELECT * FROM pembayaran")->fetch_all(MYSQLI_ASSOC);

$bayar_map = [];
foreach ($bayar_raw as $b) {
    $bayar_map[$b['anggota_id']][$b['minggu_id']] = $b;
}

// Hitung tunggakan per anggota
$tunggakan = [];
foreach ($anggota_list as $a) {
    $total = 0;
    foreach ($minggu_list as $m) {
        if (($bayar_map[$a['id']][$m['id']]['status'] ?? 'belum_bayar') === 'belum_bayar') {
            $total += $m['nominal'];
        }
    }
    $tunggakan[$a['id']] = $total;
}

// Statistik
$total_anggota   = count($anggota_list);
$total_tunggakan = array_sum($tunggakan);
$anggota_kritis  = count(array_filter($tunggakan, fn($t) => $t > 10000));
$kas_terkumpul   = (int)$conn->query("
    SELECT COALESCE(SUM(mk.nominal),0) FROM pembayaran p
    JOIN minggu_kas mk ON p.minggu_id=mk.id WHERE p.status='sudah_bayar'
")->fetch_row()[0];

// Grup minggu per bulan
$minggu_by_bulan = [];
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
foreach ($minggu_list as $m) {
    $bln = (int)date('n', strtotime($m['tanggal']));
    $minggu_by_bulan[$bln][] = $m;
}

$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? 'success';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — KasELITE</title>
<link rel="stylesheet" href="style.css">
<style>
.month-sep th {
    background: var(--s3) !important;
    color: var(--accent) !important;
    font-size: .72rem !important;
    letter-spacing: 1px;
    text-align: center;
    padding: 6px 12px !important;
    border-top: 2px solid var(--accent) !important;
}
.sabtu-th {
    min-width: 66px;
    font-size: .67rem !important;
}
.sabtu-th .day  { font-weight: 700; color: var(--text); font-size: .78rem; }
.sabtu-th .date { color: var(--muted); font-size: .65rem; margin-top: 1px; }
</style>
</head>
<body>
<?php require_once '_sidebar.php'; ?>

<main class="main">
    <!-- HEADER -->
    <div class="page-header">
        <h1>Dashboard KasELITE 💰</h1>
        <p>Kas mingguan setiap Sabtu · Rp3.000/Sabtu · Februari — Juli 2026</p>
    </div>

    <!-- STATISTIK -->
    <div class="stats-grid">
        <div class="stat-card y">
            <div class="stat-label">Total Anggota</div>
            <div class="stat-value"><?= $total_anggota ?></div>
            <div class="stat-sub">orang terdaftar</div>
        </div>
        <div class="stat-card g">
            <div class="stat-label">Kas Terkumpul</div>
            <div class="stat-value">Rp<?= number_format($kas_terkumpul/1000,0,',','.') ?>rb</div>
            <div class="stat-sub">sudah dibayar</div>
        </div>
        <div class="stat-card r">
            <div class="stat-label">Total Tunggakan</div>
            <div class="stat-value">Rp<?= number_format($total_tunggakan/1000,0,',','.') ?>rb</div>
            <div class="stat-sub">belum dibayar</div>
        </div>
        <div class="stat-card o">
            <div class="stat-label">Anggota Kritis</div>
            <div class="stat-value"><?= $anggota_kritis ?></div>
            <div class="stat-sub">tunggakan &gt; Rp10.000</div>
        </div>
    </div>

    <!-- NOTIFIKASI KRITIS -->
    <?php $kritis = array_filter($anggota_list, fn($a) => $tunggakan[$a['id']] > 10000); ?>
    <?php if (count($kritis) > 0): ?>
    <div class="alert-banner danger">
        <div class="alert-title">🔔 Pengingat Tunggakan Melebihi Rp10.000</div>
        <?php foreach ($kritis as $a): ?>
        <div class="alert-item">
            <div>
                <b><?= htmlspecialchars($a['nama']) ?></b>
                <span style="color:var(--muted);font-size:.78rem;margin-left:8px"><?= $a['kelas'].' - '.$a['jurusan'] ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:9px">
                <span class="t-danger">Rp <?= number_format($tunggakan[$a['id']],0,',','.') ?></span>
                <a href="kirim_wa.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" target="_blank">📲 Kirim WA</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:16px;font-size:.78rem;color:var(--muted)">
            <span>🔑 Legenda:</span>
            <span style="display:flex;align-items:center;gap:5px">
                <span style="width:20px;height:20px;background:rgba(0,224,150,.15);border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:var(--safe);font-size:.85rem">✓</span>
                Sudah bayar
            </span>
            <span style="display:flex;align-items:center;gap:5px">
                <span style="width:20px;height:20px;background:rgba(255,69,96,.08);border:1px dashed rgba(255,69,96,.3);border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:rgba(255,69,96,.45);font-size:.75rem">✕</span>
                Belum bayar
            </span>
            <span style="color:var(--muted);font-size:.72rem">· hover = lihat tanggal bayar</span>
        </div>
        <div style="display:flex;gap:8px">
            <a href="minggu.php"  class="btn btn-sm btn-outline">+ Jadwal Sabtu</a>
            <a href="anggota.php" class="btn btn-sm btn-accent">+ Anggota</a>
        </div>
    </div>

    <!-- GRID KAS PER SABTU -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">📋 Grid Pembayaran Kas — Tiap Sabtu</div>
            <span style="font-size:.78rem;color:var(--muted)"><?= count($minggu_list) ?> Sabtu · <?= count($anggota_list) ?> anggota</span>
        </div>

        <div class="section-scroll">
        <table>
            <thead>
                <!-- Baris 1: Header bulan (colspan per bulan) -->
                <tr>
                    <th class="col-no tl" rowspan="2" style="vertical-align:middle">#</th>
                    <th class="col-nama tl" rowspan="2" style="vertical-align:middle">Nama Anggota</th>
                    <th class="col-kj tl" rowspan="2" style="vertical-align:middle">Kelas / Jurusan</th>
                    <?php foreach ($minggu_by_bulan as $bln => $minggu_arr): ?>
                    <th colspan="<?= count($minggu_arr) ?>"
                        style="background:var(--s3);color:var(--accent);font-size:.72rem;letter-spacing:1px;border-left:2px solid var(--accent)">
                        📅 <?= $nama_bulan[$bln] ?> 2026
                        <span style="color:var(--muted);font-weight:400;font-size:.65rem;margin-left:4px">(<?= count($minggu_arr) ?> Sabtu)</span>
                    </th>
                    <?php endforeach; ?>
                    <th class="col-tung" rowspan="2" style="vertical-align:middle">Tunggakan</th>
                </tr>
                <!-- Baris 2: Tanggal tiap Sabtu -->
                <tr>
                    <?php
                    $first_in_month = true;
                    foreach ($minggu_by_bulan as $bln => $minggu_arr):
                        foreach ($minggu_arr as $idx => $m):
                            $border = $idx === 0 ? 'border-left:2px solid var(--accent)' : '';
                    ?>
                    <th class="sabtu-th" style="<?= $border ?>">
                        <div class="day">Sabtu</div>
                        <div class="date"><?= date('d/m', strtotime($m['tanggal'])) ?></div>
                    </th>
                    <?php endforeach; endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($anggota_list as $i => $a):
                    $tung = $tunggakan[$a['id']];
                    $tc   = $tung > 10000 ? 't-danger' : ($tung > 0 ? 't-warn' : 't-lunas');
                ?>
                <tr>
                    <td class="col-no tl" style="color:var(--muted);font-size:.78rem"><?= $i+1 ?></td>
                    <td class="col-nama tl" style="font-weight:600;font-size:.855rem"><?= htmlspecialchars($a['nama']) ?></td>
                    <td class="col-kj tl">
                        <span class="badge badge-kelas"><?= $a['kelas'] ?></span>
                        <div style="font-size:.75rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($a['jurusan']) ?></div>
                    </td>

                    <?php
                    foreach ($minggu_by_bulan as $bln => $minggu_arr):
                        foreach ($minggu_arr as $idx => $m):
                            $rec    = $bayar_map[$a['id']][$m['id']] ?? null;
                            $status = $rec['status'] ?? 'belum_bayar';
                            $tglbyr = $rec['tgl_bayar'] ?? null;
                            $sudah  = $status === 'sudah_bayar';
                            $border = $idx === 0 ? 'border-left:2px solid rgba(245,200,66,.2)' : '';
                    ?>
                    <td style="<?= $border ?>">
                        <div class="tbwrap">
                            <form method="POST" action="toggle_bayar.php">
                                <input type="hidden" name="anggota_id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="minggu_id"  value="<?= $m['id'] ?>">
                                <input type="hidden" name="new_status" value="<?= $sudah ? 'belum_bayar' : 'sudah_bayar' ?>">
                                <button type="submit" class="toggle-btn <?= $sudah ? 'sudah' : 'belum' ?>"
                                    title="<?= $sudah && $tglbyr ? 'Bayar: '.date('d/m/Y',strtotime($tglbyr)) : 'Belum bayar' ?>">
                                    <?= $sudah ? '✓' : '✕' ?>
                                </button>
                            </form>
                            <?php if ($sudah && $tglbyr): ?>
                            <div class="tbtip">📅 <?= date('d/m/Y', strtotime($tglbyr)) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endforeach; endforeach; ?>

                    <td class="col-tung">
                        <span class="<?= $tc ?>">
                            <?= $tung === 0 ? '✓ Lunas' : 'Rp '.number_format($tung,0,',','.') ?>
                        </span>
                        <?php if ($tung > 0): ?>
                        <div style="font-size:.68rem;color:var(--muted);margin-top:1px"><?= intdiv($tung,3000) ?> Sabtu</div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3" class="tl" style="font-size:.8rem">
                        TOTAL · <?= $total_anggota ?> anggota · <?= count($minggu_list) ?> Sabtu
                    </td>
                    <?php
                    foreach ($minggu_by_bulan as $bln => $minggu_arr):
                        foreach ($minggu_arr as $idx => $m):
                            $sudah_count = count(array_filter($bayar_raw,
                                fn($b) => $b['minggu_id']==$m['id'] && $b['status']=='sudah_bayar'));
                            $border = $idx === 0 ? 'border-left:2px solid rgba(245,200,66,.2)' : '';
                            echo "<td style='font-size:.72rem;color:var(--muted);$border'>$sudah_count/{$total_anggota}</td>";
                        endforeach;
                    endforeach;
                    ?>
                    <td style="color:var(--danger)">Rp <?= number_format($total_tunggakan,0,',','.') ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</main>

<div class="toast <?= $type ?> <?= $msg ? 'show' : '' ?>" id="toast">
    <?= htmlspecialchars(urldecode($msg)) ?>
</div>
<script>
<?php if ($msg): ?>
setTimeout(() => document.getElementById('toast').classList.remove('show'), 4000);
<?php endif; ?>
</script>
</body>
</html>
