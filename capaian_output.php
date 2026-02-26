<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Capaian Output per Bulan - BBKK Batam</title>
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

        <h4 style="color:#007bff;font-weight:700;">Capaian Output</h4>
        <p style="font-size:13px;color:#444;margin-bottom:20px;">
            Silakan pilih bulan untuk menampilkan file PDF Capaian Output.
        </p>

        <!-- Dropdown Bulan -->
        <label style="font-size:14px;">Pilih Bulan</label>
        <select id="bulan" class="form-control mb-3" style="max-width:260px;">
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

        <!-- PDF Area -->
        <iframe id="pdfViewer" style="display:none;"></iframe>
        <div id="pdfEmpty" class="pdf-empty">
            Belum ada bulan yang dipilih. Silakan pilih bulan terlebih dahulu.
        </div>

        <!-- BUTTON KEMBALI -->
        <a href="index.php" class="btn-kembali">
            Kembali ke Beranda
        </a>

    </div>

</div>

<script>
// Mapping nama bulan -> Google Drive preview Capaian Output
var outputPDF = {
    'JANUARI'  : 'https://drive.google.com/file/d/1Jmknhwt_IDeFCEIvSUzyH-qSaFfObWkJ/preview',
    'FEBRUARI' : 'https://drive.google.com/file/d/1iDNdPBMk8OJL6PXmwhQCwG714EGXHJzc/preview',
    'MARET'    : 'https://drive.google.com/file/d/1iI8XNdrY90uBOUP_SwtRA1qdmC7RgX1I/preview',
    'APRIL'    : 'https://drive.google.com/file/d/1vahjy3osl34BNKCdiGoGjb_fg2IseiR2/preview',
    'MEI'      : 'https://drive.google.com/file/d/1xl0En1bk6IhHm-JMOpSO-dW3YlFfoKw4/preview',
    'JUNI'     : 'https://drive.google.com/file/d/1LUQ1FUUOyOM8l_oQ3M9cWKEaKVf4LA3f/preview',
    'JULI'     : 'https://drive.google.com/file/d/1ZA5mThOvwi2NKOlXBFBPuWTukukO-QBP/preview',
    'AGUSTUS'  : 'https://drive.google.com/file/d/12prvlHcvXY3im5cF6Y0rCOKs_4_meKG8/preview',
    'SEPTEMBER': 'https://drive.google.com/file/d/1sjEFc-6KADuuMjj24w4e81xUe2TIoECe/preview',
    'OKTOBER'  : 'https://drive.google.com/file/d/1IPwRgO7WI87gkQc0azYr7qaLCpvOxkr7/preview',
    'NOVEMBER' : 'https://drive.google.com/file/d/16_hMC_1kKdMYrYlZV2_cUvTryvZ8Xxhx/preview',
    'DESEMBER' : 'https://drive.google.com/file/d/1O8uPUk6EghcjtTcAfPXzCDIYLdlIsiEd/preview'
};

var select = document.getElementById("bulan");
var viewer = document.getElementById("pdfViewer");
var empty  = document.getElementById("pdfEmpty");

function loadPDF(){
    var bulan = select.value;

    if (!bulan) {
        viewer.style.display = "none";
        empty.style.display  = "block";
        empty.textContent    = "Belum ada bulan yang dipilih. Silakan pilih bulan terlebih dahulu.";
        return;
    }

    var link = outputPDF[bulan];

    if (!link) {
        viewer.style.display = "none";
        empty.style.display  = "block";
        empty.textContent    = "File PDF untuk bulan ini belum tersedia.";
        viewer.src = "";
        return;
    }

    // tampilkan PDF dari Google Drive (seperti ikpa.php)
    viewer.style.display = "block";
    empty.style.display  = "none";
    viewer.src = link + (link.includes("?") ? "&v=" : "?v=") + Date.now();
}

select.addEventListener("change", loadPDF);

// Tampilan awal
viewer.style.display = "none";
empty.style.display  = "block";
</script>

</body>
</html>
