<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if($id > 0){
    // Ambil tahun dari data dulu
    $data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT tahun FROM pk_2025_kinerja WHERE id=$id"));
    if($data) {
        $tahun = $data['tahun'];
    }
    mysqli_query($koneksi, "DELETE FROM pk_2025_kinerja WHERE id=$id");
}

header("Location: index.php?tahun=$tahun");
exit;
?>