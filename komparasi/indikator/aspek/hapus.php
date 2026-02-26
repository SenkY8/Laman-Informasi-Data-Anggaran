<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../../login.php");
    exit;
}
include "../../../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['message'] = 'Data tidak valid!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../../index.php");
    exit;
}

$cek = mysqli_query($koneksi, "SELECT id FROM indikator_aspek WHERE id = $id");

if (mysqli_num_rows($cek) == 0) {
    $_SESSION['message'] = 'Data tidak ditemukan!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../../index.php");
    exit;
}

$sql = "DELETE FROM indikator_aspek WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    $_SESSION['message'] = 'Data berhasil dihapus!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error: ' . mysqli_error($koneksi);
    $_SESSION['message_type'] = 'danger';
}

header("Location: kelola.php");
exit;
?>