<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Ambil data dulu untuk mendapat tahunnya
    $data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT tahun FROM percepatan WHERE id='$id'"));
    
    if ($data) {
        $tahun = $data['tahun'];
        // Hapus hanya data dengan ID
        mysqli_query($koneksi, "DELETE FROM percepatan WHERE id='$id'");
        header("location:index.php?tahun=$tahun");
    } else {
        header("location:index.php");
    }
} else {
    header("location:index.php");
}
?>