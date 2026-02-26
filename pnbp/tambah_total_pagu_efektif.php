<?php
ob_start();
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) { 
    header("Location: ../login.php"); 
    exit; 
}
include "../koneksi.php";
ob_end_clean();

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])){
    $input = $_POST['total_pagu_efektif'];
    $clean = str_replace(['.', ',', ' '], '', $input);
    
    if(!is_numeric($clean)){
        $error = "❌ Input harus berupa angka!";
    } else {
        $nilai = (int)$clean;
        
        if($nilai <= 0){
            $error = "❌ Nilai harus lebih dari 0";
        } else {
            // Hapus data lama terlebih dahulu
            mysqli_query($koneksi, "DELETE FROM pnbp_total_pagu_efektif WHERE tahun = $tahun AND id = 1");
            
            // Insert data baru
            $query = "INSERT INTO pnbp_total_pagu_efektif (tahun, id, total_pagu_efektif) VALUES ($tahun, 1, $nilai)";
            
            if(mysqli_query($koneksi, $query)){
                $success = "✅ Data berhasil disimpan! Nilai: " . number_format($nilai, 0, ',', '.');
            } else {
                $error = "❌ Gagal: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Total Pagu Efektif</title>
  <link href="../asset/css/bootstrap.min.css" rel="stylesheet">
  <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
  <style>
    .bg-gradient-primary {
        background-color: #0DBBCB !important;
        background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
    }
    .sidebar .nav-item .nav-link { color: #ffffff !important; }
    .sidebar .nav-item .nav-link:hover { background-color: #009AA8 !important; }
    .sidebar .nav-item.active .nav-link { background-color: #0DBBCB !important; color: #ffffff !important; }
    .btn-primary { background-color: #0DBBCB !important; border-color: #0DBBCB !important; }
    .btn-primary:hover { background-color: #0b9e88ff !important; border-color: #0b9e88ff !important; }
    .card { border-left: 4px solid #0DBBCB !important; }
    body { background-color: #f8f9fa; }
    .page-heading { margin-bottom: 1.5rem; }
    .page-heading h2 { color: #5a5c69; font-weight: 700; font-size: 1.75rem; margin-bottom: 0.25rem; }
    .page-heading p { color: #858796; margin-bottom: 0; }
    .form-card { max-width: 700px; margin: 0 auto; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { font-weight: 600; color: #5a5c69; margin-bottom: 0.5rem; font-size: 0.9rem; display: block; }
    .form-control { border: 1px solid #d1d3e2; border-radius: 0.35rem; padding: 0.75rem 1rem; font-size: 0.95rem; color: #6e707e; transition: all 0.15s ease-in-out; }
    .form-control:focus { border-color: #0DBBCB; box-shadow: 0 0 0 0.2rem rgba(13, 187, 203, 0.25); color: #6e707e; }
    .form-control::placeholder { color: #d1d3e2; }
    .form-text { display: block; margin-top: 0.5rem; font-size: 0.85rem; color: #858796; }
    .btn-group-actions { display: flex; gap: 10px; margin-top: 1.5rem; }
    .btn { padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; }
    .btn-secondary { background-color: #6c757d; border-color: #6c757d; color: #fff; }
    .btn-secondary:hover { background-color: #5a6268; border-color: #545b62; color: #fff; }
    .card { border-radius: 0.35rem; border: 1px solid #e3e6f0; }
    .card-body { padding: 2rem; }
  </style>
</head>
<body id="page-top">

<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item"><a class="nav-link" href="../realisasi_jenis_belanja/index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../spm_berjalan/index.php"><span>SPM Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php"><span>Permasalahan & Rencana Tindak Lanjut</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../email/index.php"><span>Broadcast Instruksi</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="index.php"><span>Data Realisasi PNBP</span></a></li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item"><a class="nav-link" href="../logout.php"><span>Kembali</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

          <div class="page-heading">
            <h2>Tambah Total Pagu Efektif - Tahun <?= $tahun ?></h2>
            <p>Masukkan nilai total pagu efektif untuk tahun anggaran <?= $tahun ?></p>
          </div>

          <hr>

          <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
              <?= htmlspecialchars($error) ?>
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
          <?php endif; ?>

          <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
              <?= htmlspecialchars($success) ?>
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
          <?php endif; ?>

          <div class="form-card">
            <div class="card shadow">
              <div class="card-body">
                
                <form method="POST" action="">
                  
                  <div class="form-group">
                    <label for="total_pagu_efektif">Total Pagu Efektif - T.A. <?= $tahun ?></label>
                    <input 
                      type="text" 
                      name="total_pagu_efektif" 
                      id="total_pagu_efektif"
                      class="form-control" 
                      required 
                      placeholder="Contoh: 100.000.000"
                      autocomplete="off">
                    <small class="form-text">Masukkan angka tanpa simbol "Rp". Anda dapat menggunakan titik sebagai pemisah ribuan.</small>
                  </div>

                  <div class="btn-group-actions">
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                    <a href="index.php?tahun=<?= $tahun ?>" class="btn btn-secondary">Batal</a>
                  </div>

                </form>

              </div>
            </div>
          </div>

        </div>
    </div>
</div>

<script src="../asset/js/jquery.min.js"></script>
<script src="../asset/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('total_pagu_efektif').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    e.target.value = value;
  });
</script>

</body>
</html>