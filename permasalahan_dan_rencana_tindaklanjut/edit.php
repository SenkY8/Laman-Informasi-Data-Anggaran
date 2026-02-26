<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT * FROM permasalahan_tindak_lanjut WHERE id = $id
"));

if (!$data) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $tahun_baru = (int)$_POST['tahun'];
    $permasalahan  = mysqli_real_escape_string($koneksi, $_POST['permasalahan']);
    $tindak_lanjut = mysqli_real_escape_string($koneksi, $_POST['tindak_lanjut']);

    mysqli_query($koneksi, "
        UPDATE permasalahan_tindak_lanjut SET
            tahun = '$tahun_baru',
            permasalahan  = '$permasalahan',
            tindak_lanjut = '$tindak_lanjut'
        WHERE id = $id
    ");

    header("Location: index.php?tahun=$tahun_baru");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Permasalahan & Tindak Lanjut</title>
    <link href="../asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body { background: #EAF7F2; }

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
            background: #0b9e88ff;
            border-color: #0b9e88ff;
        }

        .btn-secondary:hover {
            background: #cccccc;
        }

        label {
            font-weight: 600;
            color: #0b9e88ff;
        }

        textarea.form-control,
        input.form-control {
            border-radius: 6px;
            border: 1px solid #0b9e88ff;
        }
        textarea.form-control:focus,
        input.form-control:focus {
            border-color: #00AEEF;
            box-shadow: 0 0 4px rgba(0, 174, 239, 0.6);
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <div class="card shadow" style="width: 50rem;">
            <div class="card-header">
                <h5 class="m-0">Edit Permasalahan & Tindak Lanjut - Tahun <?= $data['tahun'] ?></h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="<?= $data['tahun'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Permasalahan</label>
                        <textarea name="permasalahan" rows="4"
                                  class="form-control" required><?= htmlspecialchars($data['permasalahan']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Tindak Lanjut</label>
                        <textarea name="tindak_lanjut" rows="4"
                                  class="form-control" required><?= htmlspecialchars($data['tindak_lanjut']) ?></textarea>
                    </div>

                    <button class="btn btn-primary" name="simpan">Simpan</button>
                    <a href="index.php?tahun=<?= $data['tahun'] ?>" class="btn btn-secondary">Kembali</a>
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