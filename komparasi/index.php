<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }

// ===== TABEL 1: NILAI PAGU DAN REALISASI =====
$q1 = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM komparasi_nilai_pagu ORDER BY tahun ASC");
$years = [];
while($r = mysqli_fetch_assoc($q1)) {
    $years[] = $r['tahun'];
}

$q1 = mysqli_query($koneksi, "SELECT * FROM komparasi_nilai_pagu ORDER BY tahun ASC, uraian ASC");
$data1 = [];
while($r = mysqli_fetch_assoc($q1)) {
    $tahun = $r['tahun'];
    if(!isset($data1[$tahun])) $data1[$tahun] = [];
    if ($r['uraian'] == 'PAGU ANGGARAN') $data1[$tahun]['PAGU'] = $r;
    else if ($r['uraian'] == 'REALISASI') $data1[$tahun]['REALISASI'] = $r;
}

// ===== TABEL 2: INDIKATOR KINERJA =====
$q2 = mysqli_query($koneksi, "SELECT * FROM komparasi_indikator_kinerja ORDER BY tahun_anggaran ASC");
$data2 = [];
while($r = mysqli_fetch_assoc($q2)) $data2[] = $r;

// ===== TABEL 3: NILAI KINERJA =====
$q3 = mysqli_query($koneksi, "SELECT * FROM komparasi_nilai_kinerja ORDER BY tahun_anggaran ASC");
$data3 = [];
while($r = mysqli_fetch_assoc($q3)) $data3[] = $r;

// ===== TABEL 2B: INDIKATOR ASPEK =====
$q2b_years = mysqli_query($koneksi, "SELECT DISTINCT tahun_anggaran FROM indikator_aspek ORDER BY tahun_anggaran ASC");
$years2b = [];
while($r = mysqli_fetch_assoc($q2b_years)) $years2b[] = $r['tahun_anggaran'];

$q2b_aspek = mysqli_query($koneksi, "SELECT aspek FROM indikator_aspek GROUP BY aspek ORDER BY MIN(id) ASC");
$aspek2b = [];
while($r = mysqli_fetch_assoc($q2b_aspek)) $aspek2b[] = $r['aspek'];

$q2b = mysqli_query($koneksi, "SELECT a.* FROM indikator_aspek a JOIN (SELECT aspek, MIN(id) as min_id FROM indikator_aspek GROUP BY aspek) b ON a.aspek = b.aspek ORDER BY b.min_id ASC, a.tahun_anggaran ASC");
$data2b = [];
while($r = mysqli_fetch_assoc($q2b)) {
    $data2b[$r['aspek']][$r['tahun_anggaran']] = $r['nilai'];
}

// ===== TABEL 3B: KINERJA ASPEK =====
$q3b_years = mysqli_query($koneksi, "SELECT DISTINCT tahun_anggaran FROM kinerja_aspek ORDER BY tahun_anggaran ASC");
$years3b = [];
while($r = mysqli_fetch_assoc($q3b_years)) $years3b[] = $r['tahun_anggaran'];

$q3b_aspek = mysqli_query($koneksi, "SELECT aspek FROM kinerja_aspek GROUP BY aspek ORDER BY MIN(id) ASC");
$aspek3b = [];
while($r = mysqli_fetch_assoc($q3b_aspek)) $aspek3b[] = $r['aspek'];

$q3b = mysqli_query($koneksi, "SELECT a.* FROM kinerja_aspek a JOIN (SELECT aspek, MIN(id) as min_id FROM kinerja_aspek GROUP BY aspek) b ON a.aspek = b.aspek ORDER BY b.min_id ASC, a.tahun_anggaran ASC");
$data3b = [];
while($r = mysqli_fetch_assoc($q3b)) {
    $data3b[$r['aspek']][$r['tahun_anggaran']] = $r['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komparasi Pelaksanaan Anggaran</title>
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
        .btn-primary:hover { background-color: #0b9e88ff !important; }
        .table thead th { background-color: #0DBBCB !important; color: white !important; }
        .card { border-left: 4px solid #0DBBCB !important; }
        .center { text-align:center; }
        .right  { text-align:right; padding-right: 15px; }
        .table th, .table td { white-space: nowrap; font-size: 13px; padding: 12px 8px; }
        .total-row { font-weight:bold; background:#f0f0f0; }
        .table-wrapper { overflow-x: auto; }
    </style>
</head>

<body id="page-top">
<div id="wrapper">

    <!-- SIDEBAR -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <li class="nav-item"><a class="nav-link" href="../realisasi_jenis_belanja/index.php"><span>Realisasi Berdasarkan Jenis Belanja</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../penjelasan_belanja_akrual/index.php"><span>Penjelasan Belanja Akrual</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../spm_berjalan/index.php"><span>SPM Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../upaya_percepatan/index.php"><span>RPK/RPD Bulan Berjalan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../permasalahan_dan_rencana_tindaklanjut/index.php"><span>Permasalahan & Rencana Tindak Lanjut</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../email/index.php"><span>Broadcast Instruksi</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pnbp/index.php"><span>Data Realisasi PNBP</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pk/index.php"><span>Perjanjian Kinerja</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="index.php"><span>Komparasi Pelaksanaan Anggaran</span></a></li>
        <hr class="sidebar-divider my-2">
        <li class="nav-item"><a class="nav-link" href="../logout.php"><span>Kembali</span></a></li>
    </ul>

    <!-- CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <h2>Komparasi Pelaksanaan Anggaran</h2>
            <p>Data Perbandingan Pelaksanaan Anggaran BBKK Batam</p>
            <hr>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- ===== TABEL 1: NILAI PAGU DAN REALISASI ANGGARAN ===== -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar"></i> I. NILAI PAGU DAN REALISASI ANGGARAN</h6>
                </div>
                <div class="card-body table-responsive">
                    <div class="mb-3">
                        <a href="pagu/tambah.php" class="btn btn-primary">Tambah Data</a>
                        <a href="pagu/kelola.php" class="btn btn-info">Kelola Data</a>
                    </div>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr style="background-color: #0DBBCB; color: white;">
                                <th class="center" style="width:50px;">No</th>
                                <th rowspan="3" class="center">Uraian</th>
                                <?php foreach($years as $tahun): ?>
                                    <th class="center" style="width:120px;"><?= $tahun ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="center" style="width:50px;">1</td>
                                <td style="width:150px;">PAGU ANGGARAN</td>
                                <?php foreach($years as $tahun): 
                                    $pagu = isset($data1[$tahun]['PAGU']) ? $data1[$tahun]['PAGU']['pagu'] : 0;
                                ?>
                                    <td class="right" style="width:120px;"><?= $pagu > 0 ? nf0($pagu) : '-' ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="center" style="width:50px;">2</td>
                                <td style="width:150px;">REALISASI</td>
                                <?php foreach($years as $tahun): 
                                    $pagu      = isset($data1[$tahun]['REALISASI']) ? $data1[$tahun]['REALISASI']['pagu'] : 0;
                                    $realisasi = isset($data1[$tahun]['REALISASI']) ? $data1[$tahun]['REALISASI']['realisasi'] : 0;
                                    $persen    = ($pagu > 0) ? ($realisasi / $pagu * 100) : 0;
                                ?>
                                    <td class="right" style="width:120px; padding: 0;">
                                        <div style="padding: 12px 8px; border-bottom: 1px solid #dee2e6;"><?= $realisasi > 0 ? nf0($realisasi) : '-' ?></div>
                                        <div style="padding: 12px 8px;"><strong><?= $persen > 0 ? pf($persen) . '%' : '-' ?></strong></div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== TABEL 2: NILAI INDIKATOR KINERJA PELAKSANAAN ANGGARAN ===== -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> II. NILAI INDIKATOR KINERJA PELAKSANAAN ANGGARAN</h6>
                </div>
                <div class="card-body table-responsive">
                    <div class="mb-3">
                        <a href="indikator/aspek/tambah.php" class="btn btn-primary">Tambah Data</a>
                        <a href="indikator/aspek/kelola.php" class="btn btn-info">Kelola Data</a>
                    </div>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th class="center" style="width:40px;">No</th>
                                <th class="center">Aspek</th>
                                <?php foreach($years2b as $thn): ?>
                                    <th class="center" style="width:100px;"><?= (int)$thn ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($aspek2b as $asp): ?>
                            <tr>
                                <td class="center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($asp) ?></td>
                                <?php foreach($years2b as $thn):
                                    $val = isset($data2b[$asp][$thn]) ? $data2b[$asp][$thn] : null;
                                ?>
                                    <td class="right"><?= $val !== null ? pf($val) : '-' ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr>
                    <div class="mb-3">
                        <a href="indikator/tambah.php" class="btn btn-primary">Tambah Data</a>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="center" style="width:60px;">No</th>
                                <th class="center">Tahun Anggaran</th>
                                <th class="center">Capaian</th>
                                <th class="center" style="width:150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($data2 as $d): ?>
                            <tr>
                                <td class="center"><?= $no++ ?></td>
                                <td class="center"><?= (int)$d['tahun_anggaran'] ?></td>
                                <td class="right"><?= pf($d['capaian']) ?>%</td>
                                <td class="center">
                                    <a href="indikator/edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="indikator/hapus.php?id=<?= (int)$d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?');">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== TABEL 3: NILAI KINERJA ANGGARAN ===== -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie"></i> III. NILAI KINERJA ANGGARAN</h6>
                </div>
                <div class="card-body table-responsive">
                    <div class="mb-3">
                        <a href="kinerja/aspek/tambah.php" class="btn btn-primary">Tambah Data</a>
                        <a href="kinerja/aspek/kelola.php" class="btn btn-info">Kelola Data</a>
                    </div>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th class="center" style="width:40px;">No</th>
                                <th class="center">Aspek</th>
                                <?php foreach($years3b as $thn): ?>
                                    <th class="center" style="width:100px;"><?= (int)$thn ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($aspek3b as $asp): ?>
                            <tr>
                                <td class="center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($asp) ?></td>
                                <?php foreach($years3b as $thn):
                                    $val = isset($data3b[$asp][$thn]) ? $data3b[$asp][$thn] : null;
                                ?>
                                    <td class="right"><?= $val !== null ? pf($val) : '-' ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr>
                    <div class="mb-3">
                        <a href="kinerja/tambah.php" class="btn btn-primary">Tambah Data</a>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="center" style="width:60px;">No</th>
                                <th class="center">Tahun Anggaran</th>
                                <th class="center">Capaian</th>
                                <th class="center" style="width:150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($data3 as $d): ?>
                            <tr>
                                <td class="center"><?= $no++ ?></td>
                                <td class="center"><?= (int)$d['tahun_anggaran'] ?></td>
                                <td class="right"><?= pf($d['capaian']) ?>%</td>
                                <td class="center">
                                    <a href="kinerja/edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="kinerja/hapus.php?id=<?= (int)$d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?');">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL KELOLA DATA -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">Kelola Data Pagu & Realisasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="pilihTahun" class="form-label">Pilih Tahun <span class="text-danger">*</span></label>
                    <select id="pilihTahun" class="form-select">
                        <option value="">-- Pilih Tahun --</option>
                        <?php foreach($years as $tahun): ?>
                            <option value="<?= $tahun ?>"><?= $tahun ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="actionButtons" class="d-none">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>PAGU ANGGARAN</strong><br>
                            <a id="editPaguBtn" href="#" class="btn btn-warning btn-sm w-100 mb-2">Edit</a>
                            <a id="hapusPaguBtn" href="#" class="btn btn-danger btn-sm w-100" onclick="return confirm('Hapus PAGU ANGGARAN?');">Hapus</a>
                        </div>
                        <div class="col-6">
                            <strong>REALISASI</strong><br>
                            <a id="editRealisasiBtn" href="#" class="btn btn-warning btn-sm w-100 mb-2">Edit</a>
                            <a id="hapusRealisasiBtn" href="#" class="btn btn-danger btn-sm w-100" onclick="return confirm('Hapus REALISASI?');">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="../asset/vendor/jquery/jquery.min.js"></script>
<script src="../asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('pilihTahun').addEventListener('change', function() {
    const tahun = this.value;
    const actionButtons = document.getElementById('actionButtons');
    if (tahun) {
        document.getElementById('editPaguBtn').href = 'pagu/edit.php?tahun=' + tahun + '&uraian=PAGU%20ANGGARAN';
        document.getElementById('hapusPaguBtn').href = 'pagu/hapus.php?tahun=' + tahun + '&uraian=PAGU%20ANGGARAN';
        document.getElementById('editRealisasiBtn').href = 'pagu/edit.php?tahun=' + tahun + '&uraian=REALISASI';
        document.getElementById('hapusRealisasiBtn').href = 'pagu/hapus.php?tahun=' + tahun + '&uraian=REALISASI';
        actionButtons.classList.remove('d-none');
    } else {
        actionButtons.classList.add('d-none');
    }
});
</script>