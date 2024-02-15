<?php
$list_folder = scandir('data-kecurangan-tps');
$kode_provinsis = array_diff($list_folder, ['.', '..']);
$data_kecurangans = [];
foreach ($kode_provinsis as $key => $kode_provinsi) {
    $list_folder = scandir('data-kecurangan-tps/' . $kode_provinsi);
    $kode_kabupatens = array_diff($list_folder, ['.', '..']);
    foreach ($kode_kabupatens as $key => $kode_kabupaten) {
        $file_json = 'data-kecurangan-tps/' . $kode_provinsi . '/' . $kode_kabupaten . '/kecurangan.json';
        $new_data = json_decode(file_get_contents($file_json), TRUE);
        $data_kecurangans = array_merge($data_kecurangans, $new_data);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<meta name="viewport" content="width=device-width, initial-scale=0.5, shrink-to-fit=no">
<title>TPS BERMASALAH </title>
<link rel="icon" href="favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        font-family: sans-serif;
    }
</style>

<body>
    <div class=" py-5">
        <main>
            <h2 class="text-center"><b>DATA TPS BERMASALAH </b></h2>
            <h5 class="text-center">Jumlah TPS : <b><?= number_format(count($data_kecurangans)) ?></b></h5>
            <h5 class="text-center">Selisih Tambah : <b id="selisih_tambah"></b></h5>
            <h5 class="text-center">Selisih Kurang : <b id="selisih_kurang"></b></h5>
            <div class="table-responsive mt-4">
                <table class="table table-hover table-bordered fs-12" id="table_element">
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th>Selisih Suara</th>
                            <th>Link KPU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        $selisih_tambah = 0;
                        $selisih_kurang = 0;
                        foreach ($data_kecurangans as $key => $kecurangan) : ?>
                            <?php $kecurangan['selisih'] < 0 ? $selisih_kurang += $kecurangan['selisih'] : $selisih_tambah += $kecurangan['selisih']; ?>
                            <tr>
                                <td><?= $kecurangan['nama'] ?></td>
                                <td><?= $kecurangan['selisih'] ?></td>
                                <td class="text-center"><a href="<?= $kecurangan['url'] ?>" target="_blank" class="btn btn-danger btn-sm py-0 px-3">KPU</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tablefilter/2.5.0/tablefilter_all_min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/stupidtable/1.1.0/stupidtable.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selisih_tambah').text('<?= number_format($selisih_tambah) ?>');
            $('#selisih_kurang').text('<?= number_format($selisih_kurang) ?>');
            $('#table_element').stupidtable();
            load_filter();

            function load_filter() {
                var table1_Props = {
                    col_types: ['string', 'number'],
                    sort: true,
                    col_1: "select",
                    col_2: "none",
                    display_all_text: " Semua ",
                    sort_select: true,
                    on_keyup: true,
                    rows_counter: true,
                    help_instructions: false,
                    rows_counter_text: "Total : "
                };
                var tf1 = setFilterGrid("table_element", table1_Props);
                $('#flt0_table_element').attr('placeholder', 'Cari');
            }
        })
    </script>

    <style>
        .form-control option {
            text-align: center;
        }

        .table th,
        .jsgrid .jsgrid-table th,
        .table td,
        .jsgrid .jsgrid-table td {
            padding: .1rem .3rem;
        }

        .flt {
            line-height: 1;
            padding-left: 5px;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 700;
            height: 30px;
            width: 100%;
            border: 1px solid #ced4da;
        }

        .flt:focus {
            border: 1px solid #ced4da;
        }

        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        .a {
            color: red !important;
        }

        .h {
            color: #04b500 !important;
        }


        table {
            border-collapse: collapse;
        }

        table thead tr th {
            border-bottom: 1px solid #000000;
        }

        table th {
            border-left: 1px solid rgba(0, 0, 0, 0.2);
            border-right: 1px solid rgba(0, 0, 0, 0.2);
        }

        table th {
            /* Added padding for better layout after collapsing */
            padding: 4px 8px;
        }

        @media print {
            .fltrow {
                visibility: hidden;
            }

            .nav-item {
                visibility: hidden;
            }

            .btn {
                visibility: hidden;
            }
        }

        .nav-item {
            width: 50%;
            text-align: center;
        }

        th {
            text-align: center;
        }

        .table-responsive {
            overflow-y: hidden;
        }
    </style>
</body>

</html>