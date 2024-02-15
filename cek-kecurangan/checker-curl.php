<?php
$mh = curl_multi_init();
$curl_array = array();
$subfolder = '';

foreach ($data_provinsi as $provinsi) {
    // if (!in_array($provinsi['kode'], $target_provinsi)) continue;
    if (is_dir('data-kecurangan-tps/' . $provinsi['kode']))  continue;

    $list_folder = scandir($subfolder . 'data-tps/' . $provinsi['kode']);
    $kode_provinsi = array_diff($list_folder, ['.', '..']);
    echo '-' . $provinsi['nama'] . ' ' . count($kode_provinsi) . ' kabupaten' . PHP_EOL;
    echo '--------------- ---------------' . PHP_EOL;
    foreach ($kode_provinsi as $kode_kabupaten) {
        $file_json = $subfolder . 'data-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten . '/tps.json';
        $tps_kabupaten_full = json_decode(file_get_contents($file_json), TRUE);

        $chunks = array_chunk($tps_kabupaten_full, 500);
        $kecurangan_kabupaten_kota = [];
        foreach ($chunks as $tps_kabupaten) {
            $jumlah_tps = count($tps_kabupaten);
            $tps_diproses = 0;
            $step = round($jumlah_tps / 30);
            echo '--' . $provinsi['nama'] . ' ' . $kode_kabupaten . ' ' . $jumlah_tps . ' tps' . PHP_EOL;
            foreach ($tps_kabupaten as $key => $tps) {
                $url = 'https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp/' . $tps['url'];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_multi_add_handle($mh, $ch);
                $curl_array[$key] = $ch;
            }
            $running = null;
            do {
                curl_multi_exec($mh, $running);
            } while ($running > 0);
            foreach ($curl_array as $idx => $ch) {
                $json = curl_multi_getcontent($ch);
                while ($json === FALSE) {
                    $json = curl_multi_getcontent($ch);
                }
                $data_tps = json_decode($json, TRUE);
                if (
                    $data_tps['status_suara'] == false ||
                    $data_tps['status_adm'] == false ||
                    $data_tps['administrasi'] == null ||
                    !isset($data_tps['chart'][100025]) ||
                    !isset($data_tps['chart'][100026]) ||
                    !isset($data_tps['chart'][100027]) ||
                    !isset($data_tps['images'][0])
                ) continue; // jika administrasi belum selesai, skip

                $total_suara = $data_tps['chart'][100025] + $data_tps['chart'][100026] + $data_tps['chart'][100027];
                $suara_sah = $data_tps['administrasi']['suara_sah'];
                if ($suara_sah != $total_suara) {
                    $image_link = $data_tps['images'][0];
                    preg_match('/\/(\d+)-\d+-/', $image_link, $matches);
                    $id_tps = $matches[1];
                    $key = array_search($id_tps, array_column($tps_kabupaten, 'tps'));
                    $tps = $tps_kabupaten[$key];

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
            }
        }
        echo  count($kecurangan_kabupaten_kota) . " kecurangan" . PHP_EOL;
        if (count($kecurangan_kabupaten_kota) > 0) {
            if (!is_dir($subfolder . 'data-kecurangan-tps/' . $provinsi['kode'])) mkdir($subfolder . 'data-kecurangan-tps/' . $provinsi['kode'], 0777);
            if (!is_dir($subfolder . 'data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten)) mkdir($subfolder . 'data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten, 0777);
            file_put_contents($subfolder . 'data-kecurangan-tps/' . $provinsi['kode'] . '/' . $kode_kabupaten . '/kecurangan.json', json_encode($kecurangan_kabupaten_kota, JSON_PRETTY_PRINT));
            echo "----SELESAI----" . PHP_EOL;
            echo PHP_EOL;
        }
        curl_multi_close($mh);
    }
}
