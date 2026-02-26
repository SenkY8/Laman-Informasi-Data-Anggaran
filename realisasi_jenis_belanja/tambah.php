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
    $uraian = $_POST['uraian_belanja'];

    $pagu   = (int) $_POST['jml_pagu'];
    $blokir = ($_POST['jml_blokir'] === "" ? 0 : (int) $_POST['jml_blokir']);

    $real1 = (int) $_POST['realisasi_seluruh_1'];
    $real2 = (int) $_POST['realisasi_seluruh_2'];

    // hitung pagu efektif
    $pagu_efektif = $pagu - $blokir;

    // hitung persentase realisasi seluruh pagu
    $persen_real1 = ($pagu > 0) ? ($real1 / $pagu) * 100 : 0;
    $persen_real2 = ($pagu > 0) ? ($real2 / $pagu) * 100 : 0;

    // persentase blokir
    $persen_blokir = ($pagu > 0) ? ($blokir / $pagu) * 100 : 0;

    // realisasi pagu efektif = mengikuti real1 (kas) & real2 (akrual)
    $kas_basis = $real1;
    $akrual    = $real2;

    // persen efektif
    $persen_kas_basis = ($pagu_efektif > 0) ? ($kas_basis / $pagu_efektif) * 100 : 0;
    $persen_akrual    = ($pagu_efektif > 0) ? ($akrual / $pagu_efektif) * 100 : 0;

    // sisa seluruh pagu
    $sisa_seluruh_kas   = $pagu - $real1;
    $sisa_seluruh_akrual = $pagu - $real2;

    // sisa efektif
    $sisa_efektif_kas   = $pagu_efektif - $real1;
    $sisa_efektif_akrual = $pagu_efektif - $real2;

    mysqli_query($koneksi, "INSERT INTO realisasi_jenis_belanja SET
        tahun='".$_POST['tahun']."',
        uraian_belanja='$uraian',

        jml_pagu='$pagu',
        jml_blokir='$blokir',
        persen_blokir='$persen_blokir',
        jml_pagu_efektif='$pagu_efektif',

        realisasi_seluruh_1='$real1',
        persen_realisasi_seluruh_1='$persen_real1',
        realisasi_seluruh_2='$real2',
        persen_realisasi_seluruh_2='$persen_real2',

        kas_basis='$kas_basis',
        persen_kas_basis='$persen_kas_basis',
        akral='$akrual',
        persen_akrual='$persen_akrual',

        sisa_seluruh_kas='$sisa_seluruh_kas',
        sisa_seluruh_akrual='$sisa_seluruh_akrual',
        sisa_efektif_kas='$sisa_efektif_kas',
        sisa_efektif_akrual='$sisa_efektif_akrual'
    ");

    header("Location: index.php?tahun=".$_POST['tahun']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Data</title>
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
<div class="container mt-4">
<div class="card shadow">
<div class="card-header"><h5 class="m-0">Tambah Data - Tahun <?= $tahun ?></h5></div>
<div class="card-body">

<form method="post">

    <label>Tahun</label>
    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required>

    <label>Uraian Belanja</label>
    <input type="text" name="uraian_belanja" class="form-control" required>

    <label>Jumlah Pagu</label>
    <input type="number" name="jml_pagu" class="form-control" required>

    <label>Jumlah Blokir (boleh kosong)</label>
    <input type="number" name="jml_blokir" class="form-control">

    <hr>
    <div class="row">
        <div class="col-md-6">
            <label>Kas Basis (Realisasi (Seluruh Pagu))</label>
            <input type="number" name="realisasi_seluruh_1" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Akrual (Realisasi (Seluruh Pagu))</label>
            <input type="number" name="realisasi_seluruh_2" class="form-control" required>
        </div>
    </div>

    <br>

    <button class="btn btn-primary" name="simpan">Simpan</button>
    <a href="index.php?tahun=<?= $tahun ?>" class="btn btn-secondary">Kembali</a>

</form>

</div>
</div>
</div>
</body>
</html>