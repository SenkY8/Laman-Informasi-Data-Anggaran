<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../login.php");
    exit;
}
include "../../koneksi.php";

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }

// Ambil semua tahun yang ada
$q = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM komparasi_nilai_pagu ORDER BY tahun DESC");
$years = [];
while($r = mysqli_fetch_assoc($q)) {
    $years[] = $r['tahun'];
}

// Ambil data untuk semua tahun
$data = [];
$q = mysqli_query($koneksi, "SELECT * FROM komparasi_nilai_pagu ORDER BY tahun DESC, uraian ASC");
while($r = mysqli_fetch_assoc($q)) {
    $tahun = $r['tahun'];
    if(!isset($data[$tahun])) {
        $data[$tahun] = [];
    }
    if ($r['uraian'] == 'PAGU ANGGARAN') {
        $data[$tahun]['PAGU'] = $r;
    } else if ($r['uraian'] == 'REALISASI') {
        $data[$tahun]['REALISASI'] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pagu & Realisasi</title>
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
        .tahun-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .tahun-header {
            background-color: #0DBBCB;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        .tahun-content {
            padding: 15px;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .data-row:last-child {
            border-bottom: none;
        }
        .data-row strong {
            min-width: 150px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
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
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-folder-open"></i> Kelola Data Pagu & Realisasi</h1>
                <a href="../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <?php if (empty($years)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada data. <a href="tambah.php">Tambah data baru</a>
                </div>
            <?php else: ?>
                <?php foreach($years as $tahun): ?>
                    <div class="tahun-card">
                        <div class="tahun-header">
                            Tahun Anggaran <?= $tahun ?>
                        </div>
                        <div class="tahun-content">
                            <!-- PAGU ANGGARAN -->
                            <div class="data-row">
                                <div>
                                    <strong>PAGU ANGGARAN</strong>
                                    <?php if(isset($data[$tahun]['PAGU'])): ?>
                                        <div style="font-size: 14px; color: #666;">
                                            Nilai: <?= nf0($data[$tahun]['PAGU']['pagu']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="action-buttons">
                                    <a href="edit.php?tahun=<?= $tahun ?>&uraian=PAGU ANGGARAN" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="hapus.php?tahun=<?= $tahun ?>&uraian=PAGU ANGGARAN" class="btn btn-danger btn-sm" onclick="return confirm('Hapus PAGU ANGGARAN tahun <?= $tahun ?>?');">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>

                            <!-- REALISASI -->
                            <div class="data-row">
                                <div>
                                    <strong>REALISASI</strong>
                                    <?php if(isset($data[$tahun]['REALISASI'])): 
                                        $pagu = $data[$tahun]['REALISASI']['pagu'];
                                        $realisasi = $data[$tahun]['REALISASI']['realisasi'];
                                        $persen = ($pagu > 0) ? ($realisasi / $pagu * 100) : 0;
                                    ?>
                                        <div style="font-size: 14px; color: #666;">
                                            Nilai: <?= nf0($realisasi) ?> (<?= pf($persen) ?>%)
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="action-buttons">
                                    <a href="edit.php?tahun=<?= $tahun ?>&uraian=REALISASI" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="hapus.php?tahun=<?= $tahun ?>&uraian=REALISASI" class="btn btn-danger btn-sm" onclick="return confirm('Hapus REALISASI tahun <?= $tahun ?>?');">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mt-3">
                <a href="tambah.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Tambah Data Baru
                </a>
            </div>

        </div>
    </div>
</div>

<script src="../../asset/vendor/jquery/jquery.min.js"></script>
<script src="../../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>