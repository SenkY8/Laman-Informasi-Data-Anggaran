<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = $_GET['id'];
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM realisasi_jenis_belanja WHERE id='$id' AND tahun='$tahun'"));

if (!$data) {
    header("Location: index.php?tahun=$tahun");
    exit;
}

if (isset($_POST['update'])) {

    // ambil input user
    $tahun_baru = (int)$_POST['tahun'];
    $uraian = $_POST['uraian_belanja'];
    $pagu   = (int) $_POST['jml_pagu'];
    $blokir = ($_POST['jml_blokir'] === "" ? 0 : (int) $_POST['jml_blokir']);
    $real1  = (int) $_POST['realisasi_seluruh_1'];
    $real2  = (int) $_POST['realisasi_seluruh_2'];

    // hitung pagu efektif
    $pagu_efektif = $pagu - $blokir;

    // hitung persentase
    $persen_real1 = ($pagu > 0) ? ($real1 / $pagu) * 100 : 0;
    $persen_real2 = ($pagu > 0) ? ($real2 / $pagu) * 100 : 0;
    $persen_blokir = ($pagu > 0) ? ($blokir / $pagu) * 100 : 0;

    // realisasi pagu efektif
    $kas_basis = $real1;
    $akrual    = $real2;
    $persen_kas_basis = ($pagu_efektif > 0) ? ($kas_basis / $pagu_efektif) * 100 : 0;
    $persen_akrual    = ($pagu_efektif > 0) ? ($akrual / $pagu_efektif) * 100 : 0;

    // sisa
    $sisa_seluruh_kas   = $pagu - $real1;
    $sisa_seluruh_akrual = $pagu - $real2;
    $sisa_efektif_kas   = $pagu_efektif - $real1;
    $sisa_efektif_akrual = $pagu_efektif - $real2;

    // update DB dengan tahun baru
    mysqli_query($koneksi, "UPDATE realisasi_jenis_belanja SET
        tahun='$tahun_baru',
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
        WHERE id='$id'
    ");

    header("Location: index.php?tahun=$tahun_baru");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Data</title>
    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
     <style>
        body {
            background: #EAF7F2;
        }

        .card-header {
            background: #009B6F;
            color: white;
            border-bottom: 3px solid #00AEEF;
        }

        .btn-primary {
            background: #009B6F;
            border-color: #009B6F;
        }
        .btn-primary:hover {
            background: #007A57;
            border-color: #007A57;
        }

        .btn-secondary:hover {
            background: #cccccc;
        }

        label {
            font-weight: 600;
            color: #005C47;
        }

        input.form-control {
            border-radius: 6px;
            border: 1px solid #009B6F;
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
<div class="card-header"><h5 class="m-0">Edit Data - Tahun <?= $tahun ?></h5></div>
<div class="card-body">

<form method="post">
    <label>Tahun</label>
    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required>

    <label>Uraian Belanja</label>
    <input type="text" name="uraian_belanja" class="form-control" value="<?= $data['uraian_belanja'] ?>" required>

    <label>Jumlah Pagu</label>
    <input type="number" name="jml_pagu" class="form-control" value="<?= $data['jml_pagu'] ?>" required>

    <label>Jumlah Blokir (boleh kosong)</label>
    <input type="number" name="jml_blokir" class="form-control" value="<?= $data['jml_blokir'] ?>">

    <hr>
    <div class="row">
        <div class="col-md-6">
            <label>Kas Basis (Realisasi (Seluruh Pagu))</label>
            <input type="number" name="realisasi_seluruh_1" class="form-control" value="<?= $data['realisasi_seluruh_1'] ?>" required>
        </div>
        <div class="col-md-6">
            <label>Akrual (Realisasi (Seluruh Pagu))</label>
            <input type="number" name="realisasi_seluruh_2" class="form-control" value="<?= $data['realisasi_seluruh_2'] ?>" required>
        </div>
    </div>
    <br>
    <button class="btn btn-primary" name="update">Update</button>
    <a href="index.php?tahun=<?= $tahun ?>" class="btn btn-secondary">Kembali</a>
</form>

</div>
</div>
</div>
</body>
</html>