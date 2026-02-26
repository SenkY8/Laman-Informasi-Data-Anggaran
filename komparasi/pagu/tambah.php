<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../login.php");
    exit;
}
include "../../koneksi.php";

$error = '';

if(isset($_POST['simpan'])) {
    $tahun = trim($_POST['tahun'] ?? '');
    $pagu = (float)($_POST['pagu'] ?? 0);
    $realisasi = (float)($_POST['realisasi'] ?? 0);
    
    // Validasi
    if (!$tahun) {
        $error = "Tahun harus diisi!";
    } else {
        // Cek apakah data tahun sudah ada
        $cek = mysqli_query($koneksi, "SELECT id FROM komparasi_nilai_pagu WHERE tahun = '$tahun' AND uraian = 'PAGU ANGGARAN'");
        
        if (mysqli_num_rows($cek) > 0) {
            $error = "Data tahun $tahun sudah ada! Gunakan Edit untuk mengubahnya.";
        } else {
            // Insert PAGU ANGGARAN
            $sql1 = "INSERT INTO komparasi_nilai_pagu (tahun, uraian, pagu, realisasi)
                    VALUES ('$tahun', 'PAGU ANGGARAN', $pagu, 0)";
            
            // Insert REALISASI
            $sql2 = "INSERT INTO komparasi_nilai_pagu (tahun, uraian, pagu, realisasi)
                    VALUES ('$tahun', 'REALISASI', $pagu, $realisasi)";
            
            if(mysqli_query($koneksi, $sql1) && mysqli_query($koneksi, $sql2)) {
                $_SESSION['message'] = 'Data berhasil ditambahkan!';
                $_SESSION['message_type'] = 'success';
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Error: " . mysqli_error($koneksi);
            }
        }
    }
}

$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Pagu dan Realisasi</title>
    <link href="../../asset/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .bg-gradient-primary {
            background-color: #0DBBCB !important;
            background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
        }
        .btn-primary {
            background-color: #0DBBCB !important;
            border-color: #0DBBCB !important;
        }
        .btn-primary:hover {
            background-color: #0b9e88ff !important;
        }
        .card {
            border-left: 4px solid #0DBBCB !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item">
            <a class="nav-link" href="../../realisasi_jenis_belanja/index.php">
                <span>Realisasi Berdasarkan Jenis Belanja</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../penjelasan_belanja_akrual/index.php">
                <span>Penjelasan Belanja Akrual</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../spm_berjalan/index.php">
                <span>SPM Berjalan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../pk_2025_kinerja/index.php">
                <span>Perjanjian Kinerja 2025</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../upaya_percepatan/index.php">
                <span>RPK/RPD Bulan Berjalan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../permasalahan_dan_rencana_tindaklanjut/index.php">
                <span>Permasalahan & Rencana Tindak Lanjut</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../email/index.php">
                <span>Broadcast Instruksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../pnbp/index.php">
                <span>Data Realisasi PNBP</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="../index.php">
                <span>Komparasi Pelaksanaan Anggaran</span>
            </a>
        </li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item">
            <a class="nav-link" href="../../logout.php"><span>Kembali</span></a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus text-success"></i> Tambah Data Pagu dan Realisasi</h1>
                <a href="../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tahun">Tahun Anggaran <span class="text-danger">*</span></label>
                                <input type="number" id="tahun" name="tahun" class="form-control" 
                                       value="<?= isset($_POST['tahun']) ? (int)$_POST['tahun'] : $current_year ?>" 
                                       min="2000" max="2099" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="pagu">Pagu <span class="text-danger">*</span></label>
                                <input type="number" id="pagu" name="pagu" class="form-control" 
                                       step="0.01" value="<?= isset($_POST['pagu']) ? (float)$_POST['pagu'] : 0 ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="realisasi">Realisasi <span class="text-danger">*</span></label>
                                <input type="number" id="realisasi" name="realisasi" class="form-control" 
                                       step="0.01" value="<?= isset($_POST['realisasi']) ? (float)$_POST['realisasi'] : 0 ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-sm-12">
                                <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Simpan Data
                                </button>
                                <a href="../index.php" class="btn btn-secondary btn-lg">
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

<script src="../../asset/vendor/jquery/jquery.min.js"></script>
<script src="../../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>