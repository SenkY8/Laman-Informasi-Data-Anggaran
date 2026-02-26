<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

// Ambil data admin berdasarkan username
$q = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
$user = mysqli_fetch_assoc($q);

if ($user) {
    if ($user['password'] === $password) {

        // Set session
        $_SESSION['login'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];

        header("Location: realisasi_jenis_belanja/index.php");
        exit;

    } else {
        echo "<script>alert('Password salah!');window.location='login.php';</script>";
        exit;
    }

} else {
    echo "<script>alert('Username tidak ditemukan!');window.location='login.php';</script>";
    exit;
}
?>
