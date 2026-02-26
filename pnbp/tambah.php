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

    // dukung format Indonesia: 1.234.567,89
    $v = str_replace([' ', 'Rp', 'rp'], '', $v);
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);

    return (float)$v;
}

$months = [
  'jan'=>'Januari','feb'=>'Februari','mar'=>'Maret','apr'=>'April','mei'=>'Mei','jun'=>'Juni',
  'jul'=>'Juli','agu'=>'Agustus','sep'=>'September','okt'=>'Oktober','nov'=>'November','des'=>'Desember'
];

// ===== AMBIL TAHUN DARI GET ATAU DEFAULT =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ===== AMBIL TAHUN DARI INPUT TEXT =====
    $tahun_input = isset($_POST['tahun']) ? (int)trim($_POST['tahun']) : date('Y');
    
    // Validasi tahun
    if ($tahun_input < 2000 || $tahun_input > 2099) {
        $err = "❌ Tahun harus antara 2000-2099!";
    } else {
        $mak    = trim($_POST['mak'] ?? '');
        $uraian = trim($_POST['uraian'] ?? '');
        $target = fnum($_POST['target'] ?? 0);

        $val = [];
        foreach(array_keys($months) as $m){
            $val[$m] = fnum($_POST[$m] ?? 0);
        }

        if ($mak === '' || $uraian === '') {
            $err = "❌ MAK dan Uraian wajib diisi.";
        } else {

            // ===== INSERT DENGAN TAHUN =====
            $sql = "
                INSERT INTO pnbp
                (tahun, mak, uraian, target, jan, feb, mar, apr, mei, jun, jul, agu, sep, okt, nov, des)
                VALUES
                (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ";
            $stmt = mysqli_prepare($koneksi, $sql);

            if(!$stmt){
                $err = "❌ Gagal prepare query: " . mysqli_error($koneksi);
            } else {
                $types = "i" . "ss" . str_repeat("d", 13);

                mysqli_stmt_bind_param(
                    $stmt,
                    $types,
                    $tahun_input,
                    $mak, $uraian,
                    $target,
                    $val['jan'], $val['feb'], $val['mar'], $val['apr'], $val['mei'], $val['jun'],
                    $val['jul'], $val['agu'], $val['sep'], $val['okt'], $val['nov'], $val['des']
                );

                if(!mysqli_stmt_execute($stmt)){
                    $err = "❌ Gagal simpan: " . mysqli_stmt_error($stmt);
                } else {
                    mysqli_stmt_close($stmt);
                    // ===== REDIRECT KE TAHUN YANG DIINPUT =====
                    header("Location: index.php?tahun=$tahun_input");
                    exit;
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tambah Data PNBP</title>
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

    <!-- SIDEBAR -->
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
    <!-- END SIDEBAR -->

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Tambah Data PNBP</h2>
            <p>Isi data PNBP (angka boleh pakai titik/koma).</p>
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
                        <!-- ===== INPUT TEXT TAHUN - BISA DIKETIK LANGSUNG! ===== -->
                        <div class="form-group col-md-3">
                            <label for="tahun"><strong>Tahun Anggaran</strong></label>
                            <input type="text" name="tahun" id="tahun" class="form-control" 
                                   placeholder="Contoh: 2025" 
                                   value="<?= $tahun ?>" 
                                   required
                                   maxlength="4">
                            <small class="form-text text-muted">Ketik tahun anggaran (2000-2099)</small>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>MAK</label>
                                <input type="text" name="mak" class="form-control" required>
                            </div>
                            <div class="form-group col-md-9">
                                <label>Uraian</label>
                                <input type="text" name="uraian" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target</label>
                            <input type="text" name="target" class="form-control" value="0">
                        </div>

                        <hr>
                        <h6 class="font-weight-bold">Realisasi Per Bulan</h6>

                        <div class="form-row">
                            <?php foreach($months as $k=>$lbl): ?>
                                <div class="form-group col-md-3">
                                    <label><?= $lbl ?></label>
                                    <input type="text" name="<?= $k ?>" class="form-control" value="0">
                                </div>
                            <?php endforeach; ?>
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

<script src="../asset/js/jquery.min.js"></script>
<script src="../asset/js/bootstrap.bundle.min.js"></script>
<script>
// Validasi hanya angka di input tahun
document.getElementById('tahun').addEventListener('input', function(e) {
    // Hapus karakter non-angka
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});
</script>
</body>
</html>