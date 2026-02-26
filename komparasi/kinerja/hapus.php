<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../../login.php");
    exit;
}
include "../../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0) {
    mysqli_query($koneksi, "DELETE FROM komparasi_nilai_kinerja WHERE id=$id");
}

header("Location: ../index.php");
exit;
?>