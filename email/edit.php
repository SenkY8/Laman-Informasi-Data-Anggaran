<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function clean($conn, $str) {
    return mysqli_real_escape_string($conn, trim($str));
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'ID email tidak valid.';
    header("Location: index.php");
    exit;
}

// Ambil data email berdasarkan ID
$res = mysqli_query($koneksi, "SELECT * FROM email_list WHERE id = $id");
if (!$res || mysqli_num_rows($res) == 0) {
    $_SESSION['error'] = 'Data email tidak ditemukan.';
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($res);
$nama  = htmlspecialchars($row['nama']);
$email = htmlspecialchars($row['email']);

// Ambil pesan flash (jika ada)
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Email</title>
<link href="../asset/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">

<style>
/* SIDEBAR WARNA KEMENKES */
.bg-gradient-primary {
    background-color: #0DBBCB !important;
    background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
}

/* WARNA LINK SIDEBAR */
.sidebar .nav-item .nav-link {
    color: #ffffff !important;
}
.sidebar .nav-item .nav-link:hover {
    background-color: #009AA8 !important;
}

/* ACTIVE MENU */
.sidebar .nav-item.active .nav-link {
    background-color: #0DBBCB !important;
    color: #ffffff !important;
}

/* BUTTON UTAMA */
.btn-primary {
    background-color: #0DBBCB !important;
    border-color: #0DBBCB !important;
}
.btn-primary:hover {
    background-color: #0b9e88ff !important;
}

/* CARD BORDER KEMENKES */
.card {
    border-left: 4px solid #0DBBCB !important;
}

.center { text-align:center; }
.right  { text-align:right; }
</style>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- SIDEBAR (sama seperti index.php, cuma menu Broadcast Email yang aktif) -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item">
            <a class="nav-link" href="../realisasi_jenis_belanja/index.php">
                <span>Realisasi Berdasarkan Jenis Belanja</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../belanja_akrual/index.php">
                <span>Penjelasan Belanja Akrual</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../spm_berjalan/index.php">
                <span>SPM Berjalan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../upaya_percepatan/index.php">
                <span>Upaya percepatan/belanja yang segera dilaksanakan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php">
                <span>Permasalahan & Rencana Tindak Lanjut</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <span>Broadcast Email</span>
            </a>
        </li>

        <hr class="sidebar-divider my-2">
        <li class="nav-item">
            <a class="nav-link" href="../logout.php"><span>Kembali</span></a>
        </li>
    </ul>
    <!-- END SIDEBAR -->

    <!-- CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content" class="p-4">

            <h2>Edit Email</h2>
            <p>Perbaiki nama atau alamat email yang sudah terdaftar.</p>
            <hr>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong><i class="fas fa-edit"></i> Form Edit Email</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="process.php">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= (int)$id ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required
                                   value="<?= $nama ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= $email ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="index.php" class="btn btn-secondary ms-2">
                            Batal
                        </a>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>

<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../asset/vendor/fontawesome-free/js/all.min.js"></script>
</body>
</html>
