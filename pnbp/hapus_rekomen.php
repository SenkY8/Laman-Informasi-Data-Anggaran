<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// ===== AMBIL TAHUN DARI GET PARAMETER =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// ===== KOSONGKAN REKOMENDASI UNTUK TAHUN TERTENTU =====
$stmt = mysqli_prepare($koneksi, "UPDATE pnbp_rekomendasi SET rekomendasi='' WHERE tahun=? AND id=1");
mysqli_stmt_bind_param($stmt, "i", $tahun);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ===== REDIRECT KE INDEX DENGAN PARAMETER TAHUN =====
header("Location: index.php?tahun=$tahun");
exit;
?>