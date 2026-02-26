<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// ===== AMBIL ID DAN TAHUN DARI GET PARAMETER =====
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if($id > 0){
    mysqli_query($koneksi, "DELETE FROM pnbp WHERE id=$id");
}

// ===== REDIRECT DENGAN PARAMETER TAHUN =====
header("Location: index.php?tahun=$tahun");
exit;
?>