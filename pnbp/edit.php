<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function fnum($v){
    if ($v === null) return 0;
    $v = trim((string)$v);
    if ($v === '') return 0;

    $v = str_replace([' ', 'Rp', 'rp'], '', $v);
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);

    return (float)$v;
}

// Fungsi untuk menampilkan angka tanpa .00 jika bulat
function displayNumber($num) {
    if ($num == 0) return '';
    // Hapus .00 jika angka bulat
    $formatted = rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
    return $formatted;
}

$months = [
  'jan'=>'Januari','feb'=>'Februari','mar'=>'Maret','apr'=>'April','mei'=>'Mei','jun'=>'Juni',
  'jul'=>'Juli','agu'=>'Agustus','sep'=>'September','okt'=>'Oktober','nov'=>'November','des'=>'Desember'
];

// ===== AMBIL ID DAN TAHUN DARI GET PARAMETER =====
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if ($id <= 0) {
    header("Location: index.php?tahun=$tahun");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pnbp WHERE id=$id LIMIT 1"));
if (!$data) {
    header("Location: index.php?tahun=$tahun");
    exit;
}

// ===== VALIDASI TAHUN DATA =====
if((int)$data['tahun'] !== $tahun) {
    header("Location: index.php?tahun=$tahun");
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mak    = mysqli_real_escape_string($koneksi, trim($_POST['mak'] ?? ''));
    $uraian = mysqli_real_escape_string($koneksi, trim($_POST['uraian'] ?? ''));
    $target = fnum($_POST['target'] ?? 0);

    $jan = fnum($_POST['jan'] ?? 0);
    $feb = fnum($_POST['feb'] ?? 0);
    $mar = fnum($_POST['mar'] ?? 0);
    $apr = fnum($_POST['apr'] ?? 0);
    $mei = fnum($_POST['mei'] ?? 0);
    $jun = fnum($_POST['jun'] ?? 0);
    $jul = fnum($_POST['jul'] ?? 0);
    $agu = fnum($_POST['agu'] ?? 0);
    $sep = fnum($_POST['sep'] ?? 0);
    $okt = fnum($_POST['okt'] ?? 0);
    $nov = fnum($_POST['nov'] ?? 0);
    $des = fnum($_POST['des'] ?? 0);

    if ($mak === '' || $uraian === '') {
        $err = "MAK dan Uraian wajib diisi.";
    } else {
        $sql = "UPDATE pnbp SET
                mak='$mak',
                uraian='$uraian',
                target=$target,
                jan=$jan,
                feb=$feb,
                mar=$mar,
                apr=$apr,
                mei=$mei,
                jun=$jun,
                jul=$jul,
                agu=$agu,
                sep=$sep,
                okt=$okt,
                nov=$nov,
                des=$des
                WHERE id=$id";

        if(mysqli_query($koneksi, $sql)){
            $_SESSION['success'] = "Data berhasil diperbarui!";
            // ===== REDIRECT DENGAN PARAMETER TAHUN =====
            header("Location: index.php?tahun=$tahun");
            exit;
        } else {
            $err = "Gagal simpan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Data PNBP</title>
<link href="../asset/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
<style>
.bg-gradient-primary {
    background-color: #0DBBCB !important;
    background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
}
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

            <h2>Edit Data PNBP - ID: <?= $id ?> (Tahun <?= $tahun ?>)</h2>
            <p>Ubah data PNBP (angka boleh pakai titik/koma).</p>
            <hr>

            <?php if($err): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>MAK</label>
                                <input type="text" name="mak" class="form-control" required value="<?= htmlspecialchars($data['mak']) ?>">
                            </div>
                            <div class="form-group col-md-9">
                                <label>Uraian</label>
                                <input type="text" name="uraian" class="form-control" required value="<?= htmlspecialchars($data['uraian']) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target</label>
                            <input type="text" name="target" class="form-control" value="<?= displayNumber($data['target']) ?>">
                        </div>

                        <hr>
                        <h6 class="font-weight-bold">Realisasi Per Bulan</h6>

                        <div class="form-row">
                            <?php foreach($months as $k=>$lbl): ?>
                                <div class="form-group col-md-3">
                                    <label><?= $lbl ?></label>
                                    <input type="text" name="<?= $k ?>" class="form-control" value="<?= displayNumber($data[$k]) ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex" style="gap:10px;">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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