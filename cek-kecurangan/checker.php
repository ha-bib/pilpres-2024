<?php
foreach ($data_provinsi as $provinsi) {
    if (!in_array($provinsi['kode'], $target_provinsi)) continue;

    $list_folder = scandir('data-tps/' . $provinsi['kode']);
    $kode_provinsi = array_diff($list_folder, ['.', '..']);
    echo '-' . $provinsi['nama'] . ' ' . count($kode_provinsi) . ' kabupaten' . PHP_EOL;
    echo '--------------- ---------------' . PHP_EOL;
    foreach ($kode_provinsi as $kode_kabupaten) {
        $file_json = 'data-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten . '/tps.json';
        $tps_kabupaten = json_decode(file_get_contents($file_json), TRUE);
        $jumlah_tps = count($tps_kabupaten);
        $step = round($jumlah_tps / 30);
        echo '--' . $kode_kabupaten . ' ' . $jumlah_tps . ' tps' . PHP_EOL;
        $kecurangan_kabupaten_kota = [];
        foreach ($tps_kabupaten as $tps) {
            $url = 'https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp/' . $tps['url'];
            $json = file_get_contents($url);
            while ($json === FALSE) {
                sleep(5000);
                $json = file_get_contents($url);
            }
            $data_tps = json_decode($json, TRUE);
            if ($data_tps['status_suara'] == 'false' || $data_tps['status_adm'] == 'false' || $data_tps['administrasi'] == null || !isset($data_tps['chart'][100025])) continue; // jika administrasi belum selesai, skip

            $total_suara = $data_tps['chart'][100025] + $data_tps['chart'][100026] + $data_tps['chart'][100027];
            $suara_sah = $data_tps['administrasi']['suara_sah'];
            if ($suara_sah != $total_suara) {
                $kecurangan_kabupaten_kota[] = [
                    'provinsi' => $tps['provinsi'],
                    'kabupaten' => $tps['kabupaten'],
                    'kecamatan' => $tps['kecamatan'],
                    'desa' => $tps['desa'],
                    'tps' => $tps['tps'],
                    'nama' => $tps['nama'],
                    'selisih' => $total_suara - $suara_sah,
                    'url' => 'https://pemilu2024.kpu.go.id/pilpres/hitung-suara/' . substr($tps['url'], 0, -5),
                ];
            }
            $jumlah_tps--;
            if ($jumlah_tps % $step == 0) {
                echo '-';
            }
        }
        echo PHP_EOL;
        if (count($kecurangan_kabupaten_kota) > 0) {
            if (!is_dir('data-kecurangan-tps/' . $provinsi['kode'])) mkdir('data-kecurangan-tps/' . $provinsi['kode'], 0777);
            if (!is_dir('data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten)) mkdir('data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten, 0777);
            file_put_contents('data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten . '/kecurangan.json', json_encode($kecurangan_kabupaten_kota, JSON_PRETTY_PRINT));
            echo "----SELESAI----" . PHP_EOL;
        }
    }
}
