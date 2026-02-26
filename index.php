<?php
session_start();
include "koneksi.php";

function nf($n){ return number_format($n,0,',','.'); }
function pf($n){ return number_format($n,2,',','.'); }

// ===== FILTER TAHUN GLOBAL (BERBASIS SESSION) =====
if(isset($_GET['tahun'])) {
    $_SESSION['tahun_filter'] = (int)$_GET['tahun'];
}
$tahun = isset($_SESSION['tahun_filter']) ? (int)$_SESSION['tahun_filter'] : (int)date('Y');
$_SESSION['tahun_filter'] = $tahun;

// Daftar tahun dari database - gabung dari SEMUA tabel (URUTAN DESC)
$tahun_list_query = mysqli_query($koneksi, "
    SELECT DISTINCT tahun FROM realisasi_jenis_belanja 
    UNION 
    SELECT DISTINCT tahun FROM percepatan 
    UNION 
    SELECT DISTINCT tahun FROM spm_berjalan 
    UNION 
    SELECT DISTINCT tahun FROM belanja_akrual 
    UNION
    SELECT DISTINCT tahun FROM pnbp
    UNION
    SELECT DISTINCT tahun FROM pnbp_total_pagu_efektif
    UNION
    SELECT DISTINCT tahun FROM pnbp_rekomendasi
    UNION
    SELECT DISTINCT tahun FROM pk_2025_kinerja
    ORDER BY tahun DESC
");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}
if(empty($tahun_list)) {
    $tahun_list = [(int)date('Y')];
}

// CEK APAKAH TAHUN YANG DIPILIH ADA DATA
$cek_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM realisasi_jenis_belanja WHERE tahun = $tahun"));
if($cek_data['total'] == 0 && !empty($tahun_list)) {
    $tahun = $tahun_list[0];
    $_SESSION['tahun_filter'] = $tahun;
}

// ================= REALISASI =================
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

$persen_blokir_total          = ($total['total_pagu']>0)?($total['total_blokir']/$total['total_pagu']*100):0;
$persen_real_seluruh_1_total  = ($total['total_pagu']>0)?($total['total_real_1']/$total['total_pagu']*100):0;
$persen_real_seluruh_2_total  = ($total['total_pagu']>0)?($total['total_real_2']/$total['total_pagu']*100):0;
$persen_kas_basis_total       = ($total['total_pagu_efektif']>0)?($total['total_kas_basis']/$total['total_pagu_efektif']*100):0;
$persen_akrual_total          = ($total['total_pagu_efektif']>0)?($total['total_akral']/$total['total_pagu_efektif']*100):0;

// ================= SPM BERJALAN =================
$spm_res = mysqli_query($koneksi, "SELECT * FROM spm_berjalan WHERE tahun = $tahun ORDER BY id ASC");
$spm_total_row = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(jumlah_belanja) AS total_spm FROM spm_berjalan WHERE tahun = $tahun
"));
$total_spm = $spm_total_row['total_spm'] ?? 0;

// ================= PENJELASAN BELANJA AKRUAL =================
$akr_res = mysqli_query($koneksi, "SELECT * FROM belanja_akrual WHERE tahun = $tahun ORDER BY id ASC");

$akr_total_row = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT SUM(jumlah) AS total_penjelasan_akrual FROM belanja_akrual WHERE tahun = $tahun"
));
$total_penjelasan_akrual = $akr_total_row['total_penjelasan_akrual'] ?? 0;

// ================= UPAYA PERCEPATAN =================
$percep_res = mysqli_query($koneksi, "SELECT * FROM percepatan WHERE tahun = $tahun ORDER BY id ASC");
$percep_total_row = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        SUM(jumlah)    AS total_percepatan,
        SUM(realisasi) AS total_realisasi
    FROM percepatan
    WHERE tahun = $tahun
"));
$total_percepatan           = $percep_total_row['total_percepatan'] ?? 0;
$total_realisasi_percepatan = $percep_total_row['total_realisasi']  ?? 0;
$total_selisih_percepatan   = $total_percepatan - $total_realisasi_percepatan;

// ================= TARGET REALISASI (DATA TAMBAHAN) =================
$data_tambahan = mysqli_query($koneksi,"SELECT * FROM target_realisasi WHERE tahun = $tahun ORDER BY id ASC");

// ================= TARGET REALISASI NASIONAL =================
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
    "SELECT * FROM target_realisasi_nasional WHERE tw = '".$currentTw."' AND tahun = $tahun ORDER BY id DESC LIMIT 1"
));
$targetNasPersen = $targetNasRow ? (float)$targetNasRow['akrual_persen'] : 0;

// ================= PERMASALAHAN & RENCANA TINDAK LANJUT =================
$permasalahan_res = mysqli_query($koneksi, "
    SELECT * FROM permasalahan_tindak_lanjut WHERE tahun = $tahun ORDER BY id ASC
");

// ====== LABEL AKHIR BULAN DINAMIS ======
$namaBulanID = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$currMonth = (int)date('n');
$currYear  = (int)date('Y');
$lastDay   = (int)date('t');

$labelTargetAkhirBulan = $lastDay . ' ' . $namaBulanID[$currMonth] . ' ' . $currYear;
?>
<!-- KONTEN HTML DAN CSS TETAP SAMA SEPERTI YANG ANDA MILIKI -->
<!-- HANYA BAGIAN PHP DI ATAS YANG BERUBAH -->
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MinDA BBKK Batam</title>

<link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="asset/css/sb-admin-2.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Lemon Mil",sans-serif}
body{background:#f7f7f7;color:#222;scroll-behavior:smooth}

/* ===== ANIMASI GLOBAL ===== */
@keyframes slideInDown {
    from { opacity:0;transform:translateY(-20px) }
    to { opacity:1;transform:translateY(0) }
}

@keyframes fadeInUp {
    from { opacity:0;transform:translateY(20px) }
    to { opacity:1;transform:translateY(0) }
}

@keyframes fadeInLeft {
    from { opacity:0;transform:translateX(-30px) }
    to { opacity:1;transform:translateX(0) }
}

@keyframes shimmer {
    0% { background-position:-1000px 0 }
    100% { background-position:1000px 0 }
}

/* ===== NAVBAR DESKTOP ===== */
.navbar-custom{
    width:100%;
    padding:8px 32px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:linear-gradient(to bottom, rgba(0,0,0,0.9), rgba(0,0,0,0.5), transparent);
    position:fixed;
    top:0;
    left:0;
    z-index:999;
    transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.navbar-custom.scrolled{
    background:#ffffff;
    box-shadow:0 4px 12px rgba(0,0,0,0.15);
    padding:6px 32px;
}

.navbar-custom .brand{
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
    transition:transform 0.3s ease;
}

.navbar-custom .brand:hover {
    transform:scale(1.02);
}

.navbar-custom img{
    width:40px;
    transition:transform 0.3s ease;
}

.navbar-custom .brand:hover img {
    transform:rotate(5deg) scale(1.05);
}

.navbar-custom .brand span{
    font-size:20px;
    font-weight:700;
    color:#ffffff;
    transition:color 0.3s ease;
}

.navbar-custom.scrolled .brand span{
    color:#000000;
}

/* ===== NAVBAR FILTER TAHUN (MENGGANTI MENU) ===== */
.navbar-filter{
    display:flex;
    align-items:center;
    gap:10px;
}

.navbar-filter-label {
    color: #ffffff;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.navbar-custom.scrolled .navbar-filter-label {
    color: #333333;
}

.navbar-filter select {
    padding: 8px 12px;
    border: 2px solid rgba(255,255,255,0.6);
    border-radius: 6px;
    background: rgba(255,255,255,0.15);
    color: white;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    min-width: 140px;
}

.navbar-filter select:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.9);
    box-shadow: 0 4px 12px rgba(13, 187, 203, 0.3);
}

.navbar-filter select:focus {
    outline: none;
    background: rgba(255,255,255,0.3);
    border-color: #ffd23d;
    box-shadow: 0 0 8px rgba(255, 210, 61, 0.4);
}

.navbar-filter select option {
    background: white;
    color: #333;
    font-weight: 500;
}

.navbar-custom.scrolled .navbar-filter select {
    border-color: #0DBBCB;
    background: rgba(13, 187, 203, 0.12);
    color: #333;
}

.navbar-custom.scrolled .navbar-filter select:hover {
    background: rgba(13, 187, 203, 0.25);
    border-color: #0DBBCB;
}

.navbar-filter-icon {
    color: #ffffff;
    font-size: 16px;
    transition: color 0.3s ease;
}

.navbar-custom.scrolled .navbar-filter-icon {
    color: #0DBBCB;
}

.navbar-custom nav{
    display:flex;
    align-items:center;
}

.navbar-custom nav a{
    margin-left:25px;
    text-decoration:none;
    color:#ffffff;
    font-weight:500;
    font-size:14px;
    transition:all 0.3s ease;
    position:relative;
}

.navbar-custom nav a::after {
    content:'';
    position:absolute;
    bottom:-3px;
    left:0;
    width:0;
    height:2px;
    background:#ffd23d;
    transition:width 0.3s ease;
}

.navbar-custom nav a:hover::after {
    width:100%;
}

.navbar-custom nav a:hover{
    color:#ffd23d;
}

.navbar-custom.scrolled nav a{
    color:#333333;
}

.navbar-custom.scrolled nav a::after {
    background:#007bff;
}

.navbar-custom.scrolled nav a:hover{
    color:#007bff;
}

/* ===== DRAWER TRIGGER BUTTON ===== */
.drawer-trigger {
    position: fixed;
    left: 0;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1abc9c 0%, #17bcc4 100%);
    border: none;
    border-radius: 0 10px 10px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    box-shadow: 2px 2px 12px rgba(26, 188, 156, 0.35);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.drawer-trigger:hover {
    width: 60px;
    background: linear-gradient(135deg, #17bcc4 0%, #1abc9c 100%);
    box-shadow: 4px 4px 16px rgba(26, 188, 156, 0.45);
    transform: translateX(3px);
}

.drawer-trigger i {
    color: white;
    font-size: 20px;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.drawer-trigger:hover i {
    transform: scale(1.15);
}

/* ===== NAVBAR DRAWER ===== */
.navbar-drawer {
    position: fixed;
    left: -350px;
    top: 0;
    width: 350px;
    height: 100vh;
    background: linear-gradient(135deg, #0DBBCB 0%, #1abc9c 80%, #0d7a6f 100%);
    box-shadow: 4px 0 16px rgba(0,0,0,0.25);
    z-index: 999;
    transition: left 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow-y: auto;
    padding-top: 30px;
}

.navbar-drawer.active {
    left: 0;
}

.drawer-header {
    padding: 25px;
    text-align: center;
    border-bottom: 2px solid rgba(255,255,255,0.2);
    margin-bottom: 25px;
    animation: slideInDown 0.6s ease-out;
}

.drawer-header h3 {
    color: white;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.drawer-header p {
    color: rgba(255,255,255,0.85);
    font-size: 12px;
    letter-spacing:0.5px;
}

.drawer-menu {
    padding: 0 12px;
}

.drawer-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: white;
    padding: 16px;
    margin-bottom: 12px;
    border-radius: 12px;
    background: rgba(255,255,255,0.1);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-align: center;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}

.drawer-item:nth-child(1) { animation-delay: 0.1s; }
.drawer-item:nth-child(2) { animation-delay: 0.15s; }
.drawer-item:nth-child(3) { animation-delay: 0.2s; }
.drawer-item:nth-child(4) { animation-delay: 0.25s; }
.drawer-item:nth-child(5) { animation-delay: 0.3s; }
.drawer-item:nth-child(6) { animation-delay: 0.35s; }
.drawer-item:nth-child(7) { animation-delay: 0.4s; }

.drawer-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    transition: left 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 1;
}

.drawer-item:hover::before {
    left: 100%;
}

.drawer-item:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 24px rgba(26, 188, 156, 0.35);
    background: rgba(255,255,255,0.18);
    border-color: rgba(255,255,255,0.3);
}

.drawer-item:active {
    transform: translateY(0) scale(0.97);
}

.drawer-item-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 22px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    z-index: 2;
}

.drawer-item:hover .drawer-item-icon {
    transform: scale(1.2) rotate(8deg);
    background: rgba(255,255,255,0.3);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.drawer-item-text {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.3;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.drawer-item:hover .drawer-item-text {
    letter-spacing: 0.5px;
}

a.drawer-item { color: white; }
a.drawer-item:hover { color: white !important; }
a.drawer-item:visited { color: white !important; }

/* ===== DRAWER OVERLAY ===== */
.drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0);
    z-index: 998;
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
}

.drawer-overlay.active {
    background: rgba(0,0,0,0.45);
    opacity: 1;
    pointer-events: all;
}

/* ===== HERO ===== */
.hero{
    margin-top:0;
    height:520px;
    position:relative;
    overflow:hidden;
}

.hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(to right,rgba(0,0,0,0.8),rgba(0,0,0,0.4),rgba(0,0,0,0));
    z-index:1;
}

.hero-bg{
    width:100%;
    height:100%;
    object-fit:cover;
    position:absolute;
    left:0;
    top:0;
    animation: zoomIn 1.2s ease-out;
}

@keyframes zoomIn {
    from { transform:scale(1.05); opacity:0.8; }
    to { transform:scale(1); opacity:1; }
}

.hero-content{
    position:absolute;
    left:40px;
    bottom:40px;
    color:white;
    z-index:2;
    animation: fadeInLeft 0.8s ease-out 0.2s both;
    display:flex;
    flex-direction:column;
    align-items:flex-start;
}

.hero-content .kem-logo{
    width:180px;
    margin-bottom:20px;
    animation: fadeInUp 0.8s ease-out 0.3s both;
    display:block;
    margin-top:0;
}

.hero-content h1{
    font-size:32px;
    font-weight:700;
    margin-bottom:0;
    margin-top:0;
    padding:0;
}

.minda-title {
    font-family: "Segoe UI", "Montserrat", Arial, sans-serif;
    font-weight: 800;
    font-size: 46px;
    line-height: 1.1;
    color: #ffffff;
    letter-spacing: 1.2px;
    margin-bottom: 0;
    margin-top: 0;
    padding: 0;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.55), 0 0 1px rgba(0, 0, 0, 0.6);
}

.minda-sub {
    font-family: "Segoe UI", "Montserrat", Arial, sans-serif;
    font-weight: 300;
    font-size: 26px;
    font-style: italic;
    color: #ffffff;
    letter-spacing: 0.5px;
    opacity: 0.95;
    margin-top: 0;
    margin-bottom: 20px;
    padding: 0;
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.45), 0 0 1px rgba(0, 0, 0, 0.6);
}

/* ===== BUTTON ===== */
.btn-text {
    text-decoration: none;
    color: white;
    font-weight: 600;
    background: #0DBBCB;
    padding: 12px 24px;
    border-radius: 8px;
    display: inline-block;
    font-size: 16px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(13, 187, 203, 0.3);
}

.btn-text::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.25);
    transform: translateY(-50%);
    transition: left 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

a:hover{
    text-decoration: none;
}

.btn-text:hover::before {
    left: 100%;
}

.btn-text:hover {
    background: #1aa0af;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(13, 187, 203, 0.4);
    color: #ffffff;
}

.btn-text:active {
    transform: translateY(-1px);
}

/* ===== CARD ===== */
.card-plain{
    background:white;
    border-radius:12px;
    padding:16px;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    margin-top:20px;
    animation: fadeInUp 0.6s ease-out;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.card-plain:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

/* ===== CHART - IMPROVED UNTUK PDF ===== */
.chart-wrapper{
    width:100%;
    height:350px !important;
    max-height:350px !important;
    position:relative;
}
#chartKas{
    width:100% !important;
    height:100% !important;
    max-height:350px !important;
    display:block;
}

/* ===== TABLE - STRUKTUR DARI KODE KEDUA (JELAS DI PDF) ===== */
table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:8px;
    overflow:hidden;
    margin-top:12px;
    font-size:13px;
}
table th{
    background:linear-gradient(135deg, #0DBBCB 0%, #00A3B5 100%);
    color:white;
    text-align:center;
    padding:10px 6px;
    border:1px solid #cae2e4;
    font-weight:600;
    font-size:12px;
}
table td{
    padding:8px 6px;
    border:1px solid #e0e0e0;
    font-size:12px;
}
table tbody tr:hover td {
    background:#f9f9f9;
}
.center{text-align:center}
.right{text-align:right}
.total-row{background:#e3f0ff;font-weight:700;}
.target-nasional-row {
    background-color: #ffd54c !important;
}
.target-nasional-row td {
    color: #000 !important;
    font-weight: bold !important;
}

/* ===== RESPONSIF: DESKTOP BESAR ===== */
@media (max-width: 1399px) {
    .navbar-custom { padding:7px 28px; }
    .navbar-custom.scrolled { padding:5px 28px; }
    .navbar-custom .brand span { font-size:18px; }
    .navbar-custom nav a { margin-left:20px;font-size:13px; }

    .hero { height:480px; }
    .hero-content { left:30px;bottom:30px; }
    .hero-content .kem-logo { width:160px;margin-bottom:20px; }
    .minda-title { font-size:px; }
    .minda-sub { font-size:24px; }

    .card-plain { padding:14px; }
    table { font-size:13px; }
    table th,table td { padding:9px; }
    .chart-wrapper { height:320px !important; }

    .drawer-trigger { top:70px; }
    .navbar-drawer { width:320px;left:-320px; }
}

/* ===== RESPONSIF: TABLET ===== */
@media (max-width: 1199px) {
    .navbar-custom { padding:6px 20px; }
    .navbar-custom.scrolled { padding:4px 20px; }
    .navbar-custom img { width:36px; }
    .navbar-custom .brand span { font-size:17px; }
    .navbar-custom nav a { margin-left:16px;font-size:12px; }

    .hero { height:420px; }
    .hero-content { left:25px;bottom:25px; }
    .hero-content .kem-logo { width:145px;margin-bottom:20px; }
    .minda-title { font-size:34px;letter-spacing:0.7px; }
    .minda-sub { font-size:21px; }

    .card-plain { padding:12px;margin-top:15px; }
    table { font-size:12px; }
    table th,table td { padding:8px; }
    .chart-wrapper { height:300px !important; }

    .drawer-item { padding:14px; }
    .drawer-item-icon { width:44px;height:44px;font-size:19px; }
    .drawer-item-text { font-size:13px; }
}

/* ===== RESPONSIF: TABLET PORTRAIT / IPAD ===== */
@media (max-width: 991.98px) {
    .navbar-custom { padding:5px 15px; }
    .navbar-custom.scrolled { padding:3px 15px; }
    .navbar-custom img { width:33px; }
    .navbar-custom .brand span { font-size:16px; }
    .navbar-custom nav a { margin-left:14px;font-size:11px; }

    .hero { height:380px; }
    .hero-content { left:20px;bottom:20px; }
    .hero-content .kem-logo { width:130px;margin-bottom:12px;display:block; }
    .minda-title { font-size:20px;letter-spacing:0.5px;margin-bottom:8px; }
    .minda-sub { font-size:19px; }
    .btn-text { padding:10px 18px;font-size:13px; }

    .card-plain { padding:11px;margin-top:12px; }
    .container { padding-left:10px;padding-right:10px; }
    table { font-size:11px; }
    table th,table td { padding:7px; }
    .chart-wrapper { height:280px !important; }

    .drawer-trigger { width:46px;height:46px;top:60px; }
    .drawer-trigger:hover { width:54px; }
    .navbar-drawer { width:280px;left:-280px; }
    .drawer-item { padding:13px;margin-bottom:10px; }
    .drawer-item-icon { width:42px;height:42px;font-size:18px;margin-bottom:8px; }
    .drawer-item-text { font-size:12px; }
}

/* ===== RESPONSIF: LANDSCAPE / SMARTPHONE BESAR ===== */
@media (max-width: 767.98px) {
    body { background:#ffffff; }

    .navbar-custom { 
        padding: 8px 12px;
        gap: 8px;
        flex-wrap: nowrap;
    }
    .navbar-custom.scrolled { 
        padding: 6px 12px;
    }
    .navbar-custom .brand { 
        gap: 8px;
        display: flex;
        align-items: center;
        flex: 0 1 auto;
    }
    .navbar-custom img { 
        width: 36px;
        height: 36px;
        flex-shrink: 0;
    }
    .navbar-custom .brand span { 
        font-size: 15px;
        font-weight: 700;
        white-space: nowrap;
        color: #ffffff;
        flex-shrink: 0;
    }
    .navbar-custom.scrolled .brand span {
        color: #000000;
    }
    .navbar-custom nav { display:none; }
    .navbar-toggle { display:none; }

    .hero { 
        height: 320px;
    }
    .hero-content {
        left: 15px;
        bottom: 15px;
        gap: 8px;
    }
    .hero-content .kem-logo {
        width: 120px;
        height: auto;
        margin-bottom: 8px;
        display: block;
    }
    .minda-title { 
        font-size: 20px;
        margin-bottom: 6px;
        line-height: 1.3;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .minda-sub { 
        font-size: 14px;
        margin-top: 0;
        font-weight: 400;
        line-height: 1.3;
    }
    .btn-text { 
        padding: 8px 16px;
        font-size: 12px;
        margin-top: 6px;
    }

    .card-plain { padding:10px;margin-top:12px;border-radius:10px; }
    .container { padding-left:10px;padding-right:10px; }
    table { font-size:10px;margin-top:12px; }
    table th,table td { padding:5px;white-space:nowrap; }
    .chart-wrapper { height:240px !important; }

    .drawer-trigger { width:44px;height:44px;top:55px; }
    .drawer-trigger:hover { width:52px; }
    .navbar-drawer { width:270px;left:-270px; }
    .drawer-item { padding:12px;margin-bottom:10px; }
    .drawer-item-icon { width:38px;height:38px;font-size:17px;margin-bottom:8px; }
    .drawer-item-text { font-size:11px; }
}

/* ===== RESPONSIF: SMARTPHONE KECIL ===== */
@media (max-width: 575.98px) {
    body { background:#ffffff; }

    .navbar-custom { 
        padding: 8px 10px;
        gap: 6px;
        flex-wrap: nowrap;
    }
    .navbar-custom.scrolled { 
        padding: 6px 10px;
    }
    .navbar-custom .brand {
        gap: 6px;
        display: flex;
        align-items: center;
        flex: 0 1 auto;
    }
    .navbar-custom img { 
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }
    .navbar-custom .brand span { 
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        color: #ffffff;
        flex-shrink: 0;
    }
    .navbar-custom.scrolled .brand span {
        color: #000000;
    }

    .hero { 
        height: 300px;
        margin-top: 0;
    }
    .hero-content {
        left: 12px;
        bottom: 12px;
        gap: 6px;
    }
    .hero-content .kem-logo {
        width: 110px;
        height: auto;
        margin-bottom: 6px;
        display: block;
    }
    .minda-title {
        font-size: 15px;
        margin-bottom: 4px;
        line-height: 1.3;
        letter-spacing: 0.3px;
        font-weight: 700;
    }
    .minda-sub {
        font-size: 13px;
        margin-top: 0;
        font-weight: 400;
        line-height: 1.2;
        margin-bottom: 8px;
    }
    .btn-text {
        padding: 6px 12px;
        font-size: 11px;
        margin-top: 4px;
    }

    .card-plain { padding:9px;margin-top:10px; }
    .container { padding-left:8px;padding-right:8px; }
    table { font-size:9px; }
    table th,table td { padding:4px;font-size:8px; }
    .chart-wrapper { height:220px !important; }

    .drawer-trigger { width:42px;height:42px;top:50px;font-size:18px; }
    .drawer-trigger:hover { width:50px; }
    .drawer-trigger i { font-size:18px; }
    .navbar-drawer { width:260px;left:-260px;padding-top:20px; }
    .drawer-header { padding:18px;margin-bottom:15px; }
    .drawer-header h3 { font-size:16px; }
    .drawer-menu { padding:0 10px; }
    .drawer-item { padding:11px;margin-bottom:10px; }
    .drawer-item-icon { width:36px;height:36px;font-size:16px;margin-bottom:6px; }
    .drawer-item-text { font-size:11px; }
}

/* ===== RESPONSIF: EKSTRA KECIL ===== */
@media (max-width: 400px) {
    .navbar-custom { 
        padding: 6px 8px;
        gap: 4px;
        flex-wrap: nowrap;
    }
    .navbar-custom .brand { 
        gap: 5px;
        display: flex;
        align-items: center;
        flex: 0 1 auto;
    }
    .navbar-custom img { 
        width: 28px;
        height: 28px;
        flex-shrink: 0;
    }
    .navbar-custom .brand span { 
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        color: #ffffff;
        flex-shrink: 0;
    }
    .navbar-custom.scrolled .brand span {
        color: #000000;
    }

    .hero { 
        height: 280px;
    }
    .hero-content {
        left: 10px;
        bottom: 10px;
        gap: 4px;
    }
    .hero-content .kem-logo {
        width: 100px;
        height: auto;
        margin-bottom: 4px;
        display: block;
    }
    .minda-title {
        font-size: 10px;
        margin-bottom: 2px;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: 0px;
    }
    .minda-sub {
        font-size: 12px;
        margin-top: 0;
        font-weight: 400;
        line-height: 1.2;
        margin-bottom: 6px;
    }
    .btn-text {
        padding: 6px 10px;
        font-size: 10px;
        margin-top: 2px;
    }

    .drawer-trigger { width:40px;height:40px;font-size:16px; }
    .drawer-trigger:hover { width:48px; }
    .navbar-drawer { width:240px;left:-240px; }
}
</style>
</head>

<body>

<!-- Navbar dengan Filter Tahun Menggantikan Menu -->
<div class="navbar-custom" id="navbar">
    <div class="brand">
        <img src="img/karantina.png" alt="Logo">
        <span>BBKK BATAM</span>
    </div>
    
    <!-- Filter Tahun Global -->
    <div class="navbar-filter">
        <label class="navbar-filter-label" for="tahunFilter">
            <i class="fas fa-calendar-alt navbar-filter-icon"></i> T.A.
        </label>
        <select id="tahunFilter" onchange="filterTahun(this.value)">
            <?php foreach($tahun_list as $t): ?>
                <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>>
                    <?= $t ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- DRAWER TRIGGER BUTTON -->
<button class="drawer-trigger" id="drawerTrigger" title="Buka menu">
    <i class="fas fa-bars"></i>
</button>

<!-- NAVBAR DRAWER -->
<div class="navbar-drawer" id="navbarDrawer">
    <div class="drawer-header">
        <h3>MinDA BBKK</h3>
        <p>Laman Informasi Data Anggaran</p>
    </div>

    <div class="drawer-menu">
        <a href="ikpa.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-award"></i></div>
            <div class="drawer-item-text">Nilai IKPA</div>
        </a>
        <a href="capaian_output.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-chart-line"></i></div>
            <div class="drawer-item-text">Capaian Output</div>
        </a>
        <a href="dokumen_anggaran.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-file-alt"></i></div>
            <div class="drawer-item-text">Dokumen Anggaran</div>
        </a>
        <a href="pnbp.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-coins"></i></div>
            <div class="drawer-item-text">PNBP</div>
        </a>
        <a href="pk.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-handshake"></i></div>
            <div class="drawer-item-text">Perjanjian Kinerja</div>
        </a>
        <a href="performa.php" class="drawer-item">
            <div class="drawer-item-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="drawer-item-text">Performance</div>
        </a>
        <a href="https://drive.google.com/drive/folders/1cInag_nPaRuiYa1FQLw0_Vryl03aPMFI" class="drawer-item" target="_blank" rel="noopener noreferrer">
            <div class="drawer-item-icon">
                <i class="fas fa-file-signature"></i>
            </div>
            <div class="drawer-item-text">Notulensi Rapat Anggaran</div>
        </a>
    </div>
</div>

<!-- OVERLAY -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- HERO -->
<section class="hero" id="hero">
    <img class="hero-bg" src="img/g2.png" alt="Hero Background">
    <div class="hero-content">
        <img class="kem-logo" src="img/kemenkes.png" alt="Kemenkes Logo">
        <h1 class="minda-title">MinDA BBKK Batam<br>
            <span class="minda-sub">"Laman Informasi Data Anggaran"</span>
        </h1><br>
        <a href="login.php" class="btn-text">Lihat Detail →</a>
    </div>
</section>

<div class="container">
    <!-- Grafik -->
    <div class="card-plain" id="grafik">
        <h4 style="color:#0DBBCB;margin-bottom:12px">Grafik Realisasi</h4>
        <div class="chart-wrapper">
            <canvas id="chartKas"></canvas>
        </div>
    </div>

    <!-- Tabel Realisasi -->
    <div class="card-plain" id="tabel">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 style="color:#0DBBCB;margin-bottom:0">Realisasi Berdasarkan Jenis Belanja</h4>
            <button class="btn btn-sm btn-danger" onclick="downloadTablePDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <div style="overflow:auto" id="tabel-pdf">
        <table>
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">Uraian Belanja</th>
                    <th rowspan="3">Jml. Pagu</th>
                    <th colspan="2" rowspan="2">Blokir</th>
                    <th rowspan="3">Pagu Efektif</th>
                    <th colspan="4">Realisasi (Seluruh Pagu)</th>
                    <th colspan="4">Realisasi (Pagu Efektif)</th>
                    <th colspan="2">Sisa Anggaran (Seluruh Pagu)</th>
                    <th colspan="2">Sisa Anggaran (Pagu Efektif)</th>
                </tr>
                <tr>
                    <th colspan="2">Kas</th>
                    <th colspan="2">Akrual</th>
                    <th colspan="2">Kas</th>
                    <th colspan="2">Akrual</th>
                    <th colspan="2">Kas</th>
                    <th colspan="2">Akrual</th>
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
    <td><?= htmlspecialchars($d['uraian_belanja']) ?></td>
    <td class="right"><?= nf($d['jml_pagu']) ?></td>
    <td class="right"><?= nf($d['jml_blokir']) ?></td>
    <td class="center"><?= pf($d['persen_blokir']) ?></td>
    <td class="right"><?= nf($d['jml_pagu_efektif']) ?></td>
    <td class="right"><?= nf($d['realisasi_seluruh_1']) ?></td>
    <td class="center"><?= pf($d['persen_realisasi_seluruh_1']) ?></td>
    <td class="right"><?= nf($d['realisasi_seluruh_2']) ?></td>
    <td class="center"><?= pf($d['persen_realisasi_seluruh_2']) ?></td>
    <td class="right"><?= nf($d['kas_basis']) ?></td>
    <td class="center"><?= pf($d['persen_kas_basis']) ?></td>
    <td class="right"><?= nf($d['akral']) ?></td>
    <td class="center"><?= pf($d['persen_akrual']) ?></td>
    <td class="right"><?= nf($d['sisa_seluruh_kas']) ?></td>
    <td class="right"><?= nf($d['sisa_seluruh_akrual']) ?></td>
    <td class="right"><?= nf($d['sisa_efektif_kas']) ?></td>
    <td class="right"><?= nf($d['sisa_efektif_akrual']) ?></td>
</tr>
<?php } ?>
<tr class="total-row">
    <td colspan="2" class="center">Jumlah</td>
    <td class="right"><?= nf($total['total_pagu']) ?></td>
    <td class="right"><?= nf($total['total_blokir']) ?></td>
    <td class="center"><?= pf($persen_blokir_total) ?></td>
    <td class="right"><?= nf($total['total_pagu_efektif']) ?></td>
    <td class="right"><?= nf($total['total_real_1']) ?></td>
    <td class="center"><?= pf($persen_real_seluruh_1_total) ?></td>
    <td class="right"><?= nf($total['total_real_2']) ?></td>
    <td class="center"><?= pf($persen_real_seluruh_2_total) ?></td>
    <td class="right"><?= nf($total['total_kas_basis']) ?></td>
    <td class="center"><?= pf($persen_kas_basis_total) ?></td>
    <td class="right"><?= nf($total['total_akral']) ?></td>
    <td class="center"><?= pf($persen_akrual_total) ?></td>
    <td class="right"><?= nf($total['total_sisa_seluruh_kas']) ?></td>
    <td class="right"><?= nf($total['total_sisa_seluruh_akrual']) ?></td>
    <td class="right"><?= nf($total['total_sisa_efektif_kas']) ?></td>
    <td class="right"><?= nf($total['total_sisa_efektif_akrual']) ?></td>
</tr>
            </tbody>
        </table>
        </div>
    </div>

    <!-- PENJELASAN BELANJA AKRUAL -->
    <div class="card-plain">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 style="color:#0DBBCB;margin-bottom:0">Penjelasan Belanja Akrual</h4>
            <button class="btn btn-sm btn-danger" onclick="downloadAkrualPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <div style="overflow:auto" id="tabel-akrual-pdf">
            <table>
                <thead><tr>
                    <th class="center" style="width:50px">No</th>
                    <th>Uraian</th>
                    <th class="center" style="width:120px">Jumlah</th>
                    <th>Keterangan</th>
                </tr></thead>
                <tbody>
                <?php
                $noA=1;
                while($ak=mysqli_fetch_assoc($akr_res)){
                ?>
                    <tr>
                        <td class="center"><?= $noA++ ?></td>
                        <td><?= htmlspecialchars($ak['uraian_belanja']) ?></td>
                        <td class="right"><?= number_format($ak['jumlah'],2,',','.') ?></td>
                        <td><?= nl2br(htmlspecialchars($ak['keterangan'])) ?></td>
                    </tr>
                <?php } ?>
                    <tr class="total-row">
                        <td colspan="2" class="center">Jumlah</td>
                        <td class="right"><?= number_format($total_penjelasan_akrual,2,',','.') ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SPM BERJALAN -->
    <div class="card-plain">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 style="color:#0DBBCB;margin-bottom:0">SPM Berjalan</h4>
            <button class="btn btn-sm btn-danger" onclick="downloadSPMPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <div style="overflow:auto" id="tabel-spm-pdf">
            <table>
                <thead><tr>
                    <th class="center" style="width:50px">No</th>
                    <th>Uraian Belanja</th>
                    <th class="center" style="width:140px">Jumlah</th>
                </tr></thead>
                <tbody>
                    <?php
                    $noSPM = 1;
                    mysqli_data_seek($spm_res, 0);
                    while($row = mysqli_fetch_assoc($spm_res)){
                    ?>
                    <tr>
                        <td class="center"><?= $noSPM++ ?></td>
                        <td><?= htmlspecialchars($row['uraian_belanja']) ?></td>
                        <td class="right"><?= number_format($row['jumlah_belanja'],2,',','.') ?></td>
                    </tr>
                    <?php } ?>
                    <tr class="total-row">
                        <td colspan="2" class="center">Jumlah</td>
                        <td class="right"><?= number_format($total_spm,2,',','.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- UPAYA PERCEPATAN -->
    <div class="card-plain">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 style="color:#0DBBCB;margin-bottom:0">RPK/RPD Bulan Berjalan</h4>
            <button class="btn btn-sm btn-danger" onclick="downloadPercepatanPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <div style="overflow:auto" id="tabel-percepatan-pdf">
            <table>
                <thead><tr>
                    <th class="center" style="width:50px">No</th>
                    <th>Uraian</th>
                    <th class="center">Jumlah</th>
                    <th class="center">Status</th>
                    <th class="center">Selisih</th>
                    <th class="center">Realisasi</th>
                    <th>Keterangan</th>
                </tr></thead>
                <tbody>
                <?php
                $noPer = 1;
                mysqli_data_seek($percep_res, 0);
                $total_realisasi = 0;
                $total_selisih = 0;
                while ($d = mysqli_fetch_assoc($percep_res)) {
                    $jumlah = (float)$d['jumlah'];
                    $realisasi = isset($d['realisasi']) ? (float)$d['realisasi'] : 0;
                    $selisih = $jumlah - $realisasi;
                    $total_realisasi += $realisasi;
                    $total_selisih += $selisih;
                    $status = $d['status'] ?? 'belum';
                ?>
                    <tr>
                        <td class="center"><?= $noPer++ ?></td>
                        <td><?= htmlspecialchars($d['uraian_belanja']) ?></td>
                        <td class="right"><?= number_format($jumlah, 2, ',', '.') ?></td>
                        <td class="center">
                            <?php
                            if ($status == 'belum') {
                                echo '<span class="badge badge-danger">Belum Terlaksana</span>';
                            } elseif ($status == 'proses') {
                                echo '<span class="badge badge-warning">Sedang diProses</span>';
                            } elseif ($status == 'selesai') {
                                echo '<span class="badge badge-success">Sudah terlaksana</span>';
                            }
                            ?>
                        </td>
                        <td class="right"><?= number_format($selisih, 2, ',', '.') ?></td>
                        <td class="right"><?= number_format($realisasi, 2, ',', '.') ?></td>
                        <td><?= nl2br(htmlspecialchars($d['keterangan'] ?? '')) ?></td>
                    </tr>
                <?php } ?>
                <tr class="total-row">
                    <td colspan="2" class="center">Jumlah</td>
                    <td class="right"><?= number_format($total_percepatan, 2, ',', '.') ?></td>
                    <td></td>
                    <td class="right"><?= number_format($total_selisih, 2, ',', '.') ?></td>
                    <td class="right"><?= number_format($total_realisasi, 2, ',', '.') ?></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TARGET REALISASI -->
    <div class="card-plain">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 style="color:#0DBBCB;margin-bottom:0">Target Realisasi (<?= htmlspecialchars($labelTargetAkhirBulan) ?>)</h4>
            <button class="btn btn-sm btn-danger" onclick="downloadTargetPDF()">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <div style="overflow:auto" id="tabel-target-pdf">
            <table>
                <thead><tr>
                    <th class="center">No</th>
                    <th>Uraian</th>
                    <th class="center">Kas Basis</th>
                    <th class="center">Akrual</th>
                </tr></thead>
                <tbody>
                    <tr>
                        <td class="center">1</td>
                        <td>Realisasi sampai hari ini</td>
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
                        <td class="right"><?= nf($total_selisih_percepatan) ?></td>
                        <td class="right"><?= nf($total_selisih_percepatan) ?></td>
                    </tr>
<?php
$noTambahan = 4;
$jumlah_kas = $total['total_kas_basis'] + $total_spm + $total_selisih_percepatan;
$jumlah_akrual = $total['total_akral'] + $total_spm + $total_selisih_percepatan - $total_penjelasan_akrual;

while($dt = mysqli_fetch_assoc($data_tambahan)){
    $jumlah_kas += $dt['kas_basis'];
    $jumlah_akrual += $dt['akrual_basis'];
?>
                    <tr>
                        <td class="center"><?= $noTambahan++ ?></td>
                        <td><?= htmlspecialchars($dt['uraian']) ?></td>
                        <td class="right"><?= nf($dt['kas_basis']) ?></td>
                        <td class="right"><?= nf($dt['akrual_basis']) ?></td>
                    </tr>
<?php } ?>
                    <tr class="total-row">
                        <td colspan="2" class="center"><strong>Jumlah</strong></td>
                        <td class="right"><?= nf($jumlah_kas) ?></td>
                        <td class="right"><?= nf($jumlah_akrual) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" class="center"><strong>Persentase realisasi berdasarkan pagu efektif</strong></td>
                        <td class="center"><?= pf(($total['total_pagu_efektif']>0)?($jumlah_kas/$total['total_pagu_efektif']*100):0) ?>%</td>
                        <td class="center"><?= pf(($total['total_pagu_efektif']>0)?($jumlah_akrual/$total['total_pagu_efektif']*100):0) ?>%</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" class="center"><strong>Persentase realisasi berdasarkan seluruh pagu</strong></td>
                        <td class="center"><?= pf(($total['total_pagu']>0)?($jumlah_kas/$total['total_pagu']*100):0) ?>%</td>
                        <td class="center"><?= pf(($total['total_pagu']>0)?($jumlah_akrual/$total['total_pagu']*100):0) ?>%</td>
                    </tr>
                    <tr class="target-nasional-row">
                        <td colspan="2" class="center"><strong>Target Realisasi Nasional</strong></td>
                        <td class="right">TW <?= $currentTw ?></td>
                        <td class="right"><?= pf($targetNasPersen) ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PERMASALAHAN -->
    <div class="card-plain">
        <h4 style="color:#0DBBCB;margin-bottom:12px">Permasalahan & Tindak Lanjut</h4>
        <div style="overflow:auto">
            <table>
                <thead><tr>
                    <th class="center" style="width:50px">No</th>
                    <th>Permasalahan</th>
                    <th>Tindak Lanjut</th>
                </tr></thead>
                <tbody>
                    <?php
                    $noPerm = 1;
                    if (mysqli_num_rows($permasalahan_res) > 0):
                        mysqli_data_seek($permasalahan_res, 0);
                        while($pm = mysqli_fetch_assoc($permasalahan_res)):
                    ?>
                        <tr>
                            <td class="center"><?= $noPerm++ ?></td>
                            <td><?= nl2br(htmlspecialchars($pm['permasalahan'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($pm['tindak_lanjut'])) ?></td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr><td colspan="3" class="center">Belum ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="asset/vendor/jquery/jquery.min.js"></script>
<script src="asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="asset/pdf.js"></script>
<script src="asset/html2canvas.js"></script>
<script src="asset/chart.js"></script>

<script>
const drawerTrigger = document.getElementById('drawerTrigger');
const navbarDrawer = document.getElementById('navbarDrawer');
const drawerOverlay = document.getElementById('drawerOverlay');
const grafikSection = document.getElementById('grafik');

let grafikTopPosition = 0;

function initDrawerPosition() {
    if (grafikSection) {
        grafikTopPosition = grafikSection.offsetTop;
        drawerTrigger.style.top = grafikTopPosition + 'px';
    }
}

// Update posisi saat load dan resize
window.addEventListener('load', initDrawerPosition);
window.addEventListener('resize', initDrawerPosition);

let lastScrollTop = 0;

// Handle scroll
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    
    drawerTrigger.style.transition = 'top 0.3s ease-out';
    
    if (scrollTop > lastScrollTop) {
        // Scrolling ke bawah - naik perlahan ke atas di bawah navbar
        drawerTrigger.style.top = '80px';
    } else {
        // Scrolling ke atas - turun perlahan ke posisi grafik
        drawerTrigger.style.top = grafikTopPosition + 'px';
    }
    
    lastScrollTop = scrollTop;
});

// Event listeners
drawerTrigger.addEventListener('mouseenter', () => {
    navbarDrawer.classList.add('active');
    drawerOverlay.classList.add('active');
});

const closeDrawer = () => {
    navbarDrawer.classList.remove('active');
    drawerOverlay.classList.remove('active');
};

drawerTrigger.addEventListener('mouseleave', () => {
    setTimeout(() => {
        if (!navbarDrawer.matches(':hover')) {
            closeDrawer();
        }
    }, 100);
});

navbarDrawer.addEventListener('mouseleave', closeDrawer);
drawerOverlay.addEventListener('click', closeDrawer);

document.querySelectorAll('.drawer-item').forEach(item => {
    item.addEventListener('click', closeDrawer);
});

drawerTrigger.addEventListener('click', () => {
    navbarDrawer.classList.toggle('active');
    drawerOverlay.classList.toggle('active');
});
// NAVBAR SCROLL + MOBILE MENU
document.addEventListener('DOMContentLoaded', function () {
    var navbar = document.querySelector('.navbar-custom');
    var toggle = document.getElementById('navbarToggle');
    var overlay = document.getElementById('mobileMenuOverlay');
    var closeBtn = document.getElementById('mobileClose');
    var mobileLinks = document.querySelectorAll('.mobile-link');

    function handleScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    handleScroll();
    window.addEventListener('scroll', handleScroll);

    function openMobileMenu() {
        if (!overlay) return;
        overlay.classList.add('show');
        if (toggle) toggle.classList.add('open');
    }
    function closeMobileMenu() {
        if (!overlay) return;
        overlay.classList.remove('show');
        if (toggle) toggle.classList.remove('open');
    }

    if (toggle && overlay) {
        toggle.addEventListener('click', function () {
            if (window.innerWidth <= 575.98) {
                openMobileMenu();
            }
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileMenu);
    }
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeMobileMenu();
            }
        });
    }
    mobileLinks.forEach(function (lnk) {
        lnk.addEventListener('click', closeMobileMenu);
    });
});

// FILTER TAHUN
function filterTahun(tahun) {
    window.location.href = '?tahun=' + tahun;
}

function formatNumberID(value) {
    if (value == null) return '';
    value = Number(value) || 0;
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ===================== DATA DARI PHP =====================
var labels = [];
var dataKas = [];
var dataPaguEfektif = [];

<?php
$c = mysqli_query($koneksi,"SELECT uraian_belanja, kas_basis, jml_pagu_efektif FROM realisasi_jenis_belanja WHERE tahun = $tahun ORDER BY id ASC");
while($r = mysqli_fetch_assoc($c)){
    echo "labels.push('".addslashes($r['uraian_belanja'])."');";
    echo "dataKas.push(".$r['kas_basis'].");";
    echo "dataPaguEfektif.push(".$r['jml_pagu_efektif'].");";
}
?>

// HITUNG TOTAL
var totalKas = dataKas.reduce((a,b)=>a+(Number(b)||0),0);
var totalPaguEfektif = dataPaguEfektif.reduce((a,b)=>a+(Number(b)||0),0);

// LABEL TOTAL (DIGANTI)
labels.push("Total Realisasi terhadap Pagu Efektif");

// TAMBAH NILAI BAR TOTAL
dataKas.push(totalKas);
dataPaguEfektif.push(totalPaguEfektif);

// BAR AKRUAL (HANYA UNTUK LABEL TOTAL)
var dataAkrualTotalOnly = new Array(labels.length).fill(null);
dataAkrualTotalOnly[dataAkrualTotalOnly.length - 1] = <?= (float)$total['total_akral'] ?>;

// WARNA BAR (Kas Basis: normal + khusus total)
var colorKasBarNormal    = 'rgba(0, 123, 255, 0.45)';
var colorKasBorderNormal = '#007bff';

var colorKasBarTotal     = 'rgba(0, 184, 165, 0.45)';  // #00B8A5
var colorKasBorderTotal  = '#00B8A5';
// WARNA BAR PAGU EFEKTIF
var colorPaguEfektifBar     = 'rgba(0, 95, 107, 0.45)';
var colorPaguEfektifBorder  = '#005f6b';

// WARNA BAR AKRUAL (SAMA DENGAN TARGET NASIONAL)
var colorAkrualBar          = 'rgba(255, 213, 76, 0.55)'; // #ffd54c
var colorAkrualBorder       = '#ffd54c';

// warna array khusus untuk "Kas/Realisasi" (bar terakhir beda warna)
var kasBgColors = dataKas.map(() => colorKasBarNormal);
var kasBorderColors = dataKas.map(() => colorKasBorderNormal);
kasBgColors[kasBgColors.length - 1] = colorKasBarTotal;
kasBorderColors[kasBorderColors.length - 1] = colorKasBorderTotal;


// =====================================================================
// LABEL DI ATAS BAR (DESKTOP/TABLET ONLY) - PERSENTASE + ANTI TABRAK
// =====================================================================
var showValueLabels = window.innerWidth >= 576; // HP/Android OFF

Chart.plugins.register({
    afterDatasetsDraw: function(chart) {
        if (!showValueLabels) return;

        var ctx = chart.ctx;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        ctx.font = 'bold 11px Arial';
        ctx.fillStyle = '#111';

        var baseOffset = 2;
        var minGapX    = 18;
        var safeTop    = chart.chartArea.top + 12;

        for (var index = 0; index < chart.data.labels.length; index++) {
            var items = [];

            chart.data.datasets.forEach(function(ds, di){
                var meta = chart.getDatasetMeta(di);
                if (meta.hidden) return;

                var val = ds.data[index];
                if (val === null || val === undefined) return;
                if (Number(val) === 0) return;
                if (!meta.data[index]) return;

                // dataset 1 (Jumlah Pagu Efektif) tidak ditampilkan label persen
                if (di === 1) return;

                var el = meta.data[index];
                if (!el || !el._model) return;

                var yTop = Math.min(el._model.y, el._model.base);
                var x = el._model.x;

                // hitung persen
                var pct = 0;
                if (di === 0) {
                    var pagu = Number(dataPaguEfektif[index]) || 0;
                    pct = pagu > 0 ? (Number(val) / pagu * 100) : 0;
                } else if (di === 2) {
                    var paguTotal = Number(totalPaguEfektif) || 0;
                    pct = paguTotal > 0 ? (Number(val) / paguTotal * 100) : 0;
                }

                items.push({
                    x: x,
                    yTop: yTop,
                    text: pct.toFixed(2).replace('.', ',') + "%", // persen saja
                    datasetIndex: di
                });
            });

            // urut stabil (realisasi dulu, akrual setelahnya)
            items.sort(function(a,b){ return a.datasetIndex - b.datasetIndex; });

            // gambar dengan Y tetap nempel bar, kalau tabrakan geser X
            var placed = [];
            items.forEach(function(it){
                var y = it.yTop - baseOffset;

                // FIX: jangan dipaksa turun sampai masuk ke bar
                if (y < safeTop) {
                    if (safeTop >= it.yTop) y = it.yTop - baseOffset;
                    else y = safeTop;
                }

                var x = it.x;

                var tries = 0;
                while (placed.some(function(px){ return Math.abs(px - x) < minGapX; }) && tries < 10) {
                    var dir = (tries % 2 === 0) ? 1 : -1;
                    var step = (Math.floor(tries / 2) + 1) * 10;
                    x = it.x + (dir * step);
                    tries++;
                }

                placed.push(x);
                ctx.fillText(it.text, x, y);
            });
        }

        ctx.restore();
    }
});


// =======================================================
// CHART
// =======================================================
var ctx = document.getElementById('chartKas').getContext('2d');
var LAST_IDX = labels.length - 1;

var chartKas = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Realisasi',
                data: dataKas,
                backgroundColor: kasBgColors,
                borderColor: kasBorderColors,
                borderWidth: 1.5,
                barPercentage: 0.9
            },
            {
                label: 'Jumlah Pagu Efektif',
                data: dataPaguEfektif,
                backgroundColor: colorPaguEfektifBar,
                borderColor: colorPaguEfektifBorder,
                borderWidth: 1.5,
                barPercentage: 0.9
            },
            {
                label: 'Akrual Basis',
                data: dataAkrualTotalOnly,
                backgroundColor: colorAkrualBar,
                borderColor: colorAkrualBorder,
                borderWidth: 1.5,
                barPercentage: 0.9
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        devicePixelRatio: window.devicePixelRatio * 2,

        legend: {
            display: true,
            position: 'top',
            labels: {
                generateLabels: function(chart) {
                    var labelsGen = Chart.defaults.global.legend.labels.generateLabels(chart);
                    if (labelsGen[0]) {
                        labelsGen[0].hidden = !!chart.$realisasiHidden;
                    }
                    labelsGen.splice(1, 0, {
                        text: 'Kas Basis',
                        fillStyle: colorKasBarTotal,
                        strokeStyle: colorKasBorderTotal,
                        lineWidth: 1.5,
                        hidden: !!chart.$kasBasisHidden,
                        _isKasBasisLegend: true
                    });

                    return labelsGen;
                }
            },

            onClick: function(e, legendItem) {
                var ci = this.chart;
                if (!ci.$saved) {
                    ci.$saved = true;
                    ci.$origKas = dataKas.slice();
                    ci.$origKasBg = kasBgColors.slice();
                    ci.$origKasBorder = kasBorderColors.slice();
                }
                function restoreKasBasisColors() {
                    kasBgColors[LAST_IDX] = colorKasBarTotal;
                    kasBorderColors[LAST_IDX] = colorKasBorderTotal;
                }
                function hideKasBasisColors() {
                    kasBgColors[LAST_IDX] = 'rgba(0,0,0,0)';
                    kasBorderColors[LAST_IDX] = 'rgba(0,0,0,0)';
                }
                if (legendItem._isKasBasisLegend) {
                    ci.$kasBasisHidden = !ci.$kasBasisHidden;

                    if (ci.$kasBasisHidden) {
                        restoreKasBasisColors();
                        dataKas[LAST_IDX] = 0;
                        ci.update(450);
                        setTimeout(function(){
                            dataKas[LAST_IDX] = null;
                            hideKasBasisColors();
                            ci.update(0);
                        }, 470);
                    } else {
                        restoreKasBasisColors();
                        dataKas[LAST_IDX] = 0;
                        ci.update(0);

                        setTimeout(function(){
                            dataKas[LAST_IDX] = ci.$origKas[LAST_IDX];
                            ci.update(450);
                        }, 20);
                    }

                    return;
                }

                if (legendItem.datasetIndex === 0) {
                    ci.$realisasiHidden = !ci.$realisasiHidden;

                    if (ci.$realisasiHidden) {
                        for (var i = 0; i < LAST_IDX; i++) {
                            dataKas[i] = 0;
                        }
                        ci.update(450);
                        setTimeout(function(){
                            for (var j = 0; j < LAST_IDX; j++) {
                                dataKas[j] = null;
                            }
                            if (ci.$kasBasisHidden) {
                                dataKas[LAST_IDX] = null;
                                hideKasBasisColors();
                            } else {
                                dataKas[LAST_IDX] = ci.$origKas[LAST_IDX];
                                restoreKasBasisColors();
                            }

                            ci.update(0);
                        }, 470);
                    } else {
                        for (var k = 0; k < LAST_IDX; k++) {
                            dataKas[k] = 0;
                        }
                        if (ci.$kasBasisHidden) {
                            dataKas[LAST_IDX] = null;
                            hideKasBasisColors();
                        } else {
                            dataKas[LAST_IDX] = ci.$origKas[LAST_IDX];
                            restoreKasBasisColors();
                        }

                        ci.update(0);

                        setTimeout(function(){
                            for (var m = 0; m < LAST_IDX; m++) {
                                dataKas[m] = ci.$origKas[m];
                            }
                            ci.update(450);
                        }, 20);
                    }

                    return;
                }
                var meta = ci.getDatasetMeta(legendItem.datasetIndex);
                meta.hidden = meta.hidden === null
                    ? !ci.data.datasets[legendItem.datasetIndex].hidden
                    : null;
                ci.update();
            }
        },

        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                    if (value === null || value === undefined) return null;

                    var formatted = formatNumberID(value);
                    var index = tooltipItem.index;

                    if (tooltipItem.datasetIndex === 0) {
                        var isTotal = (index === labels.length - 1);
                        var customLabel = isTotal ? "Kas Basis" : "Realisasi";

                        var pagu = dataPaguEfektif[index];
                        var percent = pagu > 0 ? (value / pagu * 100) : 0;
                        var percentStr = percent.toFixed(2).replace('.', ',') + "%";
                        return customLabel + ": " + formatted + " (" + percentStr + ")";
                    }

                    if (tooltipItem.datasetIndex === 2) {
                        var paguTotal = totalPaguEfektif;
                        var percent2 = paguTotal > 0 ? (value / paguTotal * 100) : 0;
                        var percentStr2 = percent2.toFixed(2).replace('.', ',') + "%";
                        return "Akrual Basis: " + formatted + " (" + percentStr2 + ")";
                    }

                    var label = data.datasets[tooltipItem.datasetIndex].label;
                    return label + ": " + formatted;
                }
            }
        },

        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: value => formatNumberID(value)
                }
            }],
            xAxes: [{
                categoryPercentage: 0.7,
                barPercentage: 0.8,
                ticks: {
                    autoSkip: false,
                    maxRotation: 0,
                    minRotation: 0,
                    fontSize: window.innerWidth < 576 ? 9 : 11,
                    fontStyle: 'bold',
                    callback: function(value) {
                        var parts = value.split(" ");
                        var lines = [];
                        var bracket = null;
                        if (parts.length && /^\[.*\]$/.test(parts[parts.length - 1])) {
                            bracket = parts.pop();
                        }
                        parts.forEach(function(p){
                            if(p.trim().length > 0){
                                lines.push(p);
                            }
                        });
                        if (bracket) lines.push(bracket);
                        return lines;
                    }
                },
                gridLines: { offsetGridLines: true }
            }]
        }
    }
});

// UPDATE SAAT RESIZE / ROTATE
window.addEventListener('resize', function () {
    showValueLabels = window.innerWidth >= 576;
    chartKas.update();
});

// == REALISASI ==
function downloadTablePDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l', 'pt', 'a4');
    const wrapper = document.getElementById('tabel-pdf');
    const table   = wrapper.querySelector('table');

    const title = document.createElement('div');
    title.textContent = 'DATA REALISASI JENIS BELANJA';
    title.style.textAlign = 'center';
    title.style.fontSize = '22px';
    title.style.fontWeight = '900';
    title.style.marginBottom = '20px';
    title.style.fontFamily = 'Arial, sans-serif';
    wrapper.insertBefore(title, wrapper.firstChild);

    const oldOverflow = wrapper.style.overflow;
    const oldWidth    = wrapper.style.width;
    const oldTableW   = table.style.width;

    wrapper.style.overflow = 'visible';
    wrapper.style.width    = table.scrollWidth + 'px';
    table.style.width      = 'auto';

    html2canvas(wrapper,{scale:2,scrollX:0,scrollY:0,useCORS:true}).then(canvas=>{
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgW = pdfW - 40;
        const imgH = canvas.height * imgW / canvas.width;

        let heightLeft = imgH;
        let position   = 20;

        pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
        heightLeft -= pdfH;
        while(heightLeft>0){
            pdf.addPage();
            position = heightLeft - imgH + 20;
            pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
            heightLeft -= pdfH;
        }

        wrapper.style.overflow = oldOverflow;
        wrapper.style.width    = oldWidth;
        table.style.width      = oldTableW;
        wrapper.removeChild(title);

        pdf.save('realisasi-jenis-belanja.pdf');
    });
}

// == AKRUAL ==
function downloadAkrualPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l','pt','a4');
    const wrapper = document.getElementById('tabel-akrual-pdf');
    const table   = wrapper.querySelector('table');

    const title = document.createElement('div');
    title.textContent = 'PENJELASAN BELANJA AKRUAL';
    title.style.textAlign = 'center';
    title.style.fontSize = '22px';
    title.style.fontWeight = '900';
    title.style.marginBottom = '20px';
    wrapper.insertBefore(title, wrapper.firstChild);

    const oldOverflow = wrapper.style.overflow;
    const oldWidth    = wrapper.style.width;

    wrapper.style.overflow = 'visible';
    wrapper.style.width    = table.scrollWidth + 'px';

    html2canvas(wrapper,{scale:2,scrollX:0,scrollY:0,useCORS:true}).then(canvas=>{
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgW = pdfW - 40;
        const imgH = canvas.height * imgW / canvas.width;

        let heightLeft = imgH;
        let position   = 20;

        pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
        heightLeft -= pdfH;
        while(heightLeft>0){
            pdf.addPage();
            position = heightLeft - imgH + 20;
            pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
            heightLeft -= pdfH;
        }

        wrapper.style.overflow = oldOverflow;
        wrapper.style.width    = oldWidth;
        wrapper.removeChild(title);

        pdf.save('belanja-akrual.pdf');
    });
}

// == SPM ==
function downloadSPMPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l','pt','a4');
    const wrapper = document.getElementById('tabel-spm-pdf');
    const table   = wrapper.querySelector('table');

    const title = document.createElement('div');
    title.textContent = 'DATA SPM BERJALAN';
    title.style.textAlign = 'center';
    title.style.fontSize = '22px';
    title.style.fontWeight = '900';
    title.style.marginBottom = '20px';
    wrapper.insertBefore(title, wrapper.firstChild);

    const oldOverflow = wrapper.style.overflow;
    const oldWidth    = wrapper.style.width;

    wrapper.style.overflow = 'visible';
    wrapper.style.width    = table.scrollWidth + 'px';

    html2canvas(wrapper,{scale:2,scrollX:0,scrollY:0,useCORS:true}).then(canvas=>{
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgW = pdfW - 40;
        const imgH = canvas.height * imgW / canvas.width;

        let heightLeft = imgH;
        let position   = 20;

        pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
        heightLeft -= pdfH;
        while(heightLeft>0){
            pdf.addPage();
            position = heightLeft - imgH + 20;
            pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
            heightLeft -= pdfH;
        }

        wrapper.style.overflow = oldOverflow;
        wrapper.style.width    = oldWidth;
        wrapper.removeChild(title);

        pdf.save('spm-berjalan.pdf');
    });
}

// == PERCEPATAN ==
function downloadPercepatanPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l','pt','a4');
    const wrapper = document.getElementById('tabel-percepatan-pdf');
    const table   = wrapper.querySelector('table');

    const title = document.createElement('div');
    title.textContent = 'UPAYA PERCEPATAN / BELANJA YANG SEGERA DILAKSANAKAN';
    title.style.textAlign = 'center';
    title.style.fontSize = '20px';
    title.style.fontWeight = '900';
    title.style.marginBottom = '20px';
    wrapper.insertBefore(title, wrapper.firstChild);

    const oldOverflow = wrapper.style.overflow;
    const oldWidth    = wrapper.style.width;

    wrapper.style.overflow = 'visible';
    wrapper.style.width    = table.scrollWidth + 'px';

    html2canvas(wrapper,{scale:2,scrollX:0,scrollY:0,useCORS:true}).then(canvas=>{
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgW = pdfW - 40;
        const imgH = canvas.height * imgW / canvas.width;

        let heightLeft = imgH;
        let position   = 20;

        pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
        heightLeft -= pdfH;
        while(heightLeft>0){
            pdf.addPage();
            position = heightLeft - imgH + 20;
            pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
            heightLeft -= pdfH;
        }

        wrapper.style.overflow = oldOverflow;
        wrapper.style.width    = oldWidth;
        wrapper.removeChild(title);

        pdf.save('upaya-percepatan.pdf');
    });
}

// TARGET REALISASI
function downloadTargetPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l','pt','a4');
    const wrapper = document.getElementById('tabel-target-pdf');
    const table   = wrapper.querySelector('table');

    const title = document.createElement('div');
    title.textContent = 'TARGET REALISASI SAMPAI DENGAN AKHIR BULAN (<?= addslashes($labelTargetAkhirBulan) ?>)';
    title.style.textAlign = 'center';
    title.style.fontSize = '20px';
    title.style.fontWeight = '900';
    title.style.fontFamily = 'Arial, sans-serif';
    title.style.marginBottom = '20px';
    wrapper.insertBefore(title, wrapper.firstChild);

    const oldOverflow = wrapper.style.overflow;
    const oldWidth    = wrapper.style.width;

    wrapper.style.overflow = 'visible';
    wrapper.style.width    = table.scrollWidth + 'px';

    html2canvas(wrapper,{scale:2,scrollX:0,scrollY:0,useCORS:true}).then(canvas=>{
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgW = pdfW - 40;
        const imgH = canvas.height * imgW / canvas.width;

        let heightLeft = imgH;
        let position   = 20;

        pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
        heightLeft -= pdfH;
        while(heightLeft>0){
            pdf.addPage();
            position = heightLeft - imgH + 20;
            pdf.addImage(canvas.toDataURL('image/png'),'PNG',20,position,imgW,imgH);
            heightLeft -= pdfH;
        }

        wrapper.style.overflow = oldOverflow;
        wrapper.style.width    = oldWidth;
        wrapper.removeChild(title);

        pdf.save('target-realisasi.pdf');
    });
}
</script>

</body>
</html>