<?php
require_once __DIR__ . '/koneksi.php';
$active_page = 'laporan';

$anggota_list = $conn->query("
    SELECT a.*,
        COALESCE(SUM(CASE WHEN p.status='belum_bayar' THEN mk.nominal ELSE 0 END),0) AS tunggakan,
        COALESCE(SUM(CASE WHEN p.status='sudah_bayar' THEN mk.nominal ELSE 0 END),0) AS terkumpul,
        COALESCE(SUM(mk.nominal),0) AS kewajiban,
        COUNT(CASE WHEN p.status='belum_bayar' THEN 1 END) AS belum_count,
        COUNT(CASE WHEN p.status='sudah_bayar' THEN 1 END) AS sudah_count,
        MAX(CASE WHEN p.status='sudah_bayar' THEN p.tgl_bayar END) AS bayar_terakhir
    FROM anggota a
    LEFT JOIN pembayaran p  ON a.id=p.anggota_id
    LEFT JOIN minggu_kas mk ON p.minggu_id=mk.id
    GROUP BY a.id, a.nama, a.kelas, a.jurusan, a.no_hp, a.created_at ORDER BY a.kelas, a.jurusan, a.nama
")->fetch_all(MYSQLI_ASSOC);

$total_sabtu     = (int)$conn->query("SELECT COUNT(*) FROM minggu_kas")->fetch_row()[0];
$grand_terkumpul = array_sum(array_column($anggota_list,'terkumpul'));
$grand_tunggakan = array_sum(array_column($anggota_list,'tunggakan'));
$grand_kewajiban = array_sum(array_column($anggota_list,'kewajiban'));
$pct_total = $grand_kewajiban > 0 ? round(($grand_terkumpul/$grand_kewajiban)*100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan — KasELITE</title>
<link rel="stylesheet" href="style.css">
<style>
@media print {
    .sidebar,.no-print{display:none!important}
    .main{margin-left:0!important;padding:14px!important}
    body{background:white!important;color:black!important}
    .section{border:1px solid #ccc!important;background:white!important}
    thead th{background:#f0f0f0!important;color:#333!important}
    .stat-card{border:1px solid #ccc!important;background:white!important}
    .stat-value{color:black!important}
}
</style>
</head>
<body>
<?php require_once __DIR__ . '/_sidebar.php'; ?>
<main class="main">
    <div class="page-header">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div>
                <h1>📊 Laporan Kas</h1>
                <p>Rekap per anggota · Kas Sabtu Februari-Juli 2026 · <?= date('d/m/Y H:i') ?> WIB</p>
            </div>
            <button class="btn btn-outline no-print" onclick="window.print()">🖨️ Cetak / PDF</button>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="stats-grid">
        <div class="stat-card y">
            <div class="stat-label">Total Kewajiban</div>
            <div class="stat-value" style="font-size:1.2rem">Rp<?= number_format($grand_kewajiban/1000,0,',','.') ?>rb</div>
            <div class="stat-sub"><?= count($anggota_list) ?> anggota × <?= $total_sabtu ?> Sabtu</div>
        </div>
        <div class="stat-card g">
            <div class="stat-label">Sudah Terkumpul</div>
            <div class="stat-value" style="font-size:1.2rem">Rp<?= number_format($grand_terkumpul/1000,0,',','.') ?>rb</div>
            <div class="stat-sub"><?= $pct_total ?>% dari kewajiban</div>
        </div>
        <div class="stat-card r">
            <div class="stat-label">Total Tunggakan</div>
            <div class="stat-value" style="font-size:1.2rem">Rp<?= number_format($grand_tunggakan/1000,0,',','.') ?>rb</div>
            <div class="stat-sub"><?= count(array_filter($anggota_list,fn($a)=>$a['tunggakan']>0)) ?> anggota belum lunas</div>
        </div>
        <div class="stat-card o">
            <div class="stat-label">Anggota Kritis</div>
            <div class="stat-value"><?= count(array_filter($anggota_list,fn($a)=>$a['tunggakan']>10000)) ?></div>
            <div class="stat-sub">tunggakan &gt; Rp10.000</div>
        </div>
    </div>

    <!-- PROGRESS BAR TOTAL -->
    <div class="section" style="margin-bottom:20px">
        <div class="section-header"><div class="section-title">📈 Progress Keseluruhan</div></div>
        <div style="padding:18px 22px">
            <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:8px">
                <span style="color:var(--muted)">Rp <?= number_format($grand_terkumpul,0,',','.') ?> terkumpul</span>
                <span style="font-weight:700;color:var(--safe)"><?= $pct_total ?>%</span>
            </div>
            <div style="height:14px;background:var(--border);border-radius:100px;overflow:hidden">
                <div style="height:100%;width:<?= $pct_total ?>%;background:<?= $pct_total>=80?'var(--safe)':($pct_total>=40?'var(--warn)':'var(--danger)') ?>;border-radius:100px;transition:width .5s"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:6px">
                <span>Rp 0</span>
                <span>Target: Rp <?= number_format($grand_kewajiban,0,',','.') ?></span>
            </div>
        </div>
    </div>

    <!-- TABEL LAPORAN -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">📋 Rekap Per Anggota</div>
        </div>
        <div class="section-scroll">
        <table>
            <thead>
                <tr>
                    <th class="tl">#</th>
                    <th class="tl">Nama Anggota</th>
                    <th class="tl">Kelas</th>
                    <th class="tl">Jurusan</th>
                    <th>Sabtu Lunas</th>
                    <th>Sabtu Nunggak</th>
                    <th>Terkumpul</th>
                    <th>Tunggakan</th>
                    <th>Bayar Terakhir</th>
                    <th>Progress</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anggota_list as $i => $a):
                    $tung = (int)$a['tunggakan'];
                    $pct  = $a['kewajiban']>0 ? round(($a['terkumpul']/$a['kewajiban'])*100) : 100;
                    $fc   = $pct>=80?'hi':($pct>=40?'mid':'lo');
                    $tc   = $tung>10000?'t-danger':($tung>0?'t-warn':'t-lunas');
                ?>
                <tr>
                    <td class="tl" style="color:var(--muted)"><?= $i+1 ?></td>
                    <td class="tl" style="font-weight:600"><?= htmlspecialchars($a['nama']) ?></td>
                    <td class="tl"><span class="badge badge-kelas"><?= $a['kelas'] ?></span></td>
                    <td class="tl" style="color:var(--muted)"><?= htmlspecialchars($a['jurusan']) ?></td>
                    <td><span class="t-lunas"><?= $a['sudah_count'] ?></span> <span style="color:var(--muted);font-size:.75rem">Sabtu</span></td>
                    <td>
                        <span style="color:<?= $a['belum_count']>0?'var(--danger)':'var(--muted)' ?>;font-weight:<?= $a['belum_count']>0?700:400 ?>">
                            <?= $a['belum_count'] ?>
                        </span>
                        <span style="color:var(--muted);font-size:.75rem"> Sabtu</span>
                    </td>
                    <td style="color:var(--safe);font-weight:600">Rp <?= number_format($a['terkumpul'],0,',','.') ?></td>
                    <td><span class="<?= $tc ?>"><?= $tung===0?'✓ Lunas':'Rp '.number_format($tung,0,',','.') ?></span></td>
                    <td style="color:var(--muted);font-size:.8rem">
                        <?= $a['bayar_terakhir'] ? date('d/m/Y',strtotime($a['bayar_terakhir'])) : '—' ?>
                    </td>
                    <td>
                        <div class="prog-wrap">
                            <div class="prog-bar"><div class="prog-fill <?= $fc ?>" style="width:<?= $pct ?>%"></div></div>
                            <div class="prog-pct"><?= $pct ?>%</div>
                        </div>
                    </td>
                    <td class="no-print">
                        <?php if($tung>10000): ?>
                        <a href="kirim_wa.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" target="_blank">📲 WA</a>
                        <?php else: ?>
                        <span style="color:var(--muted);font-size:.75rem">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="tl">TOTAL KESELURUHAN</td>
                    <td style="color:var(--safe)">Rp <?= number_format($grand_terkumpul,0,',','.') ?></td>
                    <td style="color:var(--danger)">Rp <?= number_format($grand_tunggakan,0,',','.') ?></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</main>
</body>
</html>
