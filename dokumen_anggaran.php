<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Anggaran - BBKK Batam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body{
            background:#f0f0f0;
            font-family:"Lemon Mil",sans-serif;
            margin:0;
            padding:0;
        }

        /* FULLSCREEN WRAPPER */
        .page-wrapper{
            width:100%;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:0;
        }

        /* CARD FULLSCREEN */
        .card-plain{
            width:95%;
            height:95vh;
            background:white;
            border-radius:12px;
            padding:25px;
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
            display:flex;
            flex-direction:column;
        }

        /* AREA PDF */
        #pdfViewer{
            width:100%;
            height:75vh;
            border:1px solid #ddd;
            border-radius:10px;
            margin-top:15px;
        }

        /* AREA PDF KOSONG */
        .pdf-empty{
            padding:30px;
            margin-top:10px;
            background:#fafafa;
            border:1px dashed #ccc;
            border-radius:8px;
            color:#666;
            text-align:center;
            font-size:13px;
            display:none;
        }

        /* BUTTON */
        .btn-kembali{
            margin-top:15px;
            background:#0DBBCB;
            color:white;
            font-weight:600;
            padding:10px 18px;
            border-radius:8px;
            text-decoration:none;
            width:max-content;
        }
        .btn-kembali:hover{
            background:#0b9e88;
            color:white;
        }

        /* RESPONSIVE MOBILE */
        @media(max-width:768px){
            .card-plain{
                width:98%;
                height:96vh;
                padding:15px;
            }
            #pdfViewer{
                height:60vh;
            }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card-plain">
        <h4 style="color:#007bff;font-weight:700;">Dokumen Anggaran</h4>
        <p style="font-size:13px;color:#444;margin-bottom:20px;">
            Silakan pilih revisi dokumen anggaran yang ingin ditampilkan.
        </p>

        <!-- Dropdown Revisi -->
        <label style="font-size:14px;">Pilih Revisi</label>
        <select id="revisi" class="form-control mb-3" style="max-width:320px;">
            <option value="" selected disabled>-- Pilih Revisi --</option>
            <option value="AWAL">Dokumen Anggaran Awal 2025</option>
            <option value="REV1">Dokumen Anggaran Revisi 1 Tahun 2025</option>
            <option value="REV2">Dokumen Anggaran Revisi 2 Tahun 2025</option>
            <option value="REV3">Dokumen Anggaran Revisi 3 Tahun 2025</option>
            <option value="REV4">Dokumen Anggaran Revisi 4 Tahun 2025</option>
            <option value="REV5">Dokumen Anggaran Revisi 5 Tahun 2025</option>
            <option value="REV6">Dokumen Anggaran Revisi 6 Tahun 2025</option>
            <option value="REV7">Dokumen Anggaran Revisi 7 Tahun 2025</option>
        </select>

        <!-- PDF Area -->
        <iframe id="pdfViewer" style="display:none;"></iframe>
        <div id="pdfEmpty" class="pdf-empty">
            Belum ada revisi yang dipilih. Silakan pilih revisi terlebih dahulu.
        </div>

        <!-- BUTTON KEMBALI -->
        <a href="index.php" class="btn-kembali">
            Kembali ke Beranda
        </a>

    </div>

</div>

<script>
// Mapping kode revisi -> Google Drive preview Dokumen Anggaran
var dokumenPDF = {
    'AWAL': 'https://drive.google.com/file/d/1oiDildBpXx2tcGQqZaLJ31SjSXpYzuQj/preview',
    'REV1': 'https://drive.google.com/file/d/1BksHPRg0oQ-BdX2lUsgpe68nrtGpgneJ/preview',
    'REV2': 'https://drive.google.com/file/d/1w3jty5omfK7PlkRthCmIXaS5kbF07dzw/preview',
    'REV3': 'https://drive.google.com/file/d/1d3HxXyEOavu40ttDMxo7S7pxpO13cHUM/preview',
    'REV4': 'https://drive.google.com/file/d/1QkBrnSKIsPjj2iamK3Pn8C1YX1Zbw0l7/preview',
    'REV5': 'https://drive.google.com/file/d/1JdRjgrwaMPqG2hn5Zx9Z7T9j1Nh_XdDR/preview',
    'REV6': 'https://drive.google.com/file/d/1COzWKdKiDrAoK0wrOIlTD6HEW3KcFM2c/preview',
    'REV7': 'https://drive.google.com/file/d/1d74iOUqiR118x0C4MaBooZWcOHhfwmhJ/preview'
};

var select = document.getElementById("revisi");
var viewer = document.getElementById("pdfViewer");
var empty  = document.getElementById("pdfEmpty");

function loadPDF(){
    var key = select.value;

    if (!key) {
        viewer.style.display = "none";
        empty.style.display  = "block";
        empty.textContent    = "Belum ada revisi yang dipilih. Silakan pilih revisi terlebih dahulu.";
        return;
    }

    var link = dokumenPDF[key];

    if (!link) {
        viewer.style.display = "none";
        empty.style.display  = "block";
        empty.textContent    = "File PDF untuk revisi ini belum tersedia.";
        viewer.src = "";
        return;
    }

    // Tampilkan PDF dari Google Drive (preview)
    viewer.style.display = "block";
    empty.style.display  = "none";
    viewer.src = link + (link.includes("?") ? "&v=" : "?v=") + Date.now();
}

select.addEventListener("change", loadPDF);

// Halaman awal: kosong, hanya pesan
viewer.style.display = "none";
empty.style.display  = "block";
</script>

</body>
</html>
