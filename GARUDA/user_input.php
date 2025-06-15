<?php
// filepath: c:\xampp\htdocs\PPL-ALPAHANKAM\GARUDA\user_input.php
session_start();
$subMaterilOptions = [
    'Ranpur' => [
        ['value' => 'Panser', 'label' => 'Panser'],
        ['value' => 'Tank', 'label' => 'Tank']
    ],
    'Senjata' => [
        ['optgroup' => 'Senjata Infantri Perorangan', 'options' => [
            ['value' => 'Ringan (Perorangan)', 'label' => 'Ringan (Perorangan)'],
            ['value' => 'Berat (Kelompok)', 'label' => 'Berat (Kelompok)'],
        ]],
        ['value' => 'Senjata Kavaleri Arkubah/Canon', 'label' => 'Senjata Kavaleri Arkubah/Canon'],
        ['optgroup' => 'Senjata Artileri', 'options' => [
            ['value' => 'Artileri Medan', 'label' => 'Artileri Medan'],
            ['value' => 'Artileri Pertahanan Udara', 'label' => 'Artileri Pertahanan Udara'],
            ['value' => 'Shot Gun', 'label' => 'Shot Gun'],
        ]],
        ['value' => 'Senjata Khusus', 'label' => 'Senjata Khusus'],
        ['value' => 'Senjata Pesawat Terbang', 'label' => 'Senjata Pesawat Terbang'],
        ['value' => 'Senjata Alins', 'label' => 'Senjata Alins'],
    ],
    'Munisi' => [
        ['value' => 'Kaliber Kecil', 'label' => 'Kaliber Kecil'],
        ['value' => 'Kaliber Besar', 'label' => 'Kaliber Besar']
    ],
    'Ranmor' => [
        ['value' => 'Truk', 'label' => 'Truk'],
        ['value' => 'Jeep', 'label' => 'Jeep'],
        ['value' => 'Motor', 'label' => 'Motor']
    ],
    'Aloptik' => [
        ['value' => 'Teropong', 'label' => 'Teropong'],
        ['value' => 'Night Vision', 'label' => 'Night Vision']
    ]
];

$jenisMateril = $_POST['jenis_materil'] ?? '';
$subMateril   = $_POST['sub_materil'] ?? '';
$jumlahTotal  = (int) ($_POST['jumlah_total'] ?? 0);
$jumlahBaik   = (int) ($_POST['jumlah_baik'] ?? 0);
$jumlahRR     = (int) ($_POST['jumlah_rr'] ?? 0);
$jumlahRB     = (int) ($_POST['jumlah_rb'] ?? 0);
$kesiapan     = ($jumlahTotal > 0) ? round(($jumlahBaik / $jumlahTotal) * 100, 2) : 0;
$submitted    = isset($_POST['submit']);

if ($submitted && $jenisMateril && $subMateril) {
    $_SESSION['rekap'][] = [
        'jenis' => $jenisMateril,
        'sub' => $subMateril,
        'total' => $jumlahTotal,
        'baik' => $jumlahBaik,
        'rr' => $jumlahRR,
        'rb' => $jumlahRB,
        'kesiapan' => $kesiapan
    ];
}
$rekap = $_SESSION['rekap'] ?? [];
$akumulasi = [];
foreach ($rekap as $row) {
    $jenis = $row['jenis'];
    if (!isset($akumulasi[$jenis])) {
        $akumulasi[$jenis] = [
            'total' => 0,
            'baik' => 0,
            'rr' => 0,
            'rb' => 0
        ];
    }
    $akumulasi[$jenis]['total'] += $row['total'];
    $akumulasi[$jenis]['baik']  += $row['baik'];
    $akumulasi[$jenis]['rr']    += $row['rr'];
    $akumulasi[$jenis]['rb']    += $row['rb'];
}
if (isset($_POST['reset'])) {
    unset($_SESSION['rekap']);
    header("Location: user_input.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Data Materil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
        }
        .main-title {
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 1px;
            margin-bottom: 30px;
            text-shadow: 0 2px 8px #e2e8f0;
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.09);
            background: #fff;
        }
        .form-label {
            font-weight: 500;
            color: #334155;
        }
        .input-table th, .input-table td {
            vertical-align: middle;
            text-align: center;
        }
        .input-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
        }
        .input-table input {
            width: 80px;
            margin: auto;
            border-radius: 8px;
        }
        .btn-primary {
            background: linear-gradient(90deg, #d10000 0%, #ff7700 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #b10000 0%, #ff9900 100%);
        }
        .btn-danger {
            border-radius: 8px;
        }
        .table-secondary {
            background: #f1f5f9 !important;
            color: #d10000 !important;
        }
        .card-result {
            background: linear-gradient(90deg, #f8fafc 60%, #ffe5d0 100%);
            border-left: 6px solid #d10000;
        }
        .card-table {
            background: linear-gradient(90deg, #f8fafc 60%, #e0f2fe 100%);
            border-left: 6px solid #0ea5e9;
        }
        @media (max-width: 767px) {
            .row.flex-lg-row { flex-direction: column !important; }
            .main-title { font-size: 1.5rem; }
        }
    </style>
    <script>
    function updateSubMateril() {
        var jenis = document.getElementById('jenis_materil').value;
        var sub = document.getElementById('sub_materil');
        var options = {
            Ranpur: [
                {value: 'Panser', label: 'Panser'},
                {value: 'Tank', label: 'Tank'}
            ],
            Senjata: [
                {optgroup: 'Senjata Infantri Perorangan', options: [
                    {value: 'Ringan (Perorangan)', label: 'Ringan (Perorangan)'},
                    {value: 'Berat (Kelompok)', label: 'Berat (Kelompok)'}
                ]},
                {value: 'Senjata Kavaleri Arkubah/Canon', label: 'Senjata Kavaleri Arkubah/Canon'},
                {optgroup: 'Senjata Artileri', options: [
                    {value: 'Artileri Medan', label: 'Artileri Medan'},
                    {value: 'Artileri Pertahanan Udara', label: 'Artileri Pertahanan Udara'},
                    {value: 'Shot Gun', label: 'Shot Gun'}
                ]},
                {value: 'Senjata Khusus', label: 'Senjata Khusus'},
                {value: 'Senjata Pesawat Terbang', label: 'Senjata Pesawat Terbang'},
                {value: 'Senjata Alins', label: 'Senjata Alins'}
            ],
            Munisi: [
                {value: 'Kaliber Kecil', label: 'Kaliber Kecil'},
                {value: 'Kaliber Besar', label: 'Kaliber Besar'}
            ],
            Ranmor: [
                {value: 'Truk', label: 'Truk'},
                {value: 'Jeep', label: 'Jeep'},
                {value: 'Motor', label: 'Motor'}
            ],
            Aloptik: [
                {value: 'Teropong', label: 'Teropong'},
                {value: 'Night Vision', label: 'Night Vision'}
            ]
        };
        sub.innerHTML = '<option value="">Pilih Sub Materil</option>';
        if (options[jenis]) {
            options[jenis].forEach(function(opt) {
                if (opt.optgroup) {
                    var group = document.createElement('optgroup');
                    group.label = opt.optgroup;
                    opt.options.forEach(function(subopt) {
                        var o = document.createElement('option');
                        o.value = subopt.value;
                        o.text = subopt.label;
                        group.appendChild(o);
                    });
                    sub.appendChild(group);
                } else {
                    var o = document.createElement('option');
                    o.value = opt.value;
                    o.text = opt.label;
                    sub.appendChild(o);
                }
            });
        }
    }
    </script>
</head>
<body>
<div class="container py-5">
    <h2 class="main-title text-center">Input Data Materil Alutsista</h2>
    <form method="post">
        <div class="row flex-lg-row g-4">
            <!-- Kolom Kiri: Pilihan Jenis & Sub Materil -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="mb-3">
                        <label for="jenis_materil" class="form-label">Jenis Materil</label>
                        <select class="form-select" id="jenis_materil" name="jenis_materil" required onchange="updateSubMateril()">
                            <option value="">Pilih Jenis Materil</option>
                            <?php foreach ($subMaterilOptions as $jenis => $subs): ?>
                                <option value="<?= $jenis ?>" <?= $jenisMateril === $jenis ? 'selected' : '' ?>><?= $jenis ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sub_materil" class="form-label">Sub Materil</label>
                        <select class="form-select" id="sub_materil" name="sub_materil" required>
                            <option value="">Pilih Sub Materil</option>
                            <?php
                            if ($jenisMateril && isset($subMaterilOptions[$jenisMateril])) {
                                foreach ($subMaterilOptions[$jenisMateril] as $sub) {
                                    if (isset($sub['optgroup'])) {
                                        echo '<optgroup label="'.$sub['optgroup'].'">';
                                        foreach ($sub['options'] as $subopt) {
                                            $selected = ($subMateril === $subopt['value']) ? 'selected' : '';
                                            echo "<option value=\"{$subopt['value']}\" $selected>{$subopt['label']}</option>";
                                        }
                                        echo '</optgroup>';
                                    } else {
                                        $selected = ($subMateril === $sub['value']) ? 'selected' : '';
                                        echo "<option value=\"{$sub['value']}\" $selected>{$sub['label']}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan: Tabel Input Jumlah & Kondisi -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <table class="table table-bordered input-table mb-0">
                        <thead>
                            <tr>
                                <th>Jumlah Total</th>
                                <th>Baik</th>
                                <th>Rusak Ringan</th>
                                <th>Rusak Berat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="number" class="form-control" id="jumlah_total" name="jumlah_total" min="0" required value="<?= htmlspecialchars($jumlahTotal) ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="jumlah_baik" min="0" required value="<?= htmlspecialchars($jumlahBaik) ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="jumlah_rr" min="0" required value="<?= htmlspecialchars($jumlahRR) ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="jumlah_rb" min="0" required value="<?= htmlspecialchars($jumlahRB) ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-primary w-100 py-2 fs-5">Tampilkan Hasil</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php if ($submitted): ?>
        <div class="card card-result p-4 mt-5 mx-auto" style="max-width:600px;">
            <h5 class="mb-3 text-danger fw-bold">Hasil Input</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Jenis Materil:</strong> <?= htmlspecialchars($jenisMateril) ?></li>
                <li class="list-group-item"><strong>Sub Materil:</strong> <?= htmlspecialchars($subMateril) ?></li>
                <li class="list-group-item"><strong>Jumlah Total:</strong> <?= $jumlahTotal ?></li>
                <li class="list-group-item"><strong>Baik:</strong> <?= $jumlahBaik ?></li>
                <li class="list-group-item"><strong>Rusak Ringan:</strong> <?= $jumlahRR ?></li>
                <li class="list-group-item"><strong>Rusak Berat:</strong> <?= $jumlahRB ?></li>
                <li class="list-group-item"><strong>Kesiapan Tempur:</strong> <span class="badge bg-success fs-6"><?= $kesiapan ?>%</span></li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($rekap)): ?>
        <div class="card card-table p-4 mt-5">
            <h5 class="mb-3 text-primary fw-bold">Tabel Akumulasi Keseluruhan</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead>
                        <tr>
                            <th rowspan="2">Jenis Materil</th>
                            <th rowspan="2">Sub Materil</th>
                            <th colspan="4">Jumlah</th>
                            <th rowspan="2">Kesiapan Tempur (%)</th>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <th>Baik</th>
                            <th>Rusak Ringan</th>
                            <th>Rusak Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Kelompokkan rekap per jenis materil
                    $rekapByJenis = [];
                    foreach ($rekap as $row) {
                        $rekapByJenis[$row['jenis']][] = $row;
                    }
                    foreach ($rekapByJenis as $jenis => $rows):
                        $rowspan = count($rows) + 1; // +1 untuk baris total
                        $first = true;
                        foreach ($rows as $row):
                    ?>
                        <tr>
                            <?php if ($first): ?>
                                <td rowspan="<?= $rowspan ?>" class="align-middle fw-bold bg-light"><?= htmlspecialchars($jenis) ?></td>
                            <?php $first = false; endif; ?>
                            <td><?= htmlspecialchars($row['sub']) ?></td>
                            <td><?= $row['total'] ?></td>
                            <td><?= $row['baik'] ?></td>
                            <td><?= $row['rr'] ?></td>
                            <td><?= $row['rb'] ?></td>
                            <td><span class="badge bg-success"><?= $row['kesiapan'] ?>%</span></td>
                        </tr>
                    <?php endforeach; 
                        // Baris total per jenis
                        $tot = $akumulasi[$jenis];
                        $kesiapanJenis = ($tot['total'] > 0) ? round(($tot['baik'] / $tot['total']) * 100, 2) : 0;
                    ?>
                        <tr class="table-secondary fw-bold">
                            <td>Total</td>
                            <td><?= $tot['total'] ?></td>
                            <td><?= $tot['baik'] ?></td>
                            <td><?= $tot['rr'] ?></td>
                            <td><?= $tot['rb'] ?></td>
                            <td><span class="badge bg-primary"><?= $kesiapanJenis ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" class="mt-2">
                <button type="submit" name="reset" class="btn btn-danger btn-sm" onclick="return confirm('Reset semua data?')">Reset Data</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        updateSubMateril();
        <?php if ($subMateril): ?>
        document.getElementById('sub_materil').value = "<?= $subMateril ?>";
        <?php endif; ?>
    });
</script>
</body>
</html>