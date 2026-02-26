<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// ===== AMBIL TAHUN DARI GET PARAMETER =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$err = '';

$current = '';
// ===== CARI REKOMENDASI UNTUK TAHUN TERTENTU =====
$q = mysqli_query($koneksi, "SELECT rekomendasi FROM pnbp_rekomendasi WHERE tahun = $tahun AND id=1 LIMIT 1");
if($q && mysqli_num_rows($q) > 0){
    $r = mysqli_fetch_assoc($q);
    $current = (string)($r['rekomendasi'] ?? '');
} else {
    // kalau baris belum ada untuk tahun ini, buatkan
    mysqli_query($koneksi, "INSERT INTO pnbp_rekomendasi (tahun, id, rekomendasi) VALUES ($tahun, 1, '')");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rekomendasi = trim($_POST['rekomendasi'] ?? '');

    if ($rekomendasi === '') {
        $err = "❌ Rekomendasi wajib diisi.";
    } else {
        // ===== UPDATE REKOMENDASI UNTUK TAHUN TERTENTU =====
        $stmt = mysqli_prepare($koneksi, "UPDATE pnbp_rekomendasi SET rekomendasi=? WHERE tahun=? AND id=1");
        if(!$stmt){
            $err = "❌ Gagal prepare query: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param($stmt, "si", $rekomendasi, $tahun);
            if(!mysqli_stmt_execute($stmt)){
                $err = "❌ Gagal simpan: " . mysqli_stmt_error($stmt);
            } else {
                mysqli_stmt_close($stmt);
                // ===== REDIRECT KE INDEX DENGAN PARAMETER TAHUN =====
                header("Location: index.php?tahun=$tahun");
                exit;
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tambah Rekomendasi</title>
<link href="../asset/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
<style>
.bg-gradient-primary { background-color:#0DBBCB !important; background-image: linear-gradient(180deg,#0DBBCB 10%,#0DBBCB 100%) !important; }
.sidebar .nav-item .nav-link { color:#fff !important; }
.sidebar .nav-item .nav-link:hover { background-color:#009AA8 !important; }
.sidebar .nav-item.active .nav-link { background-color:#0DBBCB !important; color:#fff !important; }
.btn-primary { background-color:#0DBBCB !important; border-color:#0DBBCB !important; }
.btn-primary:hover { background-color:#0b9e88ff !important; }
.card { border-left:4px solid #0DBBCB !important; }
label { font-weight:600; }
.form-control { font-size:14px; }
</style>
</head>
<body id="page-top">
<div id="wrapper">

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <li class="nav-item">
        <a class="nav-link" href="../realisasi_jenis_belanja/index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../spm_berjalan/index.php"><span>SPM Berjalan</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php"><span>Permasalahan & Rencana Tindak Lanjut</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../email/index.php"><span>Broadcast Instruksi</span></a>
    </li>
    <li class="nav-item active">
        <a class="nav-link" href="index.php"><span>Data Realisasi PNBP</span></a>
    </li>
    <hr class="sidebar-divider my-2">
    <li class="nav-item">
        <a class="nav-link" href="../logout.php"><span>Kembali</span></a>
    </li>
</ul>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content" class="p-4">

    <h2>Tambah Rekomendasi - Tahun <?= $tahun ?></h2>
    <p>Rekomendasi untuk tahun anggaran <?= $tahun ?>.</p>
    <hr>

    <?php if($err): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($err) ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="5" required><?= htmlspecialchars($current) ?></textarea>
                </div>

                <div class="d-flex" style="gap:10px;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php?tahun=<?= $tahun ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

</div>
</body>
</html>