<?php
require_once 'koneksi.php';
$active_page = 'anggota';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $nama    = trim($conn->real_escape_string($_POST['nama']    ?? ''));
    $kelas   = trim($conn->real_escape_string($_POST['kelas']   ?? ''));
    $jurusan = trim($conn->real_escape_string($_POST['jurusan'] ?? ''));
    $no_hp   = trim($conn->real_escape_string($_POST['no_hp']   ?? ''));

    if ($action === 'tambah') {
        $conn->query("INSERT INTO anggota (nama,kelas,jurusan,no_hp) VALUES ('$nama','$kelas','$jurusan','$no_hp')");
        $nid = $conn->insert_id;
        foreach ($conn->query("SELECT id FROM minggu_kas")->fetch_all(MYSQLI_ASSOC) as $m)
            $conn->query("INSERT IGNORE INTO pembayaran (anggota_id,minggu_id,status) VALUES ($nid,{$m['id']},'belum_bayar')");
        $msg = urlencode('✅ Anggota berhasil ditambahkan!');

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE anggota SET nama='$nama',kelas='$kelas',jurusan='$jurusan',no_hp='$no_hp' WHERE id=$id");
        $msg = urlencode('✅ Data anggota diperbarui!');

    } elseif ($action === 'hapus') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM anggota WHERE id=$id");
        $msg = urlencode('🗑️ Anggota dihapus.');
    }

    header("Location: anggota.php?msg=" . ($msg ?? ''));
    exit();
}

$anggota_list = $conn->query("
    SELECT a.*,
        COALESCE(SUM(CASE WHEN p.status='belum_bayar' THEN mk.nominal ELSE 0 END),0) AS tunggakan
    FROM anggota a
    LEFT JOIN pembayaran p  ON a.id = p.anggota_id
    LEFT JOIN minggu_kas mk ON p.minggu_id = mk.id
    GROUP BY a.id ORDER BY a.kelas, a.jurusan, a.nama
")->fetch_all(MYSQLI_ASSOC);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Anggota — KasELITE</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once '_sidebar.php'; ?>
<main class="main">
    <div class="page-header">
        <h1>👥 Data Anggota</h1>
        <p>Total <?= count($anggota_list) ?> anggota terdaftar</p>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
        <button class="btn btn-accent" onclick="openModal('tambah')">+ Tambah Anggota</button>
    </div>

    <div class="section">
        <div class="section-header">
            <div class="section-title">📋 Daftar Anggota</div>
        </div>
        <div class="section-scroll">
        <table>
            <thead>
                <tr>
                    <th class="tl">#</th>
                    <th class="tl">Nama Lengkap</th>
                    <th class="tl">Kelas</th>
                    <th class="tl">Jurusan</th>
                    <th class="tl">No. HP / WA</th>
                    <th>Tunggakan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anggota_list as $i => $a):
                    $tung = (int)$a['tunggakan'];
                    $tc = $tung > 10000 ? 't-danger' : ($tung > 0 ? 't-warn' : 't-lunas');
                    $bc = $tung > 10000 ? 'badge-danger' : ($tung > 0 ? 'badge-warn' : 'badge-lunas');
                    $bt = $tung > 10000 ? 'Kritis' : ($tung > 0 ? 'Ada Tunggakan' : 'Lunas');
                ?>
                <tr>
                    <td class="tl" style="color:var(--muted)"><?= $i+1 ?></td>
                    <td class="tl" style="font-weight:600"><?= htmlspecialchars($a['nama']) ?></td>
                    <td class="tl"><span class="badge badge-kelas"><?= $a['kelas'] ?></span></td>
                    <td class="tl" style="color:var(--muted)"><?= htmlspecialchars($a['jurusan']) ?></td>
                    <td class="tl" style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($a['no_hp']) ?></td>
                    <td><span class="<?= $tc ?>"><?= $tung===0 ? '✓ Lunas' : 'Rp '.number_format($tung,0,',','.') ?></span></td>
                    <td><span class="badge <?= $bc ?>"><?= $bt ?></span></td>
                    <td>
                        <div style="display:flex;gap:5px;justify-content:center">
                            <button class="btn btn-sm btn-outline" onclick='openModal("edit",<?= json_encode($a) ?>)'>✏️ Edit</button>
                            <form method="POST" style="display:inline"
                                onsubmit="return confirm('Hapus <?= addslashes($a['nama']) ?>?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

<div class="overlay" id="ov">
    <div class="modal">
        <div class="modal-title" id="mTitle">➕ Tambah Anggota</div>
        <form method="POST">
            <input type="hidden" name="action" id="fA" value="tambah">
            <input type="hidden" name="id"     id="fI">
            <div class="fg"><label>Nama Lengkap</label><input type="text" name="nama" id="fN" required placeholder="Nama lengkap..."></div>
            <div class="fg"><label>Kelas</label>
                <select name="kelas" id="fK">
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
            </div>
            <div class="fg"><label>Jurusan</label><input type="text" name="jurusan" id="fJ" required placeholder="Contoh: TEI, RPL 1, TKR 1"></div>
            <div class="fg"><label>No. HP / WhatsApp</label><input type="text" name="no_hp" id="fH" required placeholder="08xxx...">
                <div class="fg-hint">Digunakan untuk kirim notifikasi WA pengingat tunggakan</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-accent" id="fS">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="toast success <?= $msg?'show':'' ?>" id="toast"><?= htmlspecialchars(urldecode($msg)) ?></div>

<script>
const ov = document.getElementById('ov');
function openModal(mode, d=null) {
    document.getElementById('mTitle').textContent = mode==='tambah' ? '➕ Tambah Anggota' : '✏️ Edit Anggota';
    document.getElementById('fA').value = mode;
    document.getElementById('fI').value = d ? d.id : '';
    document.getElementById('fN').value = d ? d.nama : '';
    document.getElementById('fK').value = d ? d.kelas : 'X';
    document.getElementById('fJ').value = d ? d.jurusan : '';
    document.getElementById('fH').value = d ? d.no_hp : '';
    document.getElementById('fS').textContent = mode==='tambah' ? 'Simpan' : 'Perbarui';
    ov.classList.add('show');
}
function closeModal() { ov.classList.remove('show'); }
ov.addEventListener('click', e => { if(e.target===ov) closeModal(); });
<?php if($msg): ?>setTimeout(()=>document.getElementById('toast').classList.remove('show'),4000);<?php endif; ?>
</script>
</body>
</html>
