<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

/* ================== TENTUKAN TRIWULAN BERDASARKAN BULAN SEKARANG ==================
   TW1: 1 Jan – 31 Mar
   TW2: 1 Apr – 30 Jun
   TW3: 1 Jul – 30 Sep
   TW4: 1 Okt – 31 Des
*/
$bulanSekarang = (int)date('n');
if ($bulanSekarang >= 1 && $bulanSekarang <= 3) {
    $currentTw = 1;
} elseif ($bulanSekarang >= 4 && $bulanSekarang <= 6) {
    $currentTw = 2;
} elseif ($bulanSekarang >= 7 && $bulanSekarang <= 9) {
    $currentTw = 3;
} else {
    $currentTw = 4;
}

$twLabel = [
    1 => 'Tw 1 (Jan–Mar)',
    2 => 'Tw 2 (Apr–Jun)',
    3 => 'Tw 3 (Jul–Sep)',
    4 => 'Tw 4 (Okt–Des)',
];

if(isset($_POST['simpan'])){
    $persen  = (float)$_POST['akrual_persen'];

    // kas_basis diset 0 (tidak dipakai di tampilan sekarang)
    mysqli_query($koneksi, "
        INSERT INTO target_realisasi_nasional (tw, kas_basis, akrual_persen)
        VALUES ('$currentTw', 0, '$persen')
    ");

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Target Realisasi Nasional</title>
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
                <h5 class="m-0 font-weight">Tambah Target Realisasi Nasional</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <!-- TW otomatis, hanya ditampilkan sebagai informasi -->
                    <div class="mb-3">
                        <label class="form-label">Triwulan (otomatis)</label>
                        <input type="text" class="form-control" value="<?= $twLabel[$currentTw] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akrual Basis (Persentase %)</label>
                        <input type="number" step="0.01" name="akrual_persen" class="form-control" required>
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
