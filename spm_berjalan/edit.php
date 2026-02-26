<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = $_GET['id'];
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM spm_berjalan WHERE id='$id'"));

if (!$data) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['simpan'])) {

    $tahun_baru = (int)$_POST['tahun'];
    $uraian = $_POST['uraian_belanja'];
    $jumlah = $_POST['jumlah_belanja'];

    mysqli_query($koneksi, "UPDATE spm_berjalan SET 
        tahun='$tahun_baru',
        uraian_belanja='$uraian',
        jumlah_belanja='$jumlah'
        WHERE id='$id'
    ");

    header("Location: index.php?tahun=$tahun_baru");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit SPM Berjalan</title>
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

        input.form-control {
            border-radius: 6px;
            border: 1px solid #0b9e88ff;
        }
        input.form-control:focus {
            border-color: #00AEEF;
            box-shadow: 0 0 4px rgba(0, 174, 239, 0.6);
        }
    </style>
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-center">
        <div class="card shadow" style="width: 40rem;">
            
            <div class="card-header">
                <h5 class="m-0">Edit Data - Tahun <?= $data['tahun'] ?></h5>
            </div>

            <div class="card-body">

                <form method="post">

                    <div class="mb-3">
                        <label>Tahun</label>
                        <input type="number" name="tahun" value="<?= $data['tahun'] ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Uraian Belanja</label>
                        <input type="text" name="uraian_belanja" value="<?= $data['uraian_belanja'] ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Jumlah Belanja</label>
                        <input type="number" step="0.01" name="jumlah_belanja" value="<?= $data['jumlah_belanja'] ?>" class="form-control" required>
                    </div>

                    <button class="btn btn-primary" name="simpan">Simpan</button>
                    <a href="index.php?tahun=<?= $data['tahun'] ?>" class="btn btn-secondary">Kembali</a>

                </form>

            </div>

        </div>
    </div>

</div>

</body>
</html>