<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../../login.php");
    exit;
}
include "../../../koneksi.php";

$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: ../../index.php");
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM indikator_aspek WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: ../../index.php");
    exit;
}

if(isset($_POST['simpan'])) {
    $aspek          = trim($_POST['aspek'] ?? '');
    $tahun_anggaran = (int)($_POST['tahun_anggaran'] ?? 0);
    $nilai          = (float)($_POST['nilai'] ?? 0);

    if (!$aspek) {
        $error = "Aspek harus diisi!";
    } else if (!$tahun_anggaran) {
        $error = "Tahun Anggaran harus diisi!";
    } else {
        $aspek_sql = mysqli_real_escape_string($koneksi, $aspek);

        // Cek duplikat jika aspek atau tahun diubah
        $cek = mysqli_query($koneksi, "SELECT id FROM indikator_aspek WHERE aspek = '$aspek_sql' AND tahun_anggaran = $tahun_anggaran AND id != $id");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Data aspek '$aspek' tahun $tahun_anggaran sudah ada!";
        } else {
            $sql = "UPDATE indikator_aspek SET aspek = '$aspek_sql', tahun_anggaran = $tahun_anggaran, nilai = $nilai WHERE id = $id";

            if(mysqli_query($koneksi, $sql)) {
                $_SESSION['message'] = 'Data berhasil diperbarui!';
                $_SESSION['message_type'] = 'success';
                header("Location: kelola.php");
                exit;
            } else {
                $error = "Error: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Indikator Aspek</title>
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
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit text-info"></i> Edit Data Indikator Kinerja</h1>
                <a href="kelola.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-alt"></i> Form Edit Data</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="aspek">Aspek <span class="text-danger">*</span></label>
                            <input type="text" id="aspek" name="aspek" class="form-control"
                                   value="<?= isset($_POST['aspek']) ? htmlspecialchars($_POST['aspek']) : htmlspecialchars($data['aspek']) ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tahun_anggaran">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" id="tahun_anggaran" name="tahun_anggaran" class="form-control"
                                   value="<?= isset($_POST['tahun_anggaran']) ? (int)$_POST['tahun_anggaran'] : $data['tahun_anggaran'] ?>"
                                   min="2000" max="2099" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="nilai">Nilai <span class="text-danger">*</span></label>
                            <input type="number" id="nilai" name="nilai" class="form-control"
                                   step="0.01" min="0" max="100"
                                   value="<?= isset($_POST['nilai']) ? htmlspecialchars($_POST['nilai']) : (float)$data['nilai'] ?>" required>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-sm-12">
                                <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Perbarui Data
                                </button>
                                <a href="kelola.php" class="btn btn-secondary btn-lg">
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
</body>
</html>