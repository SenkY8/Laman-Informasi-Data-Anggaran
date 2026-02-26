<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// ===== AMBIL TAHUN DARI GET PARAMETER =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// ===== HAPUS TOTAL PAGU EFEKTIF UNTUK TAHUN TERTENTU =====
mysqli_query($koneksi, "DELETE FROM pnbp_total_pagu_efektif WHERE tahun = $tahun AND id=1");

// ===== REDIRECT DENGAN PARAMETER TAHUN =====
header("Location: index.php?tahun=$tahun");
exit;
?>