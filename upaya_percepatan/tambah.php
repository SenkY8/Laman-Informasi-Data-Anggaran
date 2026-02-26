<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// Ambil tahun dari GET, default ke tahun sekarang
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if (isset($_POST['simpan'])) {
    $tahun_input = (int)$_POST['tahun'];
    $uraian = $_POST['uraian_belanja'];
    $jumlah = $_POST['jumlah'];
    $realisasi = $_POST['realisasi'];
    $keterangan = $_POST['keterangan'];

    mysqli_query($koneksi, "INSERT INTO percepatan SET 
        tahun='$tahun_input',
        uraian_belanja='$uraian',
        jumlah='$jumlah',
        realisasi='$realisasi',
        keterangan='$keterangan'
    ");

    header("Location: index.php?tahun=$tahun_input");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Percepatan</title>
    <link href="../asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            background: #EAF7F2;
        }

        .card-header {
            background: #0DBBCB;
            color: white;
            border-bottom: 3px solid #00AEEF;
        }

        .btn-primary {
            background: #0DBBCB;
            border-color: #0DBBCB;
        }
        .btn-primary:hover {
            background: #0DBBCB;
            border-color: #0DBBCB;
        }

        .btn-secondary:hover {
            background: #cccccc;
        }

        label {
            font-weight: 600;
            color: #0b9e88ff;
        }

        input.form-control,
        textarea.form-control {
            border-radius: 6px;
            border: 1px solid #0b9e88ff;
        }
        input.form-control:focus,
        textarea.form-control:focus {
            border-color: #00AEEF;
            box-shadow: 0 0 4px rgba(0, 174, 239, 0.6);
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <div class="card shadow" style="width: 40rem;">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight">Tambah Data - Tahun <?= $tahun ?></h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Uraian Belanja</label>
                        <input type="text" name="uraian_belanja" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" step="0.01" name="jumlah" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Realisasi</label>
                        <input type="number" step="0.01" name="realisasi" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-primary" name="simpan">Simpan</button>
                    <a href="index.php?tahun=<?= $tahun ?>" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../asset/vendor/jquery/jquery.min.js"></script>
<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../asset/js/sb-admin-2.min.js"></script>
</body>
</html>