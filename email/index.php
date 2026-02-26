<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// Ambil pesan flash
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Ambil semua email
$email_list = mysqli_query($koneksi, "SELECT * FROM email_list ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Broadcast Instruksi</title>
<link href="../asset/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">

<style>
.bg-gradient-primary {
    background-color: #0DBBCB !important;
    background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
}
.sidebar .nav-item .nav-link { color:#fff !important; }
.sidebar .nav-item .nav-link:hover { background:#009AA8 !important; }
.sidebar .nav-item.active .nav-link { background:#0DBBCB !important; color:#fff !important; }

.btn-primary { background:#0DBBCB !important; border-color:#0DBBCB !important; }
.btn-primary:hover { background:#0b9e88ff !important; }

.table thead th { background:#0DBBCB !important; color:white !important; }

.card { border-left:4px solid #0DBBCB !important; }
.center { text-align:center; }
</style>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- SIDEBAR -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item"><a class="nav-link" href="../realisasi_jenis_belanja/index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../spm_berjalan/index.php"><span>SPM Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php"><span>Permasalahan & Rencana Tindak Lanjut</span></a></li>

        <li class="nav-item active">
            <a class="nav-link" href="index.php"><span>Broadcast Instruksi</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pnbp/index.php">
                <span>Data Realisasi PNBP</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pk/index.php">
                <span>Perjanjian Kinerja</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../komparasi/index.php">
                <span>Komparasi Pelaksanaan Anggaran</span>
            </a>
        </li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item"><a class="nav-link" href="../logout.php"><span>Kembali</span></a></li>
    </ul>
    <!-- END SIDEBAR -->

    <!-- CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Broadcast Instruksi</h2>
            <p>Kirim informasi ke alamat email yang sudah terdaftar.</p>
            <hr>

            <!-- FLASH MESSAGE -->
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

            <!-- CARD: FORM BROADCAST -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong><i class="fas fa-paper-plane"></i> Kirim Pesan ke Semua Email</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="process.php">
                        <input type="hidden" name="action" value="broadcast">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subjek Email</label>
                            <input type="text" name="subject" id="subject" class="form-control"
                                   placeholder="Masukkan subjek email">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Isi Pesan</label>
                            <textarea name="message" id="message" rows="5" class="form-control"
                                      placeholder="Tulis isi pesan yang akan dikirim"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Instruksi
                        </button>
                    </form>
                </div>
            </div>

            <!-- CARD: FORM TAMBAH EMAIL BARU -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong><i class="fas fa-envelope"></i> Tambah Email Baru</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="process.php">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="0">

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Tambah Email
                        </button>
                    </form>
                </div>
            </div>

            <!-- CARD: TABEL LIST EMAIL -->
            <div class="card shadow">
                <div class="card-header">
                    <strong><i class="fas fa-list"></i> Daftar Email Terdaftar</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="center" style="width:60px;">No</th>
                                <th class="center">Nama</th>
                                <th class="center">Email</th>
                                <th class="center" style="width:150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($email_list) > 0):
                            while($row = mysqli_fetch_assoc($email_list)):
                        ?>
                            <tr>
                                <td class="center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td class="center">
                                    <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <a href="process.php?action=delete&id=<?= (int)$row['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus email ini?');">
                                       Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="4" class="center">Belum ada email tersimpan.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../asset/vendor/fontawesome-free/js/all.min.js"></script>
</body>
</html>
