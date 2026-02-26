<?php
session_start();
include "koneksi.php";

function nf0($n){ return number_format((float)$n,0,',','.'); }
function pf($n){ return number_format((float)$n,2,',','.'); }

// ===== TABEL 1: NILAI PAGU DAN REALISASI =====
$q1 = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM komparasi_nilai_pagu ORDER BY tahun ASC");
$years = [];
while($r = mysqli_fetch_assoc($q1)) $years[] = $r['tahun'];

$q1 = mysqli_query($koneksi, "SELECT * FROM komparasi_nilai_pagu ORDER BY tahun ASC, uraian ASC");
$data1 = [];
$paguData = [];
$realisasiData = [];
while($r = mysqli_fetch_assoc($q1)) {
    $tahun = $r['tahun'];
    if(!isset($data1[$tahun])) $data1[$tahun] = [];
    if ($r['uraian'] == 'PAGU ANGGARAN') {
        $data1[$tahun]['PAGU'] = $r;
        $paguData[$tahun] = (float)$r['pagu'];
    } else if ($r['uraian'] == 'REALISASI') {
        $data1[$tahun]['REALISASI'] = $r;
        $realisasiData[$tahun] = (float)$r['realisasi'];
    }
}

$allYears = array_unique(array_merge(array_keys($paguData), array_keys($realisasiData)));
sort($allYears);
foreach($allYears as $y) {
    if(!isset($paguData[$y])) $paguData[$y] = 0;
    if(!isset($realisasiData[$y])) $realisasiData[$y] = 0;
}

// ===== TABEL 2: INDIKATOR KINERJA (LINE) =====
$q2 = mysqli_query($koneksi, "SELECT * FROM komparasi_indikator_kinerja ORDER BY tahun_anggaran ASC");
$data2 = [];
$indikatorYears = [];
$indikatorCapaian = [];
while($r = mysqli_fetch_assoc($q2)) {
    $data2[] = $r;
    $indikatorYears[] = (int)$r['tahun_anggaran'];
    $indikatorCapaian[] = (float)$r['capaian'];
}

// ===== INDIKATOR ASPEK (BAR) =====
$q2b_years = mysqli_query($koneksi, "SELECT DISTINCT tahun_anggaran FROM indikator_aspek ORDER BY tahun_anggaran ASC");
$indikatorAspekYears = [];
while($r = mysqli_fetch_assoc($q2b_years)) $indikatorAspekYears[] = (int)$r['tahun_anggaran'];

$q2b_aspek = mysqli_query($koneksi, "SELECT aspek FROM indikator_aspek GROUP BY aspek ORDER BY MIN(id) ASC");
$indikatorAspekList = [];
while($r = mysqli_fetch_assoc($q2b_aspek)) $indikatorAspekList[] = $r['aspek'];

$q2b = mysqli_query($koneksi, "SELECT a.* FROM indikator_aspek a JOIN (SELECT aspek, MIN(id) as min_id FROM indikator_aspek GROUP BY aspek) b ON a.aspek = b.aspek ORDER BY b.min_id ASC, a.tahun_anggaran ASC");
$indikatorAspekData = [];
while($r = mysqli_fetch_assoc($q2b)) {
    $indikatorAspekData[$r['aspek']][$r['tahun_anggaran']] = (float)$r['nilai'];
}

$indikatorAspekDatasets = [];
$barColors2 = ['rgba(13,187,203,0.6)', 'rgba(0,123,255,0.6)', 'rgba(255,159,64,0.6)', 'rgba(153,102,255,0.6)', 'rgba(255,99,132,0.6)'];
$barBorders2 = ['#0DBBCB', '#007bff', '#ff9f40', '#9966ff', '#ff6384'];
foreach($indikatorAspekList as $i => $asp) {
    $values = [];
    foreach($indikatorAspekYears as $thn) {
        $values[] = isset($indikatorAspekData[$asp][$thn]) ? $indikatorAspekData[$asp][$thn] : null;
    }
    $indikatorAspekDatasets[] = [
        'type'  => 'bar',
        'order' => 2,
        'label' => $asp,
        'data'  => $values,
        'backgroundColor' => $barColors2[$i % count($barColors2)],
        'borderColor'     => $barBorders2[$i % count($barBorders2)],
        'borderWidth'     => 1.5,
        'barPercentage'   => 0.5,
        'categoryPercentage' => 0.7,
    ];
}

// ===== TABEL 3: NILAI KINERJA (LINE) =====
$q3 = mysqli_query($koneksi, "SELECT * FROM komparasi_nilai_kinerja ORDER BY tahun_anggaran ASC");
$data3 = [];
$kinerjaYears = [];
$kinerjaCapaian = [];
while($r = mysqli_fetch_assoc($q3)) {
    $data3[] = $r;
    $kinerjaYears[] = (int)$r['tahun_anggaran'];
    $kinerjaCapaian[] = (float)$r['capaian'];
}

// ===== KINERJA ASPEK (BAR) =====
$q3b_years = mysqli_query($koneksi, "SELECT DISTINCT tahun_anggaran FROM kinerja_aspek ORDER BY tahun_anggaran ASC");
$kinerjaAspekYears = [];
while($r = mysqli_fetch_assoc($q3b_years)) $kinerjaAspekYears[] = (int)$r['tahun_anggaran'];

$q3b_aspek = mysqli_query($koneksi, "SELECT aspek FROM kinerja_aspek GROUP BY aspek ORDER BY MIN(id) ASC");
$kinerjaAspekList = [];
while($r = mysqli_fetch_assoc($q3b_aspek)) $kinerjaAspekList[] = $r['aspek'];

$q3b = mysqli_query($koneksi, "SELECT a.* FROM kinerja_aspek a JOIN (SELECT aspek, MIN(id) as min_id FROM kinerja_aspek GROUP BY aspek) b ON a.aspek = b.aspek ORDER BY b.min_id ASC, a.tahun_anggaran ASC");
$kinerjaAspekData = [];
while($r = mysqli_fetch_assoc($q3b)) {
    $kinerjaAspekData[$r['aspek']][$r['tahun_anggaran']] = (float)$r['nilai'];
}

$kinerjaAspekDatasets = [];
$barColors3 = ['rgba(255,99,132,0.6)', 'rgba(255,205,86,0.6)', 'rgba(54,162,235,0.6)', 'rgba(255,159,64,0.6)', 'rgba(201,203,207,0.6)'];
$barBorders3 = ['#ff6384', '#ffcd56', '#36a2eb', '#ff9f40', '#c9cbcf'];
foreach($kinerjaAspekList as $i => $asp) {
    $values = [];
    foreach($kinerjaAspekYears as $thn) {
        $values[] = isset($kinerjaAspekData[$asp][$thn]) ? $kinerjaAspekData[$asp][$thn] : null;
    }
    $kinerjaAspekDatasets[] = [
        'type'  => 'bar',
        'order' => 2,
        'label' => $asp,
        'data'  => $values,
        'backgroundColor' => $barColors3[$i % count($barColors3)],
        'borderColor'     => $barBorders3[$i % count($barBorders3)],
        'borderWidth'     => 1.5,
        'barPercentage'   => 0.5,
        'categoryPercentage' => 0.7,
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance</title>
    <link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="asset/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
* { box-sizing: border-box; font-family: "Lemon Mil", sans-serif; }
body { background: #f8f9fa; color: #222; }
.btn-primary { background-color: #0DBBCB !important; border-color: #0DBBCB !important; transition: all 0.3s ease; }
.btn-primary:hover { background-color: #00A3B5 !important; transform: translateY(-2px); }
.card { border: none; border-left: 4px solid #0DBBCB !important; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; }
.card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.12); transform: translateY(-2px); }
.card-header { background: #f5f5f5; border-bottom: 2px solid #0DBBCB; padding: 15px 20px !important; }
.card-header h6 { color: #0DBBCB; font-weight: 700; margin: 0; font-size: 14px; }
.card-body { padding: 20px; }
.chart-wrapper { width: 100%; height: 400px; position: relative; }
.btn-back-mobile { display: inline-block !important; }

@media (max-width: 991.98px) {
    .chart-wrapper { height: 340px; }
    .card-header h6 { font-size: 13px; }
}
@media (max-width: 767.98px) {
    body { background: #fff; }
    .chart-wrapper { height: 300px; }
    .card { border-radius: 10px; margin-bottom: 14px; }
    .card-body { padding: 12px; }
    .card-header { padding: 10px 12px !important; }
    .card-header h6 { font-size: 12px; }
    #content { padding: 12px !important; }
    h4 { font-size: 17px !important; }
    .btn-back-mobile { display: none !important; }
}
@media (max-width: 575.98px) {
    .chart-wrapper { height: 260px; }
    .card-body { padding: 10px; }
    .card-header { padding: 8px 10px !important; }
    .card-header h6 { font-size: 11px; }
    #content { padding: 10px !important; }
    h4 { font-size: 15px !important; }
}
@media (max-width: 480px) {
    .chart-wrapper { height: 230px; }
    .card-body { padding: 8px; }
    .card-header { padding: 7px 8px !important; }
    .card-header h6 { font-size: 10px; }
    #content { padding: 8px !important; }
    h4 { font-size: 14px !important; }
}
@media (max-width: 380px) {
    .chart-wrapper { height: 200px; }
    .card-body { padding: 6px; }
    .card-header { padding: 6px !important; }
    .card-header h6 { font-size: 9px; }
    #content { padding: 6px !important; }
    h4 { font-size: 13px !important; }
}
    </style>
</head>

<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:10px;">
                <h4 style="color:#007bff;font-weight:700;">Performa Kinerja Anggaran</h4>
                <a href="index.php" class="btn btn-sm btn-primary btn-back-mobile">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <hr>

            <!-- GRAFIK 1 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> I. Nilai Pagu dan Realisasi Anggaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper"><canvas id="chartPaguRealisasi"></canvas></div>
                </div>
            </div>

            <!-- GRAFIK 2 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> II. Nilai Indikator Kinerja Pelaksanaan Anggaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper"><canvas id="chartIndikator"></canvas></div>
                </div>
            </div>

            <!-- GRAFIK 3 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> III. Nilai Kinerja Anggaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper"><canvas id="chartKinerja"></canvas></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="asset/vendor/jquery/jquery.min.js"></script>
<script src="asset/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="asset/chart.js"></script>

<script>
function formatNumberID(v){ v = Number(v)||0; return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g,"."); }

function getFontSize(){
    var w = window.innerWidth;
    if(w<=380) return 7;
    if(w<=480) return 8;
    if(w<=575) return 9;
    if(w<=767) return 10;
    return 11;
}

// Plugin: tampilkan nilai di atas setiap bar, dan % di atas titik line
var barLabelPlugin = {
    afterDatasetsDraw: function(chart){
        var ctx = chart.ctx;
        var fs = getFontSize();
        chart.data.datasets.forEach(function(dataset, i){
            var meta = chart.getDatasetMeta(i);
            if(meta.hidden) return;

            if(dataset.type === 'bar' || (dataset.type !== 'line' && chart.config.type === 'bar')){
                // Label di atas bar
                if(dataset.type === 'line') return;
                meta.data.forEach(function(el, idx){
                    var val = dataset.data[idx];
                    if(val === null || val === undefined || val === 0) return;
                    ctx.save();
                    ctx.fillStyle = '#222';
                    ctx.font = 'bold ' + fs + 'px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(parseFloat(val).toFixed(2), el._model.x, el._model.y - 3);
                    ctx.restore();
                });
            }
        });

        // Label % di atas titik line (dataset terakhir)
        var lastIdx = chart.data.datasets.length - 1;
        var lastDs = chart.data.datasets[lastIdx];
        if(lastDs && lastDs.type === 'line'){
            var meta = chart.getDatasetMeta(lastIdx);
            meta.data.forEach(function(el, idx){
                var val = lastDs.data[idx];
                if(val === null || val === undefined) return;
                ctx.save();
                ctx.fillStyle = '#333';
                ctx.font = 'bold ' + fs + 'px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillText(parseFloat(val).toFixed(2) + '%', el._model.x, el._model.y - 8);
                ctx.restore();
            });
        }
    }
};

// ===== CHART 1: TIDAK DIUBAH =====
var labels1 = <?= json_encode($allYears) ?>;
var dataPagu = <?= json_encode(array_values(array_replace(array_flip($allYears), $paguData))) ?>;
var dataRealisasi = <?= json_encode(array_values(array_replace(array_flip($allYears), $realisasiData))) ?>;

var ctx1 = document.getElementById('chartPaguRealisasi').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: labels1,
        datasets: [
            { type:'bar', label:'Pagu Anggaran', data:dataPagu, backgroundColor:'rgba(13,187,203,0.55)', borderColor:'#0DBBCB', borderWidth:1.5, order:2, barPercentage:0.5, categoryPercentage:0.6 },
            { type:'bar', label:'Realisasi', data:dataRealisasi, backgroundColor:'rgba(0,123,255,0.55)', borderColor:'#007bff', borderWidth:1.5, order:2, barPercentage:0.5, categoryPercentage:0.6 },
            { type:'line', label:'Persentase Realisasi', data:dataPagu.map(function(p,i){ return p>0?(dataRealisasi[i]/p*100):0; }), fill:true, backgroundColor:'rgba(75,192,75,0.1)', borderColor:'#4bc04b', pointBackgroundColor:'#4bc04b', borderWidth:2.5, pointRadius:6, tension:0.2, yAxisID:'y1', order:1 }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        layout:{ padding:{top:20,bottom:5,left:5,right:5} },
        legend:{ display:true, position:'top', labels:{fontSize:11} },
        tooltips:{ callbacks:{ label:function(ti,data){ var ds=data.datasets[ti.datasetIndex]; var val=ds.data[ti.index]; if(ti.datasetIndex===2) return ds.label+": "+val.toFixed(2)+"%"; return ds.label+": "+formatNumberID(val); } } },
        scales:{
            xAxes:[{stacked:false}],
            yAxes:[
                { id:'y', type:'linear', position:'left', ticks:{ beginAtZero:true, callback:function(v){ return formatNumberID(v); } }, max:Math.max(...dataPagu,...dataRealisasi)*1.25 },
                { id:'y1', type:'linear', position:'right', ticks:{ beginAtZero:true, max:180, callback:function(v){ return v+'%'; } } }
            ]
        }
    },
    plugins:[{ afterDatasetsDraw:function(chart){ var ctx=chart.ctx; chart.data.datasets.forEach(function(ds,i){ if(i!==2)return; var meta=chart.getDatasetMeta(i); meta.data.forEach(function(el,idx){ ctx.fillStyle='#333'; ctx.font='bold 11px Arial'; ctx.textAlign='center'; ctx.textBaseline='bottom'; ctx.fillText(ds.data[idx].toFixed(2)+'%',el._model.x,el._model.y-8); }); }); } }]
});

// ===== CHART 2 =====
var labels2 = <?= json_encode($indikatorAspekYears) ?>;
var datasetsIndikator = <?= json_encode($indikatorAspekDatasets) ?>;
var indikatorLineYears = <?= json_encode($indikatorYears) ?>;
var indikatorLineData  = <?= json_encode($indikatorCapaian) ?>;
var lineData2 = labels2.map(function(thn){ var idx=indikatorLineYears.indexOf(thn); return idx!==-1?indikatorLineData[idx]:null; });
datasetsIndikator.push({ type:'line', order:1, label:'Capaian (%)', data:lineData2, fill:true, backgroundColor:'rgba(245,149,53,0.1)', borderColor:'#f59535', pointBackgroundColor:'#f59535', pointRadius:6, borderWidth:2.5, tension:0.2, yAxisID:'y1' });

var ctx2 = document.getElementById('chartIndikator').getContext('2d');
new Chart(ctx2, {
    type:'bar',
    data:{ labels:labels2, datasets:datasetsIndikator },
    options:{
        responsive:true, maintainAspectRatio:false,
        layout:{ padding:{top:28,bottom:5,left:5,right:5} },
        legend:{ display:true, position:'top', labels:{ fontSize:10, boxWidth:12 } },
        tooltips:{ callbacks:{ label:function(ti,data){ var ds=data.datasets[ti.datasetIndex]; var val=ds.data[ti.index]; return ds.label+": "+(val!==null?parseFloat(val).toFixed(2):'-'); } } },
        scales:{
            xAxes:[{ stacked:false, ticks:{ fontSize:10 } }],
            yAxes:[
                { id:'y', type:'linear', position:'left', ticks:{ beginAtZero:true, max:120, fontSize:10 } },
                { id:'y1', type:'linear', position:'right', ticks:{ beginAtZero:true, max:180, fontSize:10, callback:function(v){ return v+'%'; } } }
            ]
        }
    },
    plugins:[barLabelPlugin]
});

// ===== CHART 3 =====
var labels3 = <?= json_encode($kinerjaAspekYears) ?>;
var datasetsKinerja = <?= json_encode($kinerjaAspekDatasets) ?>;
var kinerjaLineYears = <?= json_encode($kinerjaYears) ?>;
var kinerjaLineData  = <?= json_encode($kinerjaCapaian) ?>;
var lineData3 = labels3.map(function(thn){ var idx=kinerjaLineYears.indexOf(thn); return idx!==-1?kinerjaLineData[idx]:null; });
datasetsKinerja.push({ type:'line', order:1, label:'Capaian (%)', data:lineData3, fill:true, backgroundColor:'rgba(9,253,21,0.1)', borderColor:'#00f3ae', pointBackgroundColor:'#00f3ae', pointRadius:6, borderWidth:2.5, tension:0.2, yAxisID:'y1' });

var ctx3 = document.getElementById('chartKinerja').getContext('2d');
new Chart(ctx3, {
    type:'bar',
    data:{ labels:labels3, datasets:datasetsKinerja },
    options:{
        responsive:true, maintainAspectRatio:false,
        layout:{ padding:{top:28,bottom:5,left:5,right:5} },
        legend:{ display:true, position:'top', labels:{ fontSize:10, boxWidth:12 } },
        tooltips:{ callbacks:{ label:function(ti,data){ var ds=data.datasets[ti.datasetIndex]; var val=ds.data[ti.index]; return ds.label+": "+(val!==null?parseFloat(val).toFixed(2):'-'); } } },
        scales:{
            xAxes:[{ stacked:false, ticks:{ fontSize:10 } }],
            yAxes:[
                { id:'y', type:'linear', position:'left', ticks:{ beginAtZero:true, max:120, fontSize:10 } },
                { id:'y1', type:'linear', position:'right', ticks:{ beginAtZero:true, max:180, fontSize:10, callback:function(v){ return v+'%'; } } }
            ]
        }
    },
    plugins:[barLabelPlugin]
});
</script>

</body>
</html>