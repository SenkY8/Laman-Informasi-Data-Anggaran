<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../../login.php");
    exit;
}
include "../../../koneksi.php";

$error = '';

if(isset($_POST['simpan'])) {
    $aspek = trim($_POST['aspek'] ?? '');
    $tahun_anggaran = (int)($_POST['tahun_anggaran'] ?? 0);
    $nilai = (float)($_POST['nilai'] ?? 0);

    if (!$aspek) {
        $error = "Aspek harus diisi!";
    } else if (!$tahun_anggaran) {
        $error = "Tahun Anggaran harus diisi!";
    } else {
        $aspek_sql = mysqli_real_escape_string($koneksi, $aspek);
        $cek = mysqli_query($koneksi, "SELECT id FROM indikator_aspek WHERE aspek = '$aspek_sql' AND tahun_anggaran = $tahun_anggaran");

        if (mysqli_num_rows($cek) > 0) {
            $error = "Data aspek '$aspek' tahun $tahun_anggaran sudah ada!";
        } else {
            $sql = "INSERT INTO indikator_aspek (aspek, tahun_anggaran, nilai) VALUES ('$aspek_sql', $tahun_anggaran, $nilai)";

            if(mysqli_query($koneksi, $sql)) {
                $_SESSION['message'] = 'Data berhasil ditambahkan!';
                $_SESSION['message_type'] = 'success';
                header("Location: ../../index.php");
                exit;
            } else {
                $error = "Error: " . mysqli_error($koneksi);
            }
        }
    }
}

$current_year = date('Y');

// Ambil daftar aspek yang sudah ada
$daftar_aspek = [];
$qa = mysqli_query($koneksi, "SELECT DISTINCT aspek FROM indikator_aspek ORDER BY aspek ASC");
while($r = mysqli_fetch_assoc($qa)) $daftar_aspek[] = $r['aspek'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Indikator Aspek</title>
    <link href="../../../asset/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .bg-gradient-primary { background-color: #0DBBCB !important; background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important; }
        .btn-primary { background-color: #0DBBCB !important; border-color: #0DBBCB !important; }
        .btn-primary:hover { background-color: #0b9e88ff !important; }
        .card { border-left: 4px solid #0DBBCB !important; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item"><a class="nav-link" href="../../../realisasi_jenis_belanja/index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../spm_berjalan/index.php"><span>SPM Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../permasalahan_dan_rencana_tindaklanjut/index.php"><span>Permasalahan & Rencana Tindak Lanjut</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../email/index.php"><span>Broadcast Instruksi</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../pnbp/index.php"><span>Data Realisasi PNBP</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../../../pk/index.php"><span>Perjanjian Kinerja</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="../../index.php"><span>Komparasi Pelaksanaan Anggaran</span></a></li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item"><a class="nav-link" href="../../../logout.php"><span>Kembali</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus text-success"></i> Tambah Data Indikator Kinerja</h1>
                <a href="../../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-alt"></i> Form Tambah Data</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="aspek_pilih">Aspek <span class="text-danger">*</span></label>
                            <?php if(!empty($daftar_aspek)): ?>
                            <select id="aspek_pilih" name="aspek_pilih" class="form-control mb-2" onchange="toggleAspekBaru(this.value)">
                                <option value="">-- Pilih Aspek --</option>
                                <?php foreach($daftar_aspek as $a): ?>
                                    <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                                <?php endforeach; ?>
                                <option value="__baru__">+ Tambah Aspek Baru</option>
                            </select>
                            <?php endif; ?>
                            <input type="text" id="aspek" name="aspek" class="form-control <?= empty($daftar_aspek) ? '' : 'd-none' ?>"
                                   value="<?= isset($_POST['aspek']) ? htmlspecialchars($_POST['aspek']) : '' ?>"
                                   placeholder="Ketik nama aspek baru">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tahun_anggaran">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" id="tahun_anggaran" name="tahun_anggaran" class="form-control"
                                   value="<?= isset($_POST['tahun_anggaran']) ? (int)$_POST['tahun_anggaran'] : $current_year ?>"
                                   min="2000" max="2099" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="nilai">Nilai <span class="text-danger">*</span></label>
                            <input type="number" id="nilai" name="nilai" class="form-control"
                                   step="0.01" min="0" max="100"
                                   value="<?= isset($_POST['nilai']) ? htmlspecialchars($_POST['nilai']) : '' ?>"
                                   placeholder="Contoh: 93.64" required>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-sm-12">
                                <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Simpan Data
                                </button>
                                <a href="../../index.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../../asset/vendor/jquery/jquery.min.js"></script>
<script src="../../../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function toggleAspekBaru(val) {
    const input = document.getElementById('aspek');
    if (val === '__baru__') {
        input.classList.remove('d-none');
        input.focus();
    } else {
        input.classList.add('d-none');
        input.value = val; // isi dengan pilihan dropdown
    }
}
// Set default value aspek dari dropdown ke hidden input
document.getElementById('aspek_pilih')?.addEventListener('change', function() {
    if (this.value !== '__baru__' && this.value !== '') {
        document.getElementById('aspek').value = this.value;
    }
});
</script>
</body>
</html>