<?php
session_start();
include "koneksi.php";

// ===== FILTER TAHUN GLOBAL (SAMA DENGAN INDEX.PHP) =====
// Ambil tahun dari session yang sudah diset di index.php
$tahun = isset($_SESSION['tahun_filter']) ? (int)$_SESSION['tahun_filter'] : (int)date('Y');

// Jika ada parameter tahun langsung (misal dari link), update session
if(isset($_GET['tahun'])) {
    $_SESSION['tahun_filter'] = (int)$_GET['tahun'];
    $tahun = (int)$_GET['tahun'];
}

// Daftar tahun dari tabel PNBP (untuk validasi)
$tahun_list_query = mysqli_query($koneksi, "
    SELECT DISTINCT tahun FROM pnbp
    UNION
    SELECT DISTINCT tahun FROM pnbp_total_pagu_efektif
    UNION
    SELECT DISTINCT tahun FROM pnbp_rekomendasi
    ORDER BY tahun DESC
");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}
if(empty($tahun_list)) {
    $tahun_list = [(int)date('Y')];
}

// CEK APAKAH TAHUN YANG DIPILIH ADA DATA DI PNBP
$cek_data_pnbp = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pnbp WHERE tahun = $tahun"));
if($cek_data_pnbp['total'] == 0 && !empty($tahun_list)) {
    // Jika tidak ada data untuk tahun itu, gunakan tahun pertama yang tersedia
    $tahun = $tahun_list[0];
    $_SESSION['tahun_filter'] = $tahun;
}

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }

$months = [
  'jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei','jun'=>'Jun',
  'jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'
];

// ==================== DATA PNBP ====================
$q = mysqli_query($koneksi, "SELECT * FROM pnbp WHERE tahun = $tahun ORDER BY id ASC");
$rows = [];
while($r = mysqli_fetch_assoc($q)) $rows[] = $r;

$tot_target = 0;
$tot_months = array_fill_keys(array_keys($months), 0);

foreach($rows as $r){
    $tot_target += (float)$r['target'];
    foreach($months as $k=>$lbl){
        $tot_months[$k] += (float)$r[$k];
    }
}

$tot_realisasi = 0;
foreach($months as $k=>$lbl) $tot_realisasi += (float)$tot_months[$k];
$tot_persen = ($tot_target > 0) ? ($tot_realisasi / $tot_target) : 0;

$total_pagu_efektif = 0;
$qtp = mysqli_query($koneksi, "SELECT total_pagu_efektif FROM pnbp_total_pagu_efektif WHERE tahun = $tahun LIMIT 1");
if($qtp && mysqli_num_rows($qtp) > 0){
    $rtp = mysqli_fetch_assoc($qtp);
    $total_pagu_efektif = (float)($rtp['total_pagu_efektif'] ?? 0);
}

$mp_pnbp = 0.86 * (float)$tot_realisasi;

$chartLabels = array_values($months);
$chartMonthly = [];
foreach($months as $k=>$lbl) $chartMonthly[] = (float)$tot_months[$k];

$chartCumulative = [];
$run = 0;
foreach($chartMonthly as $v){
    $run += (float)$v;
    $chartCumulative[] = $run;
}

$chartCumulativePercent = [];
foreach($chartCumulative as $cumVal){
    $pct = ($tot_target > 0) ? ($cumVal / $tot_target * 100) : 0;
    $chartCumulativePercent[] = $pct;
}

$chartTargetLine = array_fill(0, count($chartLabels), (float)$tot_target);

$rekomendasi_text = '';
$qr = mysqli_query($koneksi, "SELECT rekomendasi FROM pnbp_rekomendasi WHERE tahun = $tahun LIMIT 1");
if($qr && mysqli_num_rows($qr) > 0){
    $rr = mysqli_fetch_assoc($qr);
    $rekomendasi_text = (string)($rr['rekomendasi'] ?? '');
}

$cards = [];
foreach($rows as $d){
    $akum = 0;
    foreach($months as $k=>$lbl) $akum += (float)$d[$k];
    $pct = ((float)$d['target'] > 0) ? ($akum / (float)$d['target']) : 0;
    if((float)$d['target'] > 0){
        $cards[] = [
            'uraian' => (string)$d['uraian'],
            'target' => (float)$d['target'],
            'pct'    => $pct
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Realisasi PNBP</title>

<link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="asset/css/sb-admin-2.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Lemon Mil",sans-serif}
body{background:#f8f9fa;color:#222;scroll-behavior:smooth}

.bg-gradient-primary{
    background-color:#0DBBCB !important;
    background-image:linear-gradient(180deg,#0DBBCB 10%,#0DBBCB 100%) !important;
}
.btn-primary{
    background-color:#0DBBCB !important;
    border-color:#0DBBCB !important;
    transition:all 0.3s ease;
}
.btn-primary:hover{ 
    background-color:#00A3B5 !important;
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(13,187,203,0.35) !important;
}

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
    white-space:nowrap;
}

.table tbody tr {
    transition:background 0.2s ease;
}

.table tbody tr:hover {
    background-color:#f9fafb;
}

.card{ 
    border:none;
    border-left:4px solid #0DBBCB !important;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    transition:all 0.3s ease;
}

.card:hover {
    box-shadow:0 8px 20px rgba(0,0,0,0.12);
    transform:translateY(-2px);
}

.card-body {
    padding:20px;
}

.text-xs {
    font-size:11px;
    letter-spacing:0.5px;
    font-weight:600;
    color:#6b7280;
    text-transform:uppercase;
}

.h5 {
    font-size:18px;
    font-weight:700;
    color:#1f2937;
}

.center{ text-align:center; }
.right{ text-align:right; }
.table-responsive{ 
    -webkit-overflow-scrolling:touch;
    overflow-x:auto;
}

.total-row{
    font-weight:700;
    color:#1f2937;
    background:#e3f0ff;
}

.total-row:hover {
    background:#d6e8ff;
}

:root{
    --panelH:420px;
    --chartH:100%;
}

.equal-panel{
    height:var(--panelH);
    display:flex;
    flex-direction:column;
}

.equal-panel .card-body{
    flex:1 1 auto;
    min-height:0;
}

.chart-wrapper{
    width:100%;
    height:var(--chartH);
    position:relative;
}

#chartPnbp{
    width:100% !important;
    height:100% !important;
    display:block;
}

.pill-panel{
    height:var(--panelH);
    display:flex;
    flex-direction:column;
}

.pill-panel .pill-container{
    flex:1 1 auto;
    min-height:0;
    overflow-y:auto;
    padding-right:6px;
}

.pill-container::-webkit-scrollbar{ width:8px; }
.pill-container::-webkit-scrollbar-thumb{
    background:rgba(13,187,203,0.3);
    border-radius:10px;
}
.pill-container::-webkit-scrollbar-track{ background:transparent; }

.pnbp-pill{
    border-radius:14px;
    background:linear-gradient(135deg, #0DBBCB 0%, #06a9bd 50%, #0b67b4 100%);
    color:#fff;
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    box-shadow:0 4px 12px rgba(13,187,203,0.2);
    margin-bottom:12px;
    min-height:70px;
    transition:all 0.3s ease;
}

.pnbp-pill:hover {
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 8px 24px rgba(13,187,203,0.35);
}

.pnbp-pill-left{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:0;
    flex:1;
}

.pnbp-pill-icon{
    width:44px;
    height:44px;
    border-radius:50%;
    background:rgba(255,255,255,0.25);
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    font-size:18px;
    transition:all 0.3s ease;
}

.pnbp-pill:hover .pnbp-pill-icon {
    transform:scale(1.15);
    background:rgba(255,255,255,0.35);
}

.pnbp-pill-text{ min-width:0; flex:1; }

.pnbp-pill-title{
    font-weight:500;
    font-size:13px;
    line-height:1.2;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    max-width:100%;
}

.pnbp-pill-sub{
    font-weight:600;
    font-size:14px;
    line-height:1.2;
    margin-top:3px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.pnbp-pill-right{
    flex-shrink:0;
    display:flex;
    align-items:center;
    justify-content:center;
}

.pnbp-pill-badge{
    width:60px;
    height:60px;
    border-radius:50%;
    background:rgba(255,255,255,0.2);
    border:2px solid rgba(255,255,255,0.4);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:4px;
    transition:all 0.3s ease;
}

.pnbp-pill:hover .pnbp-pill-badge {
    background:rgba(255,255,255,0.3);
    border-color:rgba(255,255,255,0.6);
    transform:scale(1.1);
}

.pnbp-pill-badge .pct{
    font-weight:700;
    font-size:13px;
    line-height:1;
}

.pnbp-pill-badge .lbl{
    font-weight:600;
    font-size:9px;
    opacity:0.9;
    margin-top:2px;
    line-height:1;
}

.card-header {
    background:#f5f5f5;
    border-bottom:2px solid #0DBBCB;
    padding:15px 20px !important;
}

.card-header h6 {
    color:#0DBBCB;
    font-weight:700;
}

/* ===== SHOW BUTTON KEMBALI DI DESKTOP ===== */
.btn-back-mobile {
    display: inline-block !important;
}

/* ===== TAHUN DISPLAY DI TENGAH ===== */
.header-container {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #ffff 0%, #ffff 100%);
    border-radius: 12px;
    border-left: 5px solid #0DBBCB;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.header-title {
    color: #007bff;
    font-weight: 700;
    font-size: 24px;
    margin: 0;
    line-height: 1.2;
}

.header-subtitle {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

.header-center {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255);
    padding: 10px 20px;
    border-radius: 10px;
    border: 2px solid rgba(13, 187, 203, 0.3);
    box-shadow: 0 4px 12px rgba(13, 187, 203, 0.15);
}

.tahun-display-icon {
    color: #0DBBCB;
    font-size: 20px;
}

.tahun-display-text {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.tahun-display-value {
    font-weight: 800;
    font-size: 18px;
    color: #0DBBCB;
    background: rgba(13, 187, 203, 0.15);
    padding: 4px 12px;
    border-radius: 6px;
    min-width: 70px;
    text-align: center;
}

.header-right {
    display: flex;
    align-items: center;
}

/* ===== RESPONSIF ===== */
@media (max-width:1399px) {
    .chart-wrapper { height:360px }
    .card-body { padding:16px }
    .h5 { font-size:16px }
    .header-title { font-size: 22px; }
    .tahun-display-value { font-size: 17px; }
}

@media (max-width:1199px) {
    body { background:#f8f9fa }
    .chart-wrapper { height:340px }
    .card-body { padding:14px }
    .header-container {
        padding: 12px 16px;
        gap: 12px;
    }
    .header-title { font-size: 20px; }
    .header-center {
        padding: 8px 16px;
    }
}

@media (max-width:991.98px){
    :root{ --panelH:auto; }
    .equal-panel{ height:auto; }
    .pill-panel{ height:auto; }
    .pill-panel .pill-container{ overflow:visible; padding-right:0; }
    .pnbp-pill-title{ max-width:100%; }
    .chart-wrapper { height:320px }
    .card-body { padding:12px }
    .header-container {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    .header-left {
        align-items: center;
        text-align: center;
    }
    .header-center {
        order: 3;
        width: 100%;
        justify-content: center;
    }
    .header-right {
        order: 2;
        width: 100%;
        justify-content: center;
    }
    .header-title { font-size: 18px; }
    .tahun-display-value { font-size: 16px; }
}

@media (max-width:767.98px){
    body { background:#fff }
    .chart-wrapper { height:280px }
    .card-body { padding:10px }
    .h5 { font-size:13px }
    .pnbp-pill { padding:12px 14px; min-height:66px }
    .pnbp-pill-icon { width:40px; height:40px; font-size:16px }
    .pnbp-pill-badge { width:56px; height:56px }
    .pnbp-pill-badge .pct { font-size:12px }
    
    /* Sembunyikan button di tablet landscape */
    .btn-back-mobile { display: none !important; }
    
    .header-container {
        padding: 10px 12px;
        margin-bottom: 15px;
    }
    .header-title { font-size: 16px; }
    .header-subtitle { font-size: 12px; }
    .header-center {
        padding: 6px 12px;
        gap: 8px;
    }
    .tahun-display-icon { font-size: 16px; }
    .tahun-display-text { font-size: 12px; }
    .tahun-display-value { 
        font-size: 14px;
        padding: 3px 10px;
        min-width: 60px;
    }
}

@media (max-width:575.98px){
    :root{
        --panelH:auto;
        --chartH:260px;   
    }
    .card-body{ padding:8px; }
    .chart-wrapper{
        height:var(--chartH);
        min-height:var(--chartH);
    }
    .pnbp-pill{
        border-radius:12px;
        padding:10px 11px;
        min-height:62px;
    }
    .pnbp-pill-icon{ width:36px; height:36px; font-size:14px; }
    .pnbp-pill-badge{ width:52px; height:52px; }
    .pnbp-pill-title{ font-size:11px; }
    .pnbp-pill-sub{ font-size:12px; }
    .pnbp-pill-badge .pct{ font-size:11px; }
    .pnbp-pill-badge .lbl{ font-size:7px; }
    .table { font-size:10px }
    .table thead th { padding:8px 4px; font-size:9px }
    .table tbody td { padding:6px 4px; font-size:9px }
    
    /* Sembunyikan button di smartphone */
    .btn-back-mobile { display: none !important; }
    
    .header-container {
        padding: 8px 10px;
        border-radius: 10px;
    }
    .header-title { 
        font-size: 14px;
        text-align: center;
    }
    .header-subtitle { 
        font-size: 11px;
        text-align: center;
    }
    .header-center {
        padding: 5px 10px;
        border-radius: 8px;
    }
    .tahun-display-icon { font-size: 14px; }
    .tahun-display-text { 
        font-size: 10px;
        white-space: nowrap;
    }
    .tahun-display-value { 
        font-size: 12px;
        padding: 2px 8px;
        min-width: 50px;
    }
}

@media (max-width:400px){
    /* Extra small - tetap sembunyikan button */
    .btn-back-mobile { display: none !important; }
    .header-title { font-size: 13px; }
    .tahun-display-text { font-size: 9px; }
    .tahun-display-value { font-size: 11px; }
}
</style>
</head>

<body id="page-top">

<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <!-- HEADER DENGAN TAHUN DI TENGAH -->
            <div class="header-container">
                <div class="header-left">
                    <h1 class="header-title">Data Realisasi PNBP</h1>
                    <p class="header-subtitle">BBKK Batam</p>
                </div>
                
                <div class="header-center">
                    <i class="fas fa-calendar-alt tahun-display-icon"></i>
                    <span class="tahun-display-text">Tahun Anggaran:</span>
                    <span class="tahun-display-value"><?= $tahun ?></span>
                </div>
                
                <div class="header-right">
                    <a href="index.php" class="btn btn-sm btn-primary btn-back-mobile">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <!-- Ringkasan PNBP -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="text-xs mb-1">Total Target</div>
                            <div class="h5 mb-0"><?= nf0($tot_target) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="text-xs mb-1">Total Akumulasi Realisasi</div>
                            <div class="h5 mb-0">
                                <?= nf0($tot_realisasi) ?> <span style="font-size:13px">(<?= pf($tot_persen * 100) ?>%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="text-xs mb-1">Total Pagu Efektif PNBP</div>
                            <div class="h5 mb-0"><?= nf0($total_pagu_efektif) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="text-xs mb-1">Maksimal Pencairan (MP) PNBP</div>
                            <div class="h5 mb-0"><?= nf0($mp_pnbp) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div class="text-xs mb-2">Rekomendasi</div>
                            <div class="mb-0" style="white-space:normal; color:#1f2937; font-weight:600; font-size:13px; line-height:1.5;">
                                <?= ($rekomendasi_text !== '') ? nl2br(htmlspecialchars($rekomendasi_text)) : '<span style="color:#9ca3af">Belum ada rekomendasi</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAFIK + PILL -->
            <div class="row align-items-stretch">
                <div class="col-lg-8 mb-3">
                    <div class="card shadow mb-0 equal-panel">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold">Grafik Realisasi PNBP Tahun <?= $tahun ?></h6>
                            <span class="small text-muted">Bar: Realisasi/Bulan • Line: Akumulasi • Garis Putus: Target</span>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrapper">
                                <canvas id="chartPnbp"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel PILL -->
                <div class="col-lg-4 mb-3">
                    <?php if(count($cards) === 0): ?>
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="text-muted">Belum ada data untuk tahun <?= $tahun ?>.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pill-panel">
                            <div class="pill-container">
                                <?php foreach($cards as $c): ?>
                                    <div class="pnbp-pill">
                                        <div class="pnbp-pill-left">
                                            <div class="pnbp-pill-icon">
                                                <i class="fas fa-coins"></i>
                                            </div>
                                            <div class="pnbp-pill-text">
                                                <div class="pnbp-pill-title" title="<?= htmlspecialchars($c['uraian']) ?>">
                                                    <?= htmlspecialchars($c['uraian']) ?>
                                                </div>
                                                <div class="pnbp-pill-sub">Rp <?= nf0($c['target']) ?></div>
                                            </div>
                                        </div>

                                        <div class="pnbp-pill-right">
                                            <div class="pnbp-pill-badge">
                                                <div class="pct"><?= pf($c['pct'] * 100) ?>%</div>
                                                <div class="lbl">Akum</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ==================== TABEL PNBP ==================== -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Tabel Data Realisasi PNBP Tahun <?= $tahun ?></h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="center" style="width:60px;">No</th>
                                <th rowspan="2" class="center">MAK</th>
                                <th rowspan="2" class="center">Uraian</th>
                                <th rowspan="2" class="center">Target</th>
                                <th colspan="2" rowspan="2" class="center">Akumulasi</th>
                                <th colspan="12" class="center">Realisasi Per Bulan</th>
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
                                <td colspan="18" class="center">Belum ada data PNBP untuk tahun <?= $tahun ?>.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no=1; foreach($rows as $d): ?>
                                <?php
                                    $akum = 0;
                                    foreach($months as $k=>$lbl) $akum += (float)$d[$k];
                                    $pct = ((float)$d['target'] > 0) ? ($akum / (float)$d['target']) : 0;
                                ?>
                                <tr>
                                    <td class="center"><?= $no++ ?></td>
                                    <td class="center" style="white-space:nowrap;"><?= htmlspecialchars($d['mak']) ?></td>
                                    <td><?= htmlspecialchars($d['uraian']) ?></td>
                                    <td class="right" style="white-space:nowrap;"><?= nf0($d['target']) ?></td>
                                    <td class="right" style="white-space:nowrap;"><?= nf0($akum) ?></td>
                                    <td class="center" style="white-space:nowrap;"><?= pf($pct * 100) ?>%</td>
                                    <?php foreach($months as $k=>$lbl): ?>
                                        <td class="right" style="white-space:nowrap;"><?= nf0($d[$k]) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="total-row">
                                <td colspan="3" class="center">Jumlah</td>
                                <td class="right"><?= nf0($tot_target) ?></td>
                                <td class="right"><?= nf0($tot_realisasi) ?></td>
                                <td class="center"><?= pf($tot_persen * 100) ?>%</td>
                                <?php foreach($months as $k=>$lbl): ?>
                                    <td class="right"><?= nf0($tot_months[$k]) ?></td>
                                <?php endforeach; ?>
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
<script src="asset/chart.js"></script>

<script>
var labels = <?= json_encode($chartLabels) ?>;
var dataMonthly = <?= json_encode($chartMonthly) ?>;
var dataCumulative = <?= json_encode($chartCumulative) ?>;
var dataCumulativePercent = <?= json_encode($chartCumulativePercent) ?>;
var dataTarget = <?= json_encode($chartTargetLine) ?>;

function formatNumberID(value){
  value = Number(value) || 0;
  return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

var COLOR_BAR_FILL   = 'rgba(0, 123, 255, 0.45)';
var COLOR_BAR_BORDER = '#007bff';
var COLOR_LINE_AKUM  = '#00f3aeff';
var COLOR_LINE_AKUM_FILL = 'rgba(9, 253, 21, 0.12)';
var COLOR_LINE_TARGET = '#f59535ff';

var isMobile = window.matchMedia("(max-width: 575.98px)").matches;

var ctx = document.getElementById('chartPnbp').getContext('2d');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
      {
        type: 'bar',
        label: 'Realisasi per Bulan',
        data: dataMonthly,
        backgroundColor: COLOR_BAR_FILL,
        borderColor: COLOR_BAR_BORDER,
        borderWidth: 1.5
      },
      {
        type: 'line',
        label: 'Akumulasi Realisasi',
        data: dataCumulative,
        fill: true,
        backgroundColor: COLOR_LINE_AKUM_FILL,
        borderColor: COLOR_LINE_AKUM,
        pointBackgroundColor: COLOR_LINE_AKUM,
        pointBorderColor: COLOR_LINE_AKUM,
        borderWidth: 2,
        pointRadius: isMobile ? 2 : 2,
        tension: 0.15
      },
      {
        type: 'line',
        label: 'Target Total',
        data: dataTarget,
        fill: false,
        borderColor: COLOR_LINE_TARGET,
        borderWidth: 2,
        borderDash: [6,6],
        pointRadius: 0,
        tension: 0
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    devicePixelRatio: Math.min(window.devicePixelRatio || 1, 2),
    layout: {
      padding: {
        left: isMobile ? 10 : 6,
        right: isMobile ? 10 : 6,
        top: 4,
        bottom: isMobile ? 6 : 2
      }
    },
    legend: {
      display: true,
      position: 'top',
      labels: {
        fontSize: isMobile ? 12 : 11,
        boxWidth: isMobile ? 12 : 10
      }
    },
    tooltips: {
      titleFontSize: isMobile ? 13 : 12,
      bodyFontSize: isMobile ? 13 : 12,
      callbacks: {
        label: function(tooltipItem, data){
          var ds = data.datasets[tooltipItem.datasetIndex];
          var val = ds.data[tooltipItem.index];
          var label = ds.label + ": " + formatNumberID(val);
          if(tooltipItem.datasetIndex === 1){
            var pct = dataCumulativePercent[tooltipItem.index];
            label += " (" + pct.toFixed(2) + "%)";
          }
          return label;
        }
      }
    },
    scales: {
      xAxes: [{
        ticks: {
          fontSize: isMobile ? 12 : 11
        }
      }],
      yAxes: [{
        ticks: {
          beginAtZero: true,
          fontSize: isMobile ? 12 : 11,
          callback: function(value){ return formatNumberID(value); }
        }
      }]
    }
  }
});
</script>

</body>
</html>