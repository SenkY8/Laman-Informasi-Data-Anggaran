<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../login.php");
    exit;
}
include "../../koneksi.php";

// Ambil parameter
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$uraian = isset($_GET['uraian']) ? trim($_GET['uraian']) : '';

if (!$tahun || !$uraian) {
    $_SESSION['message'] = 'Data tidak valid!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../index.php");
    exit;
}

// Cek data ada atau tidak
$cek = mysqli_query($koneksi, "SELECT id FROM komparasi_nilai_pagu WHERE tahun = $tahun AND uraian = '$uraian'");

if (mysqli_num_rows($cek) == 0) {
    $_SESSION['message'] = 'Data tidak ditemukan!';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../index.php");
    exit;
}

// Hapus data berdasarkan tahun dan uraian
$sql = "DELETE FROM komparasi_nilai_pagu WHERE tahun = $tahun AND uraian = '$uraian'";

if (mysqli_query($koneksi, $sql)) {
    $_SESSION['message'] = 'Data ' . $uraian . ' tahun ' . $tahun . ' berhasil dihapus!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error: ' . mysqli_error($koneksi);
    $_SESSION['message_type'] = 'danger';
}

header("Location: ../index.php");
exit;
?>