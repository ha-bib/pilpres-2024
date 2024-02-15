<?php
// API endpoint URL
$url = 'https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp.json';
$url2 = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/0.json';
// Get JSON data from the API
$json_data_pilpres = file_get_contents($url);
$json_data_provinsi = file_get_contents($url2);
// Check if data retrieval was successful
if ($json_data_pilpres === FALSE || $json_data_provinsi === FALSE) {
    die('Error: Unable to fetch JSON data from the API');
}
// Decode JSON data
$data_pilpres = json_decode($json_data_pilpres, TRUE);
$data_provinsi = json_decode($json_data_provinsi, TRUE);
// Check if JSON decoding was successful
if ($data_pilpres === NULL || $data_provinsi === NULL) {
    die('Error: Unable to decode JSON data');
}
$kode_anis = 100025;
$kode_prabowo = 100026;
$kode_ganjar = 100027;
foreach ($data_provinsi as $provinsi) {
    $kode = $provinsi['kode'];
    if (isset($data_pilpres['table'][$kode])) {
        $data_pilpres['table'][$kode]['provinsi'] = $provinsi['nama'];
    }
}
$kemenangan = [
    'anis' => 0,
    'prabowo' => 0,
    'ganjar' => 0,
];

foreach ($data_pilpres['table'] as $key => $value) {
    if (isset($value[$kode_anis])) {
        $total = $value[$kode_anis] + $value[$kode_prabowo] + $value[$kode_ganjar];
        $data_pilpres['table'][$key]['presentase_anis'] = number_format($value[$kode_anis] / $total * 100, 0, ',', '.');
        $data_pilpres['table'][$key]['presentase_prabowo'] = number_format($value[$kode_prabowo] / $total * 100, 0, ',', '.');
        $data_pilpres['table'][$key]['presentase_ganjar'] = number_format($value[$kode_ganjar] / $total * 100, 0, ',', '.');
        $data_pilpres['table'][$key]['total'] = number_format($total, 0, ',', '.');

        // ranking the winner
        if ($value[$kode_anis] > $value[$kode_prabowo] && $value[$kode_anis] > $value[$kode_ganjar]) {
            $data_pilpres['table'][$key]['winner'] = 'anis';
            $kemenangan['anis']++;
        }
        if ($value[$kode_prabowo] > $value[$kode_anis] && $value[$kode_prabowo] > $value[$kode_ganjar]) {
            $data_pilpres['table'][$key]['winner'] = 'prabowo';
            $kemenangan['prabowo']++;
        }
        if ($value[$kode_ganjar] > $value[$kode_anis] && $value[$kode_ganjar] > $value[$kode_prabowo]) {
            $data_pilpres['table'][$key]['winner'] = 'ganjar';
            $kemenangan['ganjar']++;
        }
    } else {
        $data_pilpres['table'][$key]['presentase_anis'] = 0;
        $data_pilpres['table'][$key]['presentase_prabowo'] = 0;
        $data_pilpres['table'][$key]['presentase_ganjar'] = 0;
        $data_pilpres['table'][$key]['total'] = 0;
        $data_pilpres['table'][$key]['winner'] = null;
    }
}

$data_pilpres['jumlah_suara'] = $data_pilpres['chart'][$kode_anis] + $data_pilpres['chart'][$kode_prabowo] + $data_pilpres['chart'][$kode_ganjar];
$data_pilpres['presentase'][$kode_anis] = round($data_pilpres['chart'][$kode_anis] / $data_pilpres['jumlah_suara'] * 100, 2);
$data_pilpres['presentase'][$kode_prabowo] = round($data_pilpres['chart'][$kode_prabowo] / $data_pilpres['jumlah_suara'] * 100, 2);
$data_pilpres['presentase'][$kode_ganjar] = round($data_pilpres['chart'][$kode_ganjar] / $data_pilpres['jumlah_suara'] * 100, 2);

?>
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=0.5, shrink-to-fit=no">
<title>KPU - REAL COUNT PILPRES 2024 (<?= $data_pilpres['chart']['persen'] ?>%)</title>
<link rel="icon" href="favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<style>
    body {
        font-family: sans-serif;
    }
</style>

<body>
    <div class=" py-5">
        <main>
            <h2 class="text-center"><b>REAL COUNT PILPRES 2024 (<?= $data_pilpres['chart']['persen'] ?>%)</b></h2>
            <p class="text-center text-secondary mb-2"> Terakhir diperbarui: <?= $data_pilpres['ts'] ?></p>
            <div class="d-flex justify-content-center">
                <canvas id="myChart" style="width:100%;max-width:1000px"></canvas>
            </div>
            <div class="table-responsive mt-5">
                <table class="table table-hover table-bordered fs-12">
                    <thead>
                        <tr>
                            <th colspan="5" class="text-center bg-danger text-white pt-3">
                                <h2>1 PUTARAN : <?= $kemenangan['prabowo'] > 20  && $data_pilpres['presentase'][$kode_prabowo] > 50 ? 'YA' : 'TIDAK' ?></h2>
                            </th>
                        </tr>
                        <tr class="bg-dark text-white">
                            <th width="10" rowspan="2">NO</th>
                            <th rowspan="2">
                                <div style="width: 300px;">WILAYAH</div>
                            </th>
                            <th>ANIES <span class="float-end"> 🏆 <?= $kemenangan['anis'] ?></span></th>
                            <th>PRABOWO <span class="float-end"> 🏆 <?= $kemenangan['prabowo'] ?></span></th>
                            <th>GANJAR <span class="float-end"> 🏆 <?= $kemenangan['ganjar'] ?></span></th>
                        </tr>
                        <tr class="bg-dark text-white">
                            <th class="text-center">
                                <h3><b><?= $data_pilpres['presentase'][$kode_anis] ?>%</b></h3>
                            </th>
                            <th class="text-center">
                                <h3><b><?= $data_pilpres['presentase'][$kode_prabowo] ?>%</b></h3>
                            </th>
                            <th class="text-center">
                                <h3><b><?= $data_pilpres['presentase'][$kode_ganjar] ?>%</b></h3>
                            </th>
                        </tr>
                        <tr class="table-success">
                            <th></th>
                            <th> Total <span class="float-end"><?= number_format($data_pilpres['jumlah_suara'], 0, ',', '.')  ?></span></th>
                            <th class="text-end"><?= number_format($data_pilpres['chart'][$kode_anis], 0, ',', '.') ?></th>
                            <th class="text-end"><?= number_format($data_pilpres['chart'][$kode_prabowo], 0, ',', '.') ?></th>
                            <th class="text-end"><?= number_format($data_pilpres['chart'][$kode_ganjar], 0, ',', '.') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        foreach ($data_pilpres['table'] as $key => $value) : ?>
                            <tr>
                                <th><?= $nomor++ ?></th>
                                <th><?= $value['provinsi'] ?> <span class="float-end"><?= $value['total']  ?></span></th>
                                <td class="<?= $value['winner'] == 'anis' ? 'table-info' : '' ?>">
                                    <small><?= $value['presentase_anis'] ?>%</small> <span class="float-end"><?= number_format($value[$kode_anis] ?? 0, 0, ',', '.') ?></span>
                                </td>
                                <td class="<?= $value['winner'] == 'prabowo' ? 'table-info' : '' ?>">
                                    <small><?= $value['presentase_prabowo'] ?>%</small> <span class="float-end"><?= number_format($value[$kode_prabowo] ?? 0, 0, ',', '.') ?></span>
                                </td>
                                <td class="<?= $value['winner'] == 'ganjar' ? 'table-info' : '' ?>">
                                    <small><?= $value['presentase_ganjar'] ?>%</small> <span class="float-end"><?= number_format($value[$kode_ganjar] ?? 0, 0, ',', '.') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        var xValues = ["<?= $data_pilpres['presentase'][$kode_anis] ?>% ANIES", "<?= $data_pilpres['presentase'][$kode_prabowo] ?>% PRABOWO", "<?= $data_pilpres['presentase'][$kode_ganjar] ?>% GANJAR"];
        var yValues = [<?= $data_pilpres['chart'][$kode_anis] ?>, <?= $data_pilpres['chart'][$kode_prabowo] ?>, <?= $data_pilpres['chart'][$kode_ganjar] ?>];
        var barColors = [
            "#198754",
            "#00d0ff",
            "#b91d47",
        ];

        new Chart("myChart", {
            type: "pie",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
            options: {
                title: {
                    display: true,
                    text: ""
                },
            }
        });
    </script>
</body>

</html>