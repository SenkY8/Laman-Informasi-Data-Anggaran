<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }

// ===== FILTER TAHUN (BERBASIS SESSION) =====
// Ambil dari URL jika ada, jika tidak gunakan session, jika tidak ada gunakan tahun saat ini
if(isset($_GET['tahun'])) {
    $_SESSION['tahun_filter'] = (int)$_GET['tahun'];
}
$tahun = isset($_SESSION['tahun_filter']) ? (int)$_SESSION['tahun_filter'] : (int)date('Y');
$_SESSION['tahun_filter'] = $tahun;

// Daftar tahun dari database (gabungan dari pnbp dan pnbp_total_pagu_efektif)
$tahun_list_query = mysqli_query($koneksi, "
    SELECT DISTINCT tahun FROM pnbp 
    UNION 
    SELECT DISTINCT tahun FROM pnbp_total_pagu_efektif 
    ORDER BY tahun DESC
");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}

// Tambahkan tahun saat ini jika belum ada
$tahun_sekarang = (int)date('Y');
if(!in_array($tahun_sekarang, $tahun_list)) {
    $tahun_list[] = $tahun_sekarang;
    sort($tahun_list);
    $tahun_list = array_reverse($tahun_list);
}

if(empty($tahun_list)) {
    $tahun_list = [$tahun_sekarang];
}

// Jika tahun yang dipilih tidak ada di list, gunakan tahun pertama di list
if(!in_array($tahun, $tahun_list)) {
    $tahun = $tahun_list[0];
    $_SESSION['tahun_filter'] = $tahun;
}

// Bulan (header tabel)
$months = [
  'jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei','jun'=>'Jun',
  'jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'
];

// Target Bulan Berjalan
$TARGET_BULAN_BERJALAN = 420413000;

// ===== AMBIL DATA PNBP DENGAN FILTER TAHUN =====
$no = 1;
$q = mysqli_query($koneksi, "SELECT * FROM pnbp WHERE tahun = $tahun ORDER BY id ASC");

// Fetch semua data
$rows = [];
while($r = mysqli_fetch_assoc($q)) $rows[] = $r;

// Hitung total
$tot_target = 0;
$tot_akum   = 0;
$tot_months = array_fill_keys(array_keys($months), 0);

foreach($rows as $r){
    $tot_target += (float)$r['target'];

    $akum = 0;
    foreach($months as $k=>$lbl){
        $v = (float)$r[$k];
        $akum += $v;
        $tot_months[$k] += $v;
    }
    $tot_akum += $akum;
}

$tot_persen = ($tot_target > 0) ? ($tot_akum / $tot_target) : 0;

// % realisasi bulan berjalan
$persen_bulan = [];
foreach($months as $k=>$lbl){
    $persen_bulan[$k] = ($TARGET_BULAN_BERJALAN > 0) ? ($tot_months[$k] / $TARGET_BULAN_BERJALAN) : 0;
}

// ===== TOTAL PAGU EFEKTIF DENGAN FILTER TAHUN =====
$total_pagu_efektif = 0;
$tp_id = 0;
$qtp = mysqli_query($koneksi, "SELECT id, total_pagu_efektif FROM pnbp_total_pagu_efektif WHERE tahun = $tahun AND id=1 LIMIT 1");
if($qtp && mysqli_num_rows($qtp) > 0){
    $rtp = mysqli_fetch_assoc($qtp);
    $tp_id = (int)($rtp['id'] ?? 0);
    $total_pagu_efektif = (float)($rtp['total_pagu_efektif'] ?? 0);
}

// ===== REKOMENDASI DENGAN FILTER TAHUN =====
$rekomendasi_text = '';
$rekomendasi_id   = 0;
$qr = mysqli_query($koneksi, "SELECT id, rekomendasi FROM pnbp_rekomendasi WHERE tahun = $tahun AND id=1 LIMIT 1");
if($qr && mysqli_num_rows($qr) > 0){
    $rr = mysqli_fetch_assoc($qr);
    $rekomendasi_id   = (int)($rr['id'] ?? 0);
    $rekomendasi_text = (string)($rr['rekomendasi'] ?? '');
}

// ===== MP PNBP =====
$mp_pnbp = 0.86 * (float)$tot_akum;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Data Realisasi PNBP</title>

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

/* KHUSUS ROW REKOMENDASI: PAKSA PUTIH */
.rekomendasi-row td{ background:#fff !important; }

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
        <li class="nav-item active">
            <a class="nav-link" href="index.php">
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
    <!-- END SIDEBAR -->

    <!-- CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Data Realisasi PNBP</h2>
            <p>Data Realisasi Anggaran BBKK Batam</p>
            <hr>

            <!-- FILTER TAHUN PNBP -->
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

                    <?php if((float)$total_pagu_efektif <= 0): ?>
                        <a href="tambah_total_pagu_efektif.php?tahun=<?= $tahun ?>" class="btn btn-primary">Tambah Total Pagu Efektif</a>
                    <?php endif; ?>

                    <?php if(trim($rekomendasi_text) === ''): ?>
                        <a href="tambah_rekomen.php?tahun=<?= $tahun ?>" class="btn btn-primary">Tambah Rekomendasi</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-body table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="center" style="width:60px;">No</th>
                                <th rowspan="2" class="center">MAK</th>
                                <th rowspan="2" class="center">Uraian</th>
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
                                <td colspan="19" class="center">Belum ada data PNBP untuk tahun <?= $tahun ?>.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($rows as $d): ?>
                                <?php
                                    $akum = 0;
                                    foreach($months as $k=>$lbl) $akum += (float)$d[$k];
                                    $pct = ((float)$d['target'] > 0) ? ($akum / (float)$d['target']) : 0;
                                ?>
                                <tr>
                                    <td class="center"><?= $no++ ?></td>
                                    <td class="center"><?= htmlspecialchars($d['mak']) ?></td>
                                    <td><?= htmlspecialchars($d['uraian']) ?></td>
                                    <td class="right"><?= nf0($d['target']) ?></td>
                                    <td class="right"><?= nf0($akum) ?></td>
                                    <td class="center"><?= pf($pct * 100) ?>%</td>

                                    <?php foreach($months as $k=>$lbl): ?>
                                        <td class="right"><?= nf0($d[$k]) ?></td>
                                    <?php endforeach; ?>

                                    <td class="center">
                                        <a href="edit.php?id=<?= (int)$d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus.php?id=<?= (int)$d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- JUMLAH -->
                            <tr class="total-row">
                                <td colspan="3" class="center">Jumlah</td>
                                <td class="right"><?= nf0($tot_target) ?></td>
                                <td class="right"><?= nf0($tot_akum) ?></td>
                                <td class="center"><?= pf($tot_persen * 100) ?>%</td>
                                <?php foreach($months as $k=>$lbl): ?>
                                    <td class="right"><?= nf0($tot_months[$k]) ?></td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>

                            <!-- TARGET BULAN BERJALAN -->
                            <tr class="total-row" style="background:#fff3cd;">
                                <td colspan="6" class="center"><strong>Target realisasi Bulan berjalan</strong></td>
                                <?php foreach($months as $k=>$lbl): ?>
                                    <td class="right"><strong><?= nf0($TARGET_BULAN_BERJALAN) ?></strong></td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>

                            <!-- % REALISASI BULAN BERJALAN -->
                            <tr class="total-row" style="background:#d1ecf1;">
                                <td colspan="6" class="center"><strong>% Realisasi Bulan berjalan</strong></td>
                                <?php foreach($months as $k=>$lbl): ?>
                                    <td class="center"><strong><?= pf($persen_bulan[$k] * 100) ?>%</strong></td>
                                <?php endforeach; ?>
                                <td></td>
                            </tr>

                            <!-- MP PNBP -->
                            <tr class="total-row" style="background:#c3e6cb;">
                                <td colspan="6" class="center"><strong>Maksimal Pencairan (MP) PNBP</strong></td>
                                <td colspan="12" style="white-space:normal;">
                                    <strong><?= nf0($mp_pnbp) ?></strong>
                                </td>
                                <td></td>
                            </tr>

                            <!-- TOTAL PAGU EFEKTIF PNBP -->
                            <tr class="total-row" style="background:#e2e3ff;">
                                <td colspan="6" class="center"><strong>Total Pagu Efektif PNBP T.A. <?= $tahun ?></strong></td>
                                <td colspan="12" style="white-space:normal;">
                                    <strong><?= nf0($total_pagu_efektif) ?></strong>
                                </td>
                                <td class="center">
                                    <?php if((float)$total_pagu_efektif > 0): ?>
                                        <a href="hapus_total_pagu_efektif.php?tahun=<?= $tahun ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus Total Pagu Efektif ini?');">
                                           Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- REKOMENDASI -->
                            <tr class="total-row rekomendasi-row">
                                <td colspan="6" class="center"><strong>Rekomendasi T.A. <?= $tahun ?></strong></td>
                                <td colspan="12" style="white-space:normal;">
                                    <strong><?= nl2br(htmlspecialchars($rekomendasi_text)) ?></strong>
                                </td>
                                <td class="center">
                                    <?php if(trim($rekomendasi_text) !== ''): ?>
                                        <a href="edit_rekomen.php?tahun=<?= $tahun ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus_rekomen.php?tahun=<?= $tahun ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus rekomendasi ini?');">
                                           Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function filterTahun(tahun) {
    // Tetap di halaman PNBP, hanya ubah parameter tahun
    window.location.href = window.location.pathname + '?tahun=' + tahun;
}
</script>
</body>
</html>