<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// ===== FILTER TAHUN =====
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Daftar tahun dari database
$tahun_list_query = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM permasalahan_tindak_lanjut ORDER BY tahun DESC");
$tahun_list = [];
while($row = mysqli_fetch_assoc($tahun_list_query)) {
    $tahun_list[] = $row['tahun'];
}
if(empty($tahun_list)) {
    $tahun_list = [date('Y')];
}

// CEK APAKAH TAHUN YANG DIPILIH ADA DATA
$cek_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM permasalahan_tindak_lanjut WHERE tahun = $tahun"));
if($cek_data['total'] == 0 && !empty($tahun_list)) {
    // Jika tahun yang dipilih kosong, gunakan tahun yang pertama punya data
    $tahun = $tahun_list[0];
}

$q = mysqli_query($koneksi, "SELECT * FROM permasalahan_tindak_lanjut WHERE tahun = $tahun ORDER BY id ASC");
$no = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Permasalahan dan Rencana Tindak Lanjut</title>
  <link href="../asset/css/bootstrap.min.css" rel="stylesheet">
  <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
  <style>
    /* SIDEBAR WARNA */
    .bg-gradient-primary {
        background-color: #0DBBCB !important;
        background-image: linear-gradient(180deg, #0DBBCB 10%, #0DBBCB 100%) !important;
    }

    /* WARNA LINK SIDEBAR */
    .sidebar .nav-item .nav-link {
        color: #ffffff !important;
    }
    .sidebar .nav-item .nav-link:hover {
        background-color: #009AA8 !important;
    }

    /* ACTIVE MENU */
    .sidebar .nav-item.active .nav-link {
        background-color: #0DBBCB !important;
        color: #ffffff !important;
    }

    /* BUTTON UTAMA */
    .btn-primary {
        background-color: #0DBBCB !important;
        border-color: #0DBBCB !important;
    }
    .btn-primary:hover {
        background-color: #0b9e88ff !important;
    }

    /* HEADER TABLE */
    .table thead th {
        background-color: #0DBBCB !important;
        color: white !important;
    }

    /* CARD BORDER */
    .card {
        border-left: 4px solid #0DBBCB !important;
    }
    .center { text-align:center; }
    .right { text-align:right; }
  </style>
</head>
<body id="page-top">
<div id="wrapper">

  <!-- SIDEBAR -->
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
      <li class="nav-item">
          <a class="nav-link" href="../upaya_percepatan/index.php">
              <span>RPK/RPD Bulan Berjalan</span>
          </a>
      </li>
      <li class="nav-item active">
          <a class="nav-link" href="index.php">
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
      <li class="nav-item">
          <a class="nav-link" href="../logout.php"><span>Kembali</span></a>
      </li>
  </ul>
  <!-- END SIDEBAR -->

  <!-- CONTENT WRAPPER -->
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content" class="p-4">

        <h2>Permasalahan dan Rencana Tindak Lanjut</h2>
        <p>Data Permasalahan & Tindak Lanjut BBKK Batam</p>
        <hr>

        <!-- FILTER TAHUN -->
        <div style="margin-bottom: 15px;">
            <label for="tahunSelect" style="font-weight: 600;">Pilih Tahun:</label>
            <select id="tahunSelect" onchange="filterTahun(this.value)" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                <?php foreach($tahun_list as $t): ?>
                    <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>>
                        Tahun <?= $t ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <a href="tambah.php?tahun=<?= $tahun ?>" class="btn btn-primary mb-3">Tambah Data</a>

        <div class="card shadow mb-4">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th class="center" style="width:60px;">No</th>
                    <th class="center">Permasalahan</th>
                    <th class="center">Tindak Lanjut</th>
                    <th class="center" style="width:150px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($q) > 0): ?>
                    <?php while ($d = mysqli_fetch_assoc($q)): ?>
                      <tr>
                        <td class="center"><?= $no++ ?></td>
                        <td><?= nl2br(htmlspecialchars($d['permasalahan'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($d['tindak_lanjut'])) ?></td>
                        <td class="center">
                          <a href="edit.php?id=<?= $d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
                          <a href="hapus.php?id=<?= $d['id'] ?>&tahun=<?= $tahun ?>" class="btn btn-danger btn-sm mb-1"
                             onclick="return confirm('Yakin ingin menghapus data ini?');">
                             Hapus
                          </a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="center">Belum ada data.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

    </div>
  </div>

</div>

<script src="../asset/vendor/jquery/jquery.min.js"></script>
<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../asset/js/sb-admin-2.min.js"></script>

<script>
function filterTahun(tahun) {
    window.location.href = '?tahun=' + tahun;
}
</script>
</body>
</html>