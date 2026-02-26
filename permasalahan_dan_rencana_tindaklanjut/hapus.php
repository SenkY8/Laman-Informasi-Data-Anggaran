<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Ambil data dulu untuk mendapat tahunnya
    $data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT tahun FROM permasalahan_tindak_lanjut WHERE id=$id"));
    
    if ($data) {
        $tahun = $data['tahun'];
        // Hapus data
        mysqli_query($koneksi, "DELETE FROM permasalahan_tindak_lanjut WHERE id = $id");
        header("Location: index.php?tahun=$tahun");
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}
exit;
?>