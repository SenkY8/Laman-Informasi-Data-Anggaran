<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function nf($n){ return number_format($n,0,',','.'); }
function pf($n){ return number_format($n,2,',','.'); }

// ===== FILTER TAHUN =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Daftar tahun dari database
$tahun_list_query = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM realisasi_jenis_belanja ORDER BY tahun DESC");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}
if(empty($tahun_list)) {
    $tahun_list = [date('Y')];
}

// CEK APAKAH TAHUN YANG DIPILIH ADA DATA
$cek_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM realisasi_jenis_belanja WHERE tahun = $tahun"));
if($cek_data['total'] == 0 && !empty($tahun_list)) {
    $tahun = $tahun_list[0];
}

$total = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        SUM(jml_pagu) AS total_pagu,
        SUM(jml_blokir) AS total_blokir,
        SUM(jml_pagu_efektif) AS total_pagu_efektif,
        SUM(realisasi_seluruh_1) AS total_real_1,
        SUM(realisasi_seluruh_2) AS total_real_2,
        SUM(kas_basis) AS total_kas_basis,
        SUM(akral) AS total_akral,
        SUM(sisa_seluruh_kas) AS total_sisa_seluruh_kas,
        SUM(sisa_seluruh_akrual) AS total_sisa_seluruh_akrual,
        SUM(sisa_efektif_kas) AS total_sisa_efektif_kas,
        SUM(sisa_efektif_akrual) AS total_sisa_efektif_akrual
    FROM realisasi_jenis_belanja
    WHERE tahun = $tahun
"));

$persen_blokir_total         = ($total['total_pagu']>0) ? ($total['total_blokir']/$total['total_pagu']*100) : 0;
$persen_real_seluruh_1_total = ($total['total_pagu']>0) ? ($total['total_real_1']/$total['total_pagu']*100) : 0;
$persen_real_seluruh_2_total = ($total['total_pagu']>0) ? ($total['total_real_2']/$total['total_pagu']*100) : 0;
$persen_kas_basis_total      = ($total['total_pagu_efektif']>0) ? ($total['total_kas_basis']/$total['total_pagu_efektif']*100) : 0;
$persen_akrual_total         = ($total['total_pagu_efektif']>0) ? ($total['total_akral']/$total['total_pagu_efektif']*100) : 0;

$data_tambahan = mysqli_query($koneksi, "SELECT * FROM target_realisasi ORDER BY id ASC");

// ===== FIX: TAMBAHKAN WHERE tahun = $tahun UNTUK PERCEPATAN =====
$spm = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(jumlah_belanja) AS total_spm FROM spm_berjalan WHERE tahun = $tahun
"));
$total_spm = $spm['total_spm'] ?? 0;

// ===== FIX: TAMBAHKAN WHERE tahun = $tahun UNTUK PERCEPATAN =====
$percepatan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(jumlah - IFNULL(realisasi,0)) AS total_percepatan_selisih FROM percepatan WHERE tahun = $tahun
"));
$total_percepatan = $percepatan['total_percepatan_selisih'] ?? 0;

$total_spm_percepatan = $total_spm + $total_percepatan;

$bulanSekarang = (int)date('n');
if ($bulanSekarang >= 1 && $bulanSekarang <= 3) {
    $currentTw = 1;
} elseif ($bulanSekarang >= 4 && $bulanSekarang <= 6) {
    $currentTw = 2;
} elseif ($bulanSekarang >= 7 && $bulanSekarang <= 9) {
    $currentTw = 3;
} else {
    $currentTw = 4;
}

$targetNasRow = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM target_realisasi_nasional WHERE tw = '".$currentTw."' ORDER BY id DESC LIMIT 1"
));

if ($targetNasRow) {
    $targetNasKas    = (float)($targetNasRow['kas_basis'] ?? 0);
    $targetNasPersen = (float)($targetNasRow['akrual_persen'] ?? 0);
} else {
    $targetNasKas    = 0;
    $targetNasPersen = 0;
}

$namaBulanID = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$currMonth = (int)date('n');
$currYear  = (int)date('Y');
$lastDay   = (int)date('t');
$labelTargetAkhirBulan = $lastDay . ' ' . $namaBulanID[$currMonth] . ' ' . $currYear;

$akr_total_row = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(jumlah) AS total_penjelasan_akrual FROM belanja_akrual WHERE tahun = $tahun
"));
$total_penjelasan_akrual = $akr_total_row['total_penjelasan_akrual'] ?? 0;

$activeMenu = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Data Realisasi Anggaran BBKK Batam</title>
<link href="../asset/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
<style>
/* sidebar */
.bg-gradient-primary {
    background-color: #0DBBCB !important;
    background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
}

/* warna link side bar */
.sidebar .nav-item .nav-link {
    color: #ffffff !important;
}
.sidebar .nav-item .nav-link:hover {
    background-color: #009AA8 !important;
}

/* aktif menu */
.sidebar .nav-item.active .nav-link {
    background-color: #0DBBCB !important;
    color: #ffffff !important;
}

/* button utama */
.btn-primary {
    background-color: #0DBBCB !important;
    border-color: #0DBBCB !important;
}
.btn-primary:hover {
    background-color: #0b9e88ff !important;
}

/* header table */
.table thead th {
    background-color: #0DBBCB !important;
    color: white !important;
}

/* card border */
.card {
    border-left: 4px solid #0DBBCB !important;
}

/* Baris Target Realisasi Nasional */
.target-nasional-row {
    background-color: #ffd23dff !important;
}

.target-nasional-row td {
    color: #000 !important;
    font-weight: bold !important;
}

.center { text-align:center; }
.right { text-align:right; }
.total-row { font-weight:bold; background:#f0f0f0; }

/* FILTER STYLING */
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
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item active">
            <a class="nav-link" href="index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a>
        </li>
        <li class="nav-item"><a class="nav-link" href="../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../spm_berjalan/index.php"><span>SPM Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a></li>
        <li class="nav-item">
          <a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php">
              <span>Permasalahan & Rencana Tindak Lanjut</span>
          </a>
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
        <li class="nav-item">
            <a class="nav-link" href="../pk/index.php">
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

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Realisasi berdasarkan Jenis belanja</h2>
            <p>Data Realisasi Anggaran BBKK Batam</p>
            <hr>

            <!-- FILTER TAHUN REALISASI -->
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

            <a href="tambah.php?tahun=<?= $tahun ?>" class="btn btn-primary mb-3">Tambah Data</a>
            <div class="card shadow mb-4">
            <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th rowspan="3" class="center">No</th>
                        <th rowspan="3" class="center">Uraian Belanja</th>
                        <th rowspan="3" class="center">Jml. Pagu Harian</th>
                        <th colspan="2" rowspan="2" class="center">Jumlah Blokir</th>
                        <th rowspan="3" class="center">Jml. Pagu Efektif</th>
                        <th colspan="4" class="center">Realisasi (Seluruh Pagu)</th>
                        <th colspan="4" class="center">Realisasi (Pagu Efektif)</th>
                        <th colspan="2" class="center">Sisa Anggaran (Seluruh Pagu)</th>
                        <th colspan="2" class="center">Sisa Anggaran (Pagu Efektif)</th>
                        <th rowspan="3" class="center">Aksi</th>
                    </tr>
                    <tr>
                        <th colspan="2" class="center">Kas Basis</th>
                        <th colspan="2" class="center">Akrual</th>

                        <th colspan="2" class="center">Kas Basis</th>
                        <th colspan="2" class="center">Akrual</th>

                        <th rowspan="2" class="center">Kas Basis</th>
                        <th rowspan="2" class="center">Akrual</th>

                        <th rowspan="2" class="center">Kas Basis</th>
                        <th rowspan="2" class="center">Akrual</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no=1;
                $q=mysqli_query($koneksi,"SELECT * FROM realisasi_jenis_belanja WHERE tahun = $tahun ORDER BY id ASC");
                while($d=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td><?= $d['uraian_belanja'] ?></td>
                    <td class="right"><?= nf($d['jml_pagu']) ?></td>
                    <td class="right"><?= nf($d['jml_blokir']) ?></td>
                    <td class="center"><?= pf($d['persen_blokir']) ?>%</td>
                    <td class="right"><?= nf($d['jml_pagu_efektif']) ?></td>
                    <td class="right"><?= nf($d['realisasi_seluruh_1']) ?></td>
                    <td class="center"><?= pf($d['persen_realisasi_seluruh_1']) ?>%</td>
                    <td class="right"><?= nf($d['realisasi_seluruh_2']) ?></td>
                    <td class="center"><?= pf($d['persen_realisasi_seluruh_2']) ?>%</td>
                    <td class="right"><?= nf($d['kas_basis']) ?></td>
                    <td class="center"><?= pf($d['persen_kas_basis']) ?>%</td>
                    <td class="right"><?= nf($d['akral']) ?></td>
                    <td class="center"><?= pf($d['persen_akrual']) ?>%</td>
                    <td class="right"><?= nf($d['sisa_seluruh_kas']) ?></td>
                    <td class="right"><?= nf($d['sisa_seluruh_akrual']) ?></td>
                    <td class="right"><?= nf($d['sisa_efektif_kas']) ?></td>
                    <td class="right"><?= nf($d['sisa_efektif_akrual']) ?></td>
                    <td class="center">
                        <a href="edit.php?id=<?= $d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="hapus.php?id=<?= $d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
                <tr class="total-row">
                    <td colspan="2" class="center">Jumlah</td>
                    <td class="right"><?= nf($total['total_pagu']) ?></td>
                    <td class="right"><?= nf($total['total_blokir']) ?></td>
                    <td class="center"><?= pf($persen_blokir_total) ?>%</td>
                    <td class="right"><?= nf($total['total_pagu_efektif']) ?></td>
                    <td class="right"><?= nf($total['total_real_1']) ?></td>
                    <td class="center"><?= pf($persen_real_seluruh_1_total) ?>%</td>
                    <td class="right"><?= nf($total['total_real_2']) ?></td>
                    <td class="center"><?= pf($persen_real_seluruh_2_total) ?>%</td>
                    <td class="right"><?= nf($total['total_kas_basis']) ?></td>
                    <td class="center"><?= pf($persen_kas_basis_total) ?>%</td>
                    <td class="right"><?= nf($total['total_akral']) ?></td>
                    <td class="center"><?= pf($persen_akrual_total) ?>%</td>
                    <td class="right"><?= nf($total['total_sisa_seluruh_kas']) ?></td>
                    <td class="right"><?= nf($total['total_sisa_seluruh_akrual']) ?></td>
                    <td class="right"><?= nf($total['total_sisa_efektif_kas']) ?></td>
                    <td class="right"><?= nf($total['total_sisa_efektif_akrual']) ?></td>
                    <td class="center">-</td>
                </tr>
                </tbody>
            </table>
            </div>
            </div>

            <h4 class="mt-4 mb-3">
                Target realisasi sampai dengan akhir bulan (<?= htmlspecialchars($labelTargetAkhirBulan) ?>)
            </h4>
            
            <div class="card shadow">
            <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="center">No</th>
                        <th class="center">Uraian</th>
                        <th class="center">Kas Basis</th>
                        <th class="center">Akrual Basis</th>
                    </tr>
                </thead>
            <tbody>
    <tr>
        <td class="center">1</td>
        <td>Data realisasi sampai dengan hari ini</td>
        <td class="right"><?= nf($total['total_kas_basis']) ?></td>
        <td class="right"><?= nf($total['total_akral']) ?></td>
    </tr>
    <tr>
        <td class="center">2</td>
        <td>SPM dalam perjalanan</td>
        <td class="right"><?= nf($total_spm) ?></td>
        <td class="right"><?= nf($total_spm) ?></td>
    </tr>
    <tr>
        <td class="center">3</td>
        <td>RPK/RPD Bulan Berjalan</td>
        <td class="right"><?= nf($total_percepatan) ?></td>
        <td class="right"><?= nf($total_percepatan) ?></td>
    </tr>

    <?php
    $no = 4;
    $jumlah_kas = $total['total_kas_basis'] + $total_spm + $total_percepatan;
    $jumlah_akrual = $total['total_akral'] + $total_spm + $total_percepatan;

    while($dt = mysqli_fetch_assoc($data_tambahan)){
        $jumlah_kas += $dt['kas_basis'];
        $jumlah_akrual += $dt['akrual_basis'];
    ?>
    <tr>
        <td class="center"><?= $no++ ?></td>
        <td><?= $dt['uraian'] ?></td>
        <td class="right"><?= nf($dt['kas_basis']) ?></td>
        <td class="right"><?= nf($dt['akrual_basis']) ?></td>
    </tr>
    <?php } ?>

    <?php
    $jumlah_akrual = $jumlah_akrual - $total_penjelasan_akrual;
    ?>

    <tr class="total-row">
        <td colspan="2" class="center"><strong>Jumlah</strong></td>
        <td class="right"><?= nf($jumlah_kas) ?></td>
        <td class="right"><?= nf($jumlah_akrual) ?></td>
    </tr>
    <tr class="total-row">
        <td colspan="2" class="center"><strong>Persentase realisasi berdasarkan pagu efektif</strong></td>
        <td class="center">
            <?= pf(($total['total_pagu_efektif']>0)?($jumlah_kas/$total['total_pagu_efektif']*100):0) ?>%
        </td>
        <td class="center">
            <?= pf(($total['total_pagu_efektif']>0)?($jumlah_akrual/$total['total_pagu_efektif']*100):0) ?>%
        </td>
    </tr>
    <tr class="total-row">
        <td colspan="2" class="center"><strong>Persentase realisasi berdasarkan seluruh pagu</strong></td>
        <td class="center">
            <?= pf(($total['total_pagu']>0)?($jumlah_kas/$total['total_pagu']*100):0) ?>%
        </td>
        <td class="center">
            <?= pf(($total['total_pagu']>0)?($jumlah_akrual/$total['total_pagu']*100):0) ?>%
        </td>
    </tr>

    <!-- BARIS TARGET REALISASI NASIONAL -->
    <tr class="total-row target-nasional-row">
        <td colspan="2" class="center"><strong>Target realisasi nasional</strong></td>
        <td class="right">TW <?= $currentTw ?></td>
        <td class="right"><?= pf($targetNasPersen) ?>%</td>
    </tr>

    <!-- TOMBOL TAMBAH / EDIT / DELETE TARGET NASIONAL -->
    <tr>
        <td colspan="4" class="center">
            <a href="tambah_target_nasional.php" class="btn btn-sm btn-primary">
                Tambah Data
            </a>

            <?php if ($targetNasRow): ?>
                <a href="edit_target_nasional.php?id=<?= $targetNasRow['id'] ?>" class="btn btn-sm btn-warning">
                    Edit
                </a>
                <a href="hapus_target_nasional.php?id=<?= $targetNasRow['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Yakin ingin menghapus data target realisasi nasional?');">
                    Delete
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-warning" disabled>Edit</button>
                <button class="btn btn-sm btn-danger" disabled>Delete</button>
            <?php endif; ?>
        </td>
    </tr>

    </tbody>
            </table>
            </div>
            </div>

        </div>
    </div>
</div>

<script>
function filterTahun(tahun) {
    window.location.href = '?tahun=' + tahun;
}
</script>
</body>
</html>