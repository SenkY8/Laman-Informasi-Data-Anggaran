<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if($id <= 0) {
    header("Location: index.php");
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM pk_2025_kinerja WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

if(!$data) {
    header("Location: index.php?pesan=error");
    exit;
}

if(isset($_POST['simpan'])) {
    $tahun_baru = (int)$_POST['tahun'];
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $indikator = mysqli_real_escape_string($koneksi, $_POST['indikator']);
    $target = mysqli_real_escape_string($koneksi, $_POST['target']);
    
    function convertToDecimal($value) {
        $value = trim($value);
        $value = str_replace(',', '.', $value);
        return (float)$value;
    }
    
    $jan = isset($_POST['jan']) && $_POST['jan'] !== '' ? convertToDecimal($_POST['jan']) : 0;
    $feb = isset($_POST['feb']) && $_POST['feb'] !== '' ? convertToDecimal($_POST['feb']) : 0;
    $mar = isset($_POST['mar']) && $_POST['mar'] !== '' ? convertToDecimal($_POST['mar']) : 0;
    $apr = isset($_POST['apr']) && $_POST['apr'] !== '' ? convertToDecimal($_POST['apr']) : 0;
    $mei = isset($_POST['mei']) && $_POST['mei'] !== '' ? convertToDecimal($_POST['mei']) : 0;
    $jun = isset($_POST['jun']) && $_POST['jun'] !== '' ? convertToDecimal($_POST['jun']) : 0;
    $jul = isset($_POST['jul']) && $_POST['jul'] !== '' ? convertToDecimal($_POST['jul']) : 0;
    $agu = isset($_POST['agu']) && $_POST['agu'] !== '' ? convertToDecimal($_POST['agu']) : 0;
    $sep = isset($_POST['sep']) && $_POST['sep'] !== '' ? convertToDecimal($_POST['sep']) : 0;
    $okt = isset($_POST['okt']) && $_POST['okt'] !== '' ? convertToDecimal($_POST['okt']) : 0;
    $nov = isset($_POST['nov']) && $_POST['nov'] !== '' ? convertToDecimal($_POST['nov']) : 0;
    $des = isset($_POST['des']) && $_POST['des'] !== '' ? convertToDecimal($_POST['des']) : 0;
    
    $sql = "UPDATE pk_2025_kinerja SET 
            tahun='$tahun_baru',
            jenis='$jenis', 
            deskripsi='$deskripsi', 
            indikator='$indikator', 
            target='$target',
            jan=$jan, feb=$feb, mar=$mar, apr=$apr, mei=$mei, jun=$jun, 
            jul=$jul, agu=$agu, sep=$sep, okt=$okt, nov=$nov, des=$des
            WHERE id='$id'";
    
    if(mysqli_query($koneksi, $sql)) {
        header("Location: index.php?tahun=$tahun_baru&pesan=edit");
        exit;
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

function formatDisplay($value) {
    return str_replace('.', ',', number_format((float)$value, 2, '.', ''));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Perjanjian Kinerja</title>
    <link href="../asset/css/bootstrap.min.css" rel="stylesheet">
    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .bg-gradient-primary {
            background-color: #0DBBCB !important;
            background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
        }

        .btn-primary {
            background-color: #0DBBCB !important;
            border-color: #0DBBCB !important;
        }
        .btn-primary:hover {
            background-color: #0b9e88ff !important;
        }

        .card {
            border-left: 4px solid #0DBBCB !important;
        }

        input[type="text"].decimal-input {
            font-family: 'Courier New', monospace;
        }

        input[type="text"].decimal-input::placeholder {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body id="page-top">

<div id="wrapper">

    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item">
            <a class="nav-link" href="../realisasi_jenis_belanja/index.php">
                <span>Realisasi Berdasarkan Jenis Belanja</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../penjelasan_belanja_akrual/index.php">
                <span>Penjelasan Belanja Akrual</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../spm_berjalan/index.php">
                <span>SPM Berjalan</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <span>Perjanjian Kinerja</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../upaya_percepatan/index.php">
                <span>RPK/RPD Bulan Berjalan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php">
                <span>Permasalahan & Rencana Tindak Lanjut</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../email/index.php">
                <span>Broadcast Instruksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pnbp/index.php">
                <span>Data Realisasi PNBP</span>
            </a>
        </li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item">
            <a class="nav-link" href="../logout.php"><span>Kembali</span></a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit text-primary"></i> Edit Data Perjanjian Kinerja</h1>
                <a href="index.php?tahun=<?= $data['tahun'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-file-alt"></i> Form Edit Data - Tahun <?= $data['tahun'] ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Tahun</label>
                                <input type="number" name="tahun" class="form-control" value="<?= $data['tahun'] ?>" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Jenis</label>
                                <select name="jenis" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Sasaran Strategis" <?php echo ($data['jenis'] == 'Sasaran Strategis') ? 'selected' : ''; ?>>Sasaran Strategis</option>
                                    <option value="Program" <?php echo ($data['jenis'] == 'Program') ? 'selected' : ''; ?>>Program</option>
                                    <option value="Kegiatan" <?php echo ($data['jenis'] == 'Kegiatan') ? 'selected' : ''; ?>>Kegiatan</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Target</label>
                                <input type="text" name="target" class="form-control" value="<?php echo htmlspecialchars($data['target']); ?>" placeholder="contoh: 80%, 90, dst" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Indikator</label>
                            <textarea name="indikator" class="form-control" rows="3" required><?php echo htmlspecialchars($data['indikator']); ?></textarea>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Realisasi Per Bulan <small class="text-muted">(Format: 0,98 atau 0.98)</small></h6>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Januari</label>
                                <input type="text" name="jan" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['jan']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Februari</label>
                                <input type="text" name="feb" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['feb']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Maret</label>
                                <input type="text" name="mar" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['mar']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>April</label>
                                <input type="text" name="apr" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['apr']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Mei</label>
                                <input type="text" name="mei" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['mei']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Juni</label>
                                <input type="text" name="jun" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['jun']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Juli</label>
                                <input type="text" name="jul" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['jul']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Agustus</label>
                                <input type="text" name="agu" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['agu']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>September</label>
                                <input type="text" name="sep" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['sep']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Oktober</label>
                                <input type="text" name="okt" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['okt']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>November</label>
                                <input type="text" name="nov" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['nov']); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Desember</label>
                                <input type="text" name="des" class="form-control decimal-input" placeholder="0,00" pattern="[\d.,]+" inputmode="decimal" value="<?php echo formatDisplay($data['des']); ?>">
                            </div>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-sm-12">
                                <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <a href="index.php?tahun=<?= $data['tahun'] ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="../asset/vendor/jquery/jquery.min.js"></script>
<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.decimal-input').forEach(input => {
    input.addEventListener('blur', function() {
        let value = this.value.trim();
        if (value !== '') {
            let numValue = parseFloat(value.replace(',', '.'));
            if (isNaN(numValue)) {
                alert('Masukkan angka yang valid!');
                this.value = '';
            }
        }
    });
});
</script>

</body>
</html>