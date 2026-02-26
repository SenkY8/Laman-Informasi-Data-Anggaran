<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// SAAT DEBUG (boleh kamu hapus nanti kalau sudah jalan)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========= PHPMailer =========
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// STRUKTUR: kemenkes1/email/process.php → ../phpmailer/src/...
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

function clean($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'save') {
    // TAMBAH / EDIT EMAIL
    $id    = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama  = clean($koneksi, $_POST['nama'] ?? '');
    $email = clean($koneksi, $_POST['email'] ?? '');

    if ($nama === '' || $email === '') {
        $_SESSION['error'] = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid.';
    } else {
        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE email_list SET nama='$nama', email='$email' WHERE id=$id";
            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['success'] = 'Email berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui email: '.mysqli_error($koneksi);
            }
        } else {
            // INSERT
            $sql = "INSERT INTO email_list (nama, email) VALUES ('$nama', '$email')";
            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['success'] = 'Email baru berhasil ditambahkan.';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan email: '.mysqli_error($koneksi);
            }
        }
    }

    header("Location: index.php");
    exit;

} elseif ($action === 'delete') {

    // HAPUS EMAIL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $sql = "DELETE FROM email_list WHERE id=$id";
        if (mysqli_query($koneksi, $sql)) {
            $_SESSION['success'] = 'Email berhasil dihapus.';
        } else {
            $_SESSION['error'] = 'Gagal menghapus email: '.mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = 'ID email tidak valid.';
    }

    header("Location: index.php");
    exit;

} elseif ($action === 'broadcast') {

    // KIRIM EMAIL MASSAL via PHPMailer (SMTP)
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($subject === '' || $message === '') {
        $_SESSION['error'] = 'Subjek dan isi pesan wajib diisi untuk mengirim email.';
        header("Location: index.php");
        exit;
    }

    $result = mysqli_query($koneksi, "SELECT * FROM email_list ORDER BY id ASC");
    $jumlah_terkirim = 0;
    $jumlah_total    = mysqli_num_rows($result);

    if ($jumlah_total == 0) {
        $_SESSION['error'] = 'Belum ada email yang terdaftar untuk dikirimi pesan.';
        header("Location: index.php");
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // ========= KONFIGURASI SMTP DI SINI =========
        $mail->isSMTP();

        // GANTI BAGIAN INI SESUAI SERVER SMTP YANG KAMU PAKAI
        $mail->Host       = 'smtp.gmail.com';          // contoh pakai Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nopriadi2411@gmail.com';    // GANTI: email pengirim
        $mail->Password   = 'kiovgmzrihzozxml';    // GANTI: app password / password SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // atau PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = 587;                       // 587 (TLS) atau 465 (SSL)

        // From harus cocok dengan akun SMTP di atas
        $mail->setFrom('email_kamu@gmail.com', 'MinDA BBKK Batam'); // SESUAIKAN
        // ============================================

        $mail->isHTML(false); // isi pesan sebagai text biasa; set true kalau mau HTML

        $failed = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $toEmail = $row['email'];
            $toName  = $row['nama'] ?? '';

            $mail->clearAddresses();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            try {
                if ($mail->send()) {
                    $jumlah_terkirim++;
                } else {
                    $failed[] = $toEmail;
                }
            } catch (Exception $e) {
                $failed[] = $toEmail;
            }
        }

        if ($jumlah_terkirim == 0) {
            $_SESSION['error'] = 'Tidak ada email yang berhasil terkirim. Cek konfigurasi SMTP atau kredensial email. Gagal ke: ' . implode(', ', $failed);
        } else {
            $msg = "Proses pengiriman selesai. Berhasil terkirim ke $jumlah_terkirim dari $jumlah_total email.";
            if (!empty($failed)) {
                $msg .= ' Gagal ke: ' . implode(', ', $failed);
            }
            $_SESSION['success'] = $msg;
        }

    } catch (Exception $e) {
        $_SESSION['error'] = 'Terjadi error saat konfigurasi PHPMailer: ' . $e->getMessage();
    }

    header("Location: index.php");
    exit;

} else {
    $_SESSION['error'] = 'Aksi tidak dikenal.';
    header("Location: index.php");
    exit;
}
?>