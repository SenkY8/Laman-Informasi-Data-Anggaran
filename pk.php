<?php
session_start();
include "koneksi.php";

// ===== FILTER TAHUN GLOBAL =====
$tahun = isset($_SESSION['tahun_filter']) ? (int)$_SESSION['tahun_filter'] : (int)date('Y');

if(isset($_GET['tahun'])) {
    $_SESSION['tahun_filter'] = (int)$_GET['tahun'];
    $tahun = (int)$_GET['tahun'];
}

// Ambil tahun yang tersedia dari tabel PK
$tahun_list_query = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM pk_2025_kinerja ORDER BY tahun DESC");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}

if(empty($tahun_list)) {
    $tahun_list = [$tahun];
}

// TENTUKAN TAHUN YANG AKAN DITAMPILKAN
$tahun_ditampilkan = $tahun;

// Cek apakah tahun yang dipilih ada datanya
$cek_data_pk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pk_2025_kinerja WHERE tahun = $tahun"));
if($cek_data_pk['total'] == 0) {
    // Jika tidak ada data untuk tahun itu, ambil tahun TERAKHIR yang ada datanya
    $tahun_terakhir = !empty($tahun_list) ? $tahun_list[0] : $tahun;
    $tahun_ditampilkan = $tahun_terakhir;
    
    // Tampilkan pesan info
    $info_tahun = "Data untuk tahun $tahun tidak tersedia. Menampilkan data tahun $tahun_terakhir.";
}

function pf($n){ return number_format((float)$n,2,',','.'); }

// ==================== DATA PK ====================
$qpk = mysqli_query($koneksi, "SELECT * FROM pk_2025_kinerja WHERE tahun = $tahun_ditampilkan ORDER BY id ASC");
$rows_pk = [];
while($r = mysqli_fetch_assoc($qpk)) $rows_pk[] = $r;

// Hitung total akumulasi
$tot_akum = 0;
foreach($rows_pk as $r){
    $akum = 0;
    $months = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
    foreach($months as $k){
        $akum += (float)$r[$k];
    }
    $tot_akum += $akum;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Perjanjian Kinerja</title>

<link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="asset/css/sb-admin-2.min.css" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

.bg-gradient-primary{
    background-color:#0DBBCB !important;
    background-image:linear-gradient(180deg,#0DBBCB 10%,#0DBBCB 100%) !important;
}
.btn-primary{
    background-color:#0DBBCB !important;
    border-color:#0DBBCB !important;
}
.btn-primary:hover{ background-color:#0b9e88ff !important; }

.table {
    border-collapse:collapse;
    background:white;
    border-radius:12px;
    overflow:hidden;
    margin-top:15px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    width:100%;
}

.table thead th{
    background:linear-gradient(135deg, #0DBBCB 0%, #00A3B5 100%);
    color:#fff !important;
    text-align:center;
    padding:12px 8px;
    border:1px solid #cae2e4ff;
    font-size:12px;
    font-weight:700;
    letter-spacing:0.3px;
    white-space:nowrap;
}

.table tbody td {
    padding:10px 8px;
    border:1px solid #ccc;
    font-size:12px;
    color:#374151;
    font-weight:500;
}

.table tbody tr {
    transition:background 0.2s ease;
}

.table tbody tr:hover {
    background-color:#f9fafb;
}

.center{ text-align:center; }
.right{ text-align:right; }
.table-responsive{ 
    -webkit-overflow-scrolling:touch;
    overflow-x: auto;
}

.total-row{
    font-weight:600;
    color:#1f2937;
    background:#e3f0ff;
}

/* ==================== HEADER SECTION ==================== */
.header-section {
    background: linear-gradient(135deg, #0DBBCB 0%, #06a9bd 50%, #0b67b4 100%);
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 20px 50px rgba(13, 187, 203, 0.25);
    position: relative;
    overflow: hidden;
}

.header-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: floatGlow 8s ease-in-out infinite;
}

.header-section::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius: 50%;
    animation: floatGlow2 10s ease-in-out infinite;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 30px;
    position: relative;
    z-index: 2;
    flex-wrap: nowrap;
}

.header-logo {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-logo-img {
    width: 100px;
    height: auto;
    animation: floatBounce 4s ease-in-out infinite;
    filter: brightness(1.2) drop-shadow(0 8px 15px rgba(0, 0, 0, 0.2));
    transition: filter 0.3s ease;
}

.header-logo-img:hover {
    filter: brightness(1.4) drop-shadow(0 12px 25px rgba(0, 0, 0, 0.3));
}

.header-text {
    flex: 1 1 auto;
    min-width: 0;
}

.header-title {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px 0;
    line-height: 1.2;
}

.header-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-weight: 500;
}

.header-action {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-header-back {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s ease;
    text-decoration: none;
    cursor: pointer;
}

.btn-header-back:hover {
    background: rgba(255, 255, 255, 0.35);
    border-color: #fff;
    transform: translateX(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    color: #fff;
}

/* ===== TAHUN DISPLAY DI TENGAH ===== */
.header-center-container {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.15);
    padding: 12px 24px;
    border-radius: 12px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.tahun-display-icon {
    color: #fff;
    font-size: 22px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.tahun-display-text {
    font-size: 15px;
    font-weight: 600;
    color: rgba(255,255,255,0.95);
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.tahun-display-value {
    font-weight: 800;
    font-size: 22px;
    color: #fff;
    background: rgba(255, 255, 255, 0.2);
    padding: 6px 16px;
    border-radius: 8px;
    min-width: 80px;
    text-align: center;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    border: 2px solid rgba(255, 255, 255, 0.4);
}

@keyframes floatGlow {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(-30px, -30px) scale(1.1);
    }
}

@keyframes floatGlow2 {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(20px, 20px) scale(1.1);
    }
}

@keyframes floatBounce {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 1399px) {
    .header-section {
        padding: 35px;
    }
    .header-title {
        font-size: 28px;
    }
    .header-subtitle {
        font-size: 13px;
    }
    .header-logo-img {
        width: 90px;
    }
    .btn-header-back {
        width: 42px;
        height: 42px;
        font-size: 16px;
    }
    .header-center-container {
        padding: 10px 20px;
    }
    .tahun-display-icon {
        font-size: 20px;
    }
    .tahun-display-text {
        font-size: 14px;
    }
    .tahun-display-value {
        font-size: 20px;
        padding: 5px 14px;
        min-width: 75px;
    }
}

@media (max-width: 1199px) {
    .header-section {
        padding: 30px;
    }
    .header-content {
        gap: 25px;
    }
    .header-title {
        font-size: 26px;
    }
    .header-subtitle {
        font-size: 12px;
    }
    .header-logo-img {
        width: 85px;
    }
    .btn-header-back {
        width: 40px;
        height: 40px;
        font-size: 15px;
    }
    .header-center-container {
        padding: 9px 18px;
        gap: 10px;
    }
    .tahun-display-icon {
        font-size: 18px;
    }
    .tahun-display-text {
        font-size: 13px;
    }
    .tahun-display-value {
        font-size: 18px;
        padding: 4px 12px;
        min-width: 70px;
    }
}

@media (max-width: 991.98px) {
    .header-section {
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 18px;
    }
    .header-content {
        gap: 20px;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .header-title {
        font-size: 24px;
    }
    .header-subtitle {
        font-size: 12px;
    }
    .header-logo-img {
        width: 80px;
    }
    .btn-header-back {
        width: 38px;
        height: 38px;
        font-size: 14px;
    }
    .header-center-container {
        order: 3;
        width: 100%;
        justify-content: center;
        margin-top: 15px;
        padding: 10px 20px;
    }
    .tahun-display-icon {
        font-size: 18px;
    }
    .tahun-display-text {
        font-size: 13px;
    }
    .tahun-display-value {
        font-size: 18px;
        padding: 5px 14px;
        min-width: 70px;
    }
}

@media (max-width: 767.98px) {
    #content {
        padding: 1rem !important;
    }
    .header-section {
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
    }
    .header-content {
        gap: 15px;
    }
    .header-title {
        font-size: 20px;
        margin-bottom: 4px;
    }
    .header-subtitle {
        font-size: 11px;
    }
    .header-logo-img {
        width: 70px;
        animation: none !important;
    }
    .header-action {
        display: none;
    }
    .header-center-container {
        padding: 8px 16px;
        margin-top: 12px;
    }
    .tahun-display-icon {
        font-size: 16px;
    }
    .tahun-display-text {
        font-size: 12px;
    }
    .tahun-display-value {
        font-size: 16px;
        padding: 4px 12px;
        min-width: 60px;
    }
    .card {
        border-radius: 10px;
        margin-bottom: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
}

@media (max-width: 575.98px) {
    body {
        font-size: 13px;
    }
    #content {
        padding: 0.8rem !important;
    }
    .header-section {
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 12px;
    }
    .header-content {
        gap: 12px;
        flex-direction: column;
        text-align: center;
    }
    .header-title {
        font-size: 18px;
        margin-bottom: 3px;
        line-height: 1.1;
    }
    .header-subtitle {
        font-size: 10px;
    }
    .header-logo-img {
        width: 60px;
        animation: none !important;
    }
    .header-center-container {
        padding: 6px 14px;
        margin-top: 10px;
        gap: 8px;
    }
    .tahun-display-icon {
        font-size: 14px;
    }
    .tahun-display-text {
        font-size: 11px;
    }
    .tahun-display-value {
        font-size: 14px;
        padding: 3px 10px;
        min-width: 55px;
    }
    .header-action {
        display: none;
    }
}

@media (max-width: 480px) {
    body {
        font-size: 12px;
    }
    #content {
        padding: 0.6rem !important;
    }
    .header-section {
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .header-content {
        gap: 10px;
    }
    .header-title {
        font-size: 16px;
        margin-bottom: 2px;
        line-height: 1.1;
    }
    .header-subtitle {
        font-size: 9px;
    }
    .header-logo-img {
        width: 55px;
        animation: none !important;
    }
    .header-center-container {
        padding: 5px 12px;
        margin-top: 8px;
    }
    .tahun-display-icon {
        font-size: 13px;
    }
    .tahun-display-text {
        font-size: 10px;
    }
    .tahun-display-value {
        font-size: 13px;
        padding: 2px 8px;
        min-width: 50px;
    }
    .header-action {
        display: none;
    }
}

@media (max-width: 380px) {
    #content {
        padding: 0.5rem !important;
    }
    .header-section {
        padding: 12px;
        margin-bottom: 8px;
    }
    .header-content {
        gap: 8px;
    }
    .header-title {
        font-size: 15px;
    }
    .header-subtitle {
        font-size: 8px;
    }
    .header-logo-img {
        width: 50px;
        animation: none !important;
    }
    .header-center-container {
        padding: 4px 10px;
    }
    .tahun-display-text {
        font-size: 9px;
    }
    .tahun-display-value {
        font-size: 12px;
        min-width: 45px;
    }
}
</style>
</head>

<body id="page-top">

<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <!-- ==================== HEADER SECTION ==================== -->
            <div class="header-section mb-4">
                <div class="header-content">
                    <div class="header-logo">
                        <img src="img/kemenkes1.png" alt="Logo Kemenkes" class="header-logo-img">
                    </div>
                    
                    <div class="header-text">
                        <h2 class="header-title">Perjanjian Kinerja</h2>
                        <p class="header-subtitle">BBKK Batam</p>
                    </div>
                    
                    <!-- TAHUN DI TENGAH -->
                    <div class="header-center-container">
                        <i class="fas fa-calendar-alt tahun-display-icon"></i>
                        <span class="tahun-display-text">Tahun:</span>
                        <span class="tahun-display-value"><?= $tahun ?></span>
                    </div>
                    
                    <div class="header-action">
                        <a href="index.php" class="btn-header-back">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TABEL PERJANJIAN KINERJA -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold" style="color:#0DBBCB;">Tabel Perjanjian Kinerja</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="center" style="width:5%;">No</th>
                                <th rowspan="2" class="center">Indikator</th>
                                <th rowspan="2" class="center" style="width:12%;">Target</th>
                                <th colspan="2" class="center" style="width:20%;">Akumulasi Realisasi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if(count($rows_pk) === 0): ?>
                            <tr>
                                <td colspan="5" class="center">Belum ada data Perjanjian Kinerja untuk tahun <?= $tahun ?>.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($rows_pk as $d): ?>
                                <?php
                                    $months = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
                                    $akum = 0;
                                    foreach($months as $k) $akum += (float)$d[$k];
                                    
                                    // Ambil target, hapus %, ganti koma jadi titik
                                    $target_str = (string)$d['target'];
                                    $target_str = str_replace('%', '', $target_str);    // Hapus %
                                    $target_str = str_replace(',', '.', $target_str);   // Ganti koma jadi titik
                                    $target_val = (float)$target_str;
                                    
                                    // Hitung persentase, batasi maksimal 100%
                                    $pct = ($target_val > 0) ? min(($akum / $target_val) * 100, 100) : 0;
                                ?>
                                <tr>
                                    <td class="center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($d['indikator']) ?></td>
                                    <td class="right"><?= htmlspecialchars($d['target']) ?></td>
                                    <td class="right"><?= pf($akum) ?></td>
                                    <td class="center"><?= pf($pct) ?>%</td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- JUMLAH -->
                            <tr class="total-row">
                                <td colspan="3" class="center">Jumlah</td>
                                <td class="right"><?= pf($tot_akum) ?></td>
                                <td></td>
                            </tr>

                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /content-wrapper -->
</div><!-- /wrapper -->

<script src="asset/vendor/jquery/jquery.min.js"></script>
<script src="asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>