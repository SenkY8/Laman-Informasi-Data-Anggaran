<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id   = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query(
    $koneksi, "SELECT * FROM target_realisasi_nasional WHERE id='$id'"
));

if(!$data){
    header("Location: index.php");
    exit;
}

$twLabel = [
    1 => 'Tw 1 (Jan–Mar)',
    2 => 'Tw 2 (Apr–Jun)',
    3 => 'Tw 3 (Jul–Sep)',
    4 => 'Tw 4 (Okt–Des)',
];

if(isset($_POST['simpan'])){
    $persen  = (float)$_POST['akrual_persen'];

    // TW tidak diubah, hanya persentase yang diperbarui
    mysqli_query($koneksi, "
        UPDATE target_realisasi_nasional
        SET akrual_persen='$persen'
        WHERE id='$id'
    ");

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Target Realisasi Nasional</title>
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

        select.form-control,
        input.form-control {
            border-radius: 6px;
            border: 1px solid #0b9e88ff;
        }
        select.form-control:focus,
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
            <div class="card-header py-3">
                <h5 class="m-0 font-weight">Edit Target Realisasi Nasional</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <!-- TW tetap, tidak dapat diubah -->
                    <div class="mb-3">
                        <label class="form-label">Triwulan</label>
                        <input type="text" class="form-control"
                               value="<?= isset($twLabel[$data['tw']]) ? $twLabel[$data['tw']] : ('Tw '.$data['tw']) ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akrual Basis (Persentase %)</label>
                        <input type="number" step="0.01" name="akrual_persen"
                               value="<?= htmlspecialchars($data['akrual_persen']) ?>"
                               class="form-control" required>
                    </div>

                    <button class="btn btn-primary" name="simpan">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
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
