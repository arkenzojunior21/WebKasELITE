<?php $active_page = $active_page ?? ''; ?>
<aside class="sidebar">
    <div class="sidebar-logo">Kas<span>ELITE</span></div>
    <nav class="sidebar-nav">
        <a href="index.php"   class="<?= $active_page==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
        <a href="anggota.php" class="<?= $active_page==='anggota'  ?'active':'' ?>">👥 Data Anggota</a>
        <a href="minggu.php"  class="<?= $active_page==='minggu'   ?'active':'' ?>">📅 Jadwal Sabtu</a>
        <a href="laporan.php" class="<?= $active_page==='laporan'  ?'active':'' ?>">📊 Laporan</a>
    </nav>
    <div class="sidebar-footer">
        KasELITE v3.0<br>
        Kas tiap Sabtu · Rp3.000/minggu<br>
        <?= date('d M Y') ?>
    </div>
</aside>
