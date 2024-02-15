<?php
$url = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/0.json';
$json_data_wilayah = file_get_contents($url);
$data_provinsi = json_decode($json_data_wilayah, TRUE);
if (!is_dir('data-kecurangan-tps')) mkdir('data-kecurangan-tps', 0777);

$target_provinsi = [
    'GORONTALO' => 75,
    'JAMBI' => 15
];

require_once 'checker.php';
