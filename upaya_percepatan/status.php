<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];
$status = $_GET['status'];

$allowed = ['belum','proses','selesai'];
if (!in_array($status, $allowed)) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "UPDATE percepatan SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: index.php");
exit;
?>