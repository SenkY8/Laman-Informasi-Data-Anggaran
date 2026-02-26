<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../login.php");
    exit;
}
include "../../koneksi.php";

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['message'] = 'Data tidak valid!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../index.php");
    exit;
}

// Cek data ada atau tidak
$cek = mysqli_query($koneksi, "SELECT id FROM komparasi_indikator_kinerja WHERE id = $id");

if (mysqli_num_rows($cek) == 0) {
    $_SESSION['message'] = 'Data tidak ditemukan!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../index.php");
    exit;
}

// Hapus data
$sql = "DELETE FROM komparasi_indikator_kinerja WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    $_SESSION['message'] = 'Data berhasil dihapus!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error: ' . mysqli_error($koneksi);
    $_SESSION['message_type'] = 'danger';
}

header("Location: ../index.php");
exit;
?>