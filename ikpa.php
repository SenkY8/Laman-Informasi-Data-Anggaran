<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nilai IKPA per Bulan - BBKK Batam</title>
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

        <h4 style="color:#007bff;font-weight:700;">Nilai IKPA</h4>
        <p style="font-size:13px;color:#444;margin-bottom:10px;">
            Silakan pilih bulan untuk menampilkan file PDF Nilai IKPA.
        </p>

        <!-- DROPDOWN -->
        <div style="max-width:260px;">
            <label>Pilih Bulan</label>
            <select id="bulan" class="form-control mb-2">
                <option value="" selected disabled>-- Pilih Bulan --</option>
                <option value="JANUARI">JANUARI</option>
                <option value="FEBRUARI">FEBRUARI</option>
                <option value="MARET">MARET</option>
                <option value="APRIL">APRIL</option>
                <option value="MEI">MEI</option>
                <option value="JUNI">JUNI</option>
                <option value="JULI">JULI</option>
                <option value="AGUSTUS">AGUSTUS</option>
                <option value="SEPTEMBER">SEPTEMBER</option>
                <option value="OKTOBER">OKTOBER</option>
                <option value="NOVEMBER">NOVEMBER</option>
                <option value="DESEMBER">DESEMBER</option>
            </select>
        </div>

        <!-- PDF -->
        <iframe id="pdfViewer" style="display:none;"></iframe>
        <div id="pdfEmpty" class="pdf-empty">File PDF belum tersedia.</div>

        <a href="index.php" class="btn-kembali">Kembali ke Beranda</a>

    </div>

</div>

<script>
var ikpaPDF = {
    'JANUARI'  : 'https://drive.google.com/file/d/1-4TimKnjiBr4yDA8yyvENEaMyv7UaSwI/preview',
    'FEBRUARI' : 'https://drive.google.com/file/d/1AOhGzXmudW6i2M1B4wDe8xvwMomp-rnG/preview',
    'MARET'    : 'https://drive.google.com/file/d/1RV5qdjktOlReDkUXIAgXrL3T-rH0L9VR/preview',
    'APRIL'    : 'https://drive.google.com/file/d/1uyt1MZ8c-dzx62FGGD1HA9yUr0gj5Dzf/preview',
    'MEI'      : 'https://drive.google.com/file/d/1rbbbgsycNca8rF0OP7sgQQUGJU1JlTJZ/preview',
    'JUNI'     : 'https://drive.google.com/file/d/1nI8hREqTfkVDDlAc9Jkl70MLEo2OVLIB/preview',
    'JULI'     : 'https://drive.google.com/file/d/1cbiiMmh4hXlB-v8gWC71lFAT-vcbiKsP/preview',
    'AGUSTUS'  : 'https://drive.google.com/file/d/1mw55NJSmQ4mdus-Zf-_wWfSbWpWmMn9F/preview',
    'SEPTEMBER': 'https://drive.google.com/file/d/1-d4l8-F9AKlKF3aiEqb0mWbmwWE9wWRw/preview',
    'OKTOBER'  : 'https://drive.google.com/file/d/1oDbYlE--yqGaZHQ9avUj1TMY4oDC5ToR/preview',
    'NOVEMBER' : 'https://drive.google.com/file/d/11rYKGklN7gqbgpv1Xnvdg3Ln5m01AbX1/preview',
    'DESEMBER' : 'https://drive.google.com/file/d/1NRgAM9vr4kRS8a7KRWl-qivqGWJGNqAg/preview'
};

var select = document.getElementById("bulan");
var iframe = document.getElementById("pdfViewer");
var empty  = document.getElementById("pdfEmpty");

function loadPDF(){
    var bulan = select.value;

    if (!bulan) {
        iframe.style.display = "none";
        empty.style.display  = "none";
        return;
    }

    var link = ikpaPDF[bulan];

    if (!link) {
        iframe.style.display = "none";
        empty.style.display  = "block";
        iframe.src = "";
        return;
    }

    iframe.style.display = "block";
    empty.style.display  = "none";

    // Tambahkan timestamp agar PDF terbaru muncul
    iframe.src = link + (link.includes("?") ? "&v=" : "?v=") + Date.now();
}

select.addEventListener("change", loadPDF);
</script>

</body>
</html>
