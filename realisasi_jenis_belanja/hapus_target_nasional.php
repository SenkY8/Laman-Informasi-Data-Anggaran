<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

mysqli_query($koneksi, "DELETE FROM target_realisasi_nasional WHERE id='$id'");

header("Location: index.php");
exit;
