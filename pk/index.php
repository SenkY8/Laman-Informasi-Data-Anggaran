<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }
function pf2($n){ return number_format((float)$n,2,',','.'); }

// Format cerdas: jika angka bulat (1.0) tampilkan tanpa desimal (1), jika ada desimal tampilkan 2 desimal
function format_smart($n) {
    $val = (float)$n;
    if ($val == (int)$val) {
        return number_format($val, 0, ',', '.');
    }
    return number_format($val, 2, ',', '.');
}

// ===== FILTER TAHUN (BERBASIS SESSION) =====
// Ambil dari URL jika ada, jika tidak gunakan session, jika tidak ada gunakan tahun saat ini
if(isset($_GET['tahun'])) {
    $_SESSION['tahun_filter'] = (int)$_GET['tahun'];
}
$tahun = isset($_SESSION['tahun_filter']) ? (int)$_SESSION['tahun_filter'] : (int)date('Y');
$_SESSION['tahun_filter'] = $tahun;

// Daftar tahun dari database
$tahun_list_query = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM pk_2025_kinerja ORDER BY tahun DESC");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}
if(empty($tahun_list)) {
    $tahun_list = [(int)date('Y')];
}

// CEK APAKAH TAHUN YANG DIPILIH ADA DATA
$cek_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pk_2025_kinerja WHERE tahun = $tahun"));
if($cek_data['total'] == 0 && !empty($tahun_list)) {
    $tahun = $tahun_list[0];
    $_SESSION['tahun_filter'] = $tahun;
}

// Bulan (header tabel)
$months = [
  'jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei','jun'=>'Jun',
  'jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'
];

// Ambil data Perjanjian Kinerja
$no = 1;
$q = mysqli_query($koneksi, "SELECT * FROM pk_2025_kinerja WHERE tahun = $tahun ORDER BY id ASC");

// Untuk hitung total, kita fetch semua dulu
$rows = [];
while($r = mysqli_fetch_assoc($q)) $rows[] = $r;

// Hitung total
$tot_target = 0;
$tot_akum   = 0;
$tot_months = array_fill_keys(array_keys($months), 0);

foreach($rows as $r){
    $akum = 0;
    foreach($months as $k=>$lbl){
        $v = (float)$r[$k];
        $akum += $v;
        $tot_months[$k] += $v;
    }
    $tot_akum += $akum;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjanjian Kinerja</title>
    <link href="../asset/css/bootstrap.min.css" rel="stylesheet">
    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        /* SIDEBAR WARNA KEMENKES */
        .bg-gradient-primary {
            background-color: #0DBBCB !important;
            background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
        }

        /* WARNA LINK SIDEBAR */
        .sidebar .nav-item .nav-link {
            color: #ffffff !important;
        }
        .sidebar .nav-item .nav-link:hover {
            background-color: #009AA8 !important;
        }

        /* ACTIVE MENU */
        .sidebar .nav-item.active .nav-link {
            background-color: #0DBBCB !important;
            color: #ffffff !important;
        }

        /* BUTTON UTAMA */
        .btn-primary {
            background-color: #0DBBCB !important;
            border-color: #0DBBCB !important;
        }
        .btn-primary:hover {
            background-color: #0b9e88ff !important;
        }

        /* HEADER TABLE */
        .table thead th {
            background-color: #0DBBCB !important;
            color: white !important;
        }

        /* CARD BORDER KEMENKES */
        .card {
            border-left: 4px solid #0DBBCB !important;
        }

        .center { text-align:center; }
        .right  { text-align:right; }
        .table-responsive { -webkit-overflow-scrolling: touch; }
        .table th, .table td { white-space: nowrap; font-size: 13px; }
        .total-row { font-weight:bold; background:#f0f0f0; }

        /* FILTER TAHUN */
        .filter-container {
            margin-bottom: 15px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 4px;
            border-left: 4px solid #0DBBCB;
        }

        .filter-container label {
            font-weight: 600;
            color: #0DBBCB;
            margin-bottom: 0;
            margin-right: 10px;
            display: inline-block;
        }

        .filter-container select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- SIDEBAR -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <li class="nav-item">
            <a class="nav-link" href="../realisasi_jenis_belanja/index.php">
                <span>Realisasi Berdasarkan Jenis Belanja</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../penjelasan_belanja_akrual/index.php">
                <span>Penjelasan Belanja Akrual</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../spm_berjalan/index.php">
                <span>SPM Berjalan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../upaya_percepatan/index.php">
                <span>RPK/RPD Bulan Berjalan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php">
                <span>Permasalahan & Rencana Tindak Lanjut</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../email/index.php">
                <span>Broadcast Instruksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pnbp/index.php">
                <span>Data Realisasi PNBP</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <span>Perjanjian Kinerja</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../komparasi/index.php">
                <span>Komparasi Pelaksanaan Anggaran</span>
            </a>
        </li>
        <hr class="sidebar-divider my-2">

        <li class="nav-item">
            <a class="nav-link" href="../logout.php"><span>Kembali</span></a>
        </li>
    </ul>
    <!-- END SIDEBAR -->

    <!-- CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Perjanjian Kinerja</h2>
            <p>Data Perjanjian Kinerja BBKK Batam</p>
            <hr>

            <!-- FILTER TAHUN -->
            <div class="filter-container">
                <label for="tahunSelect">Pilih Tahun:</label>
                <select id="tahunSelect" onchange="filterTahun(this.value)">
                    <?php foreach($tahun_list as $t): ?>
                        <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>>
                            Tahun <?= $t ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:10px;">
                <div class="d-flex flex-wrap" style="gap:10px;">
                    <a href="tambah.php?tahun=<?= $tahun ?>" class="btn btn-primary">Tambah Data</a>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-body table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="center" style="width:60px;">No</th>
                                <th rowspan="2" class="center">Jenis</th>
                                <th rowspan="2" class="center">Deskripsi</th>
                                <th rowspan="2" class="center">Indikator</th>
                                <th rowspan="2" class="center">Target</th>
                                <th colspan="2" rowspan="2" class="center">Akumulasi Realisasi</th>
                                <th colspan="12" class="center">Realisasi Per Bulan</th>
                                <th rowspan="2" class="center" style="width:150px;">Aksi</th>
                            </tr>
                            <tr>
                                <?php foreach($months as $lbl): ?>
                                    <th class="center"><?= $lbl ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if(count($rows) === 0): ?>
                            <tr>
                                <td colspan="21" class="center">Belum ada data Perjanjian Kinerja untuk tahun <?= $tahun ?>.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($rows as $d): ?>
                                <?php
                                    $akum = 0;
                                    foreach($months as $k=>$lbl) $akum += (float)$d[$k];
                                    
                                    $target_str = (string)$d['target'];
                                    $target_str = str_replace('%', '', $target_str);
                                    $target_str = str_replace(',', '.', $target_str);
                                    $target_val = (float)$target_str;
                                    
                                    $pct = ($target_val > 0) ? min($akum / $target_val, 1.0) : 0;
                                ?>
                                <tr>
                                    <td class="center"><?= $no++ ?></td>
                                    <td class="center"><?= htmlspecialchars($d['jenis']) ?></td>
                                    <td><?= htmlspecialchars($d['deskripsi']) ?></td>
                                    <td><?= htmlspecialchars($d['indikator']) ?></td>
                                    <td class="right"><?= htmlspecialchars($d['target']) ?></td>
                                    <td class="right"><?= pf2($akum) ?></td>
                                    <td class="center"><?= pf($pct * 100) ?>%</td>

                                    <?php foreach($months as $k=>$lbl): ?>
                                        <td class="right"><?= format_smart($d[$k]) ?></td>
                                    <?php endforeach; ?>

                                    <td class="center">
                                        <a href="edit.php?id=<?= (int)$d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus.php?id=<?= (int)$d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                           Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- JUMLAH -->
                            <tr class="total-row">
                                <td colspan="5" class="center">Jumlah</td>
                                <td class="right"><?= pf2($tot_akum) ?></td>
                                <td class="center">-</td>
                                <?php foreach($months as $k=>$lbl): ?>
                                    <td class="right"><?= pf2($tot_months[$k]) ?></td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>

                        <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="../asset/vendor/jquery/jquery.min.js"></script>
<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
function filterTahun(tahun) {
    // Tetap di halaman PK, hanya ubah parameter tahun
    window.location.href = window.location.pathname + '?tahun=' + tahun;
}
</script>
</body>
</html>