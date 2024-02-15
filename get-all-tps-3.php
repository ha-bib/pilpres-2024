<?php
$url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/0.json';
$json_data_wilayah = file_get_contents($url);
// Check if data retrieval was successful
if ($json_data_wilayah === FALSE) {
    die('Error: Unable to fetch JSON data from the API');
}
$data_provinsi = json_decode($json_data_wilayah, TRUE);
// Check if JSON decoding was successful
if ($data_provinsi === NULL) {
    die('Error: Unable to decode JSON data');
}
// echo "<pre>";
// print_r($data_provinsi);


// create folder 'data-tps' if folder not exist
if (!is_dir('data-tps')) mkdir('data-tps', 0777);

foreach ($data_provinsi as $key => $provinsi) {
    if ($key < 10 || $key > 14) continue; //3

    $url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/' . $provinsi['kode'] . '.json';
    $data_kabupaten = json_decode(file_get_contents($url), TRUE);
    $folder_provinsi = 'data-tps/' . $provinsi['kode'];
    if (!is_dir($folder_provinsi)) mkdir($folder_provinsi, 0777); // folder provinsi
    echo  '-' . $provinsi['nama'] . ' ' . count($data_kabupaten) . ' kabupaten' . PHP_EOL;

    foreach ($data_kabupaten as $kabupaten) {
        $url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/' . $provinsi['kode'] . '/' . $kabupaten['kode'] . '.json';
        $data_kecamatan = json_decode(file_get_contents($url), TRUE);
        $folder_kabupaten = 'data-tps/' . $provinsi['kode'] . '/' . $kabupaten['kode'];
        if (!is_dir($folder_kabupaten)) mkdir($folder_kabupaten, 0777); // folder kabupaten
        echo  '--' . $kabupaten['nama'] . '  ' . count($data_kecamatan) . ' kecamatan' . PHP_EOL;


        if (file_exists($folder_kabupaten . '/tps.json')) {
            echo  '--' . $kabupaten['nama'] . '  sudah dimuat' . PHP_EOL;
            continue;
        }

        $tps_kabupaten_kota = [];
        $jumlah_kecamatan = count($data_kecamatan);
        foreach ($data_kecamatan as $kecamatan) {
            echo  '---' . $kabupaten['nama'] . ' ' . ($jumlah_kecamatan--)  . PHP_EOL;
            $url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/' . $provinsi['kode'] . '/' . $kabupaten['kode'] . '/' . $kecamatan['kode'] . '.json';
            $data_desa = json_decode(file_get_contents($url), TRUE);

            foreach ($data_desa as $desa) {
                $url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/' . $provinsi['kode'] . '/' . $kabupaten['kode'] . '/' . $kecamatan['kode'] . '/' . $desa['kode'] . '.json';
                $data_tps = json_decode(file_get_contents($url), TRUE);
                foreach ($data_tps as $tps) {
                    $tps_kabupaten_kota[] = [
                        'provinsi' => $provinsi['kode'],
                        'kabupaten' => $kabupaten['kode'],
                        'kecamatan' => $kecamatan['kode'],
                        'desa' => $desa['kode'],
                        'tps' => $tps['kode'],
                        'url' => $provinsi['kode'] . '/' . $kabupaten['kode'] . '/' . $kecamatan['kode'] . '/' . $desa['kode'] . '/' . $tps['kode'] . '.json',
                        'nama' => $tps['nama'] . ', ' . $desa['nama'] . ', ' . $kecamatan['nama'] . ', ' . $kabupaten['nama'] . ', ' . $provinsi['nama'],
                    ];
                }
            }
        }

        file_put_contents($folder_kabupaten . '/tps.json', json_encode($tps_kabupaten_kota, JSON_PRETTY_PRINT));
    }
}

echo "Selesai";
die;
