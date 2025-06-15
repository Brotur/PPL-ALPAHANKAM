<?php
// MODE DEMO TANPA DATABASE

// Dummy data untuk unit
$allUnits = [
    [
        'id' => 'unit-1',
        'name' => 'KODAM I/BB',
        'unit_type' => 'KODAM',
        'description' => 'Komando Daerah Militer I Bukit Barisan',
        'parent_id' => null,
        'parent_name' => '-',
        'latitude' => 3.5952,
        'longitude' => 98.6722
    ],
    [
        'id' => 'unit-2',
        'name' => 'KOREM 031/WB',
        'unit_type' => 'KOREM',
        'description' => 'Komando Resort Militer 031 Wirabima',
        'parent_id' => 'unit-1',
        'parent_name' => 'KODAM I/BB',
        'latitude' => 0.5070,
        'longitude' => 101.4478
    ]
];

// Dummy data untuk alutsista
$equipment = [
    [
        'id' => 'eq-1',
        'unit_id' => 'unit-1',
        'unit_name' => 'KODAM I/BB',
        'jenis_materil' => 'Tank',
        'sub_materil' => 'Tank Ringan',
        'jumlah' => 10,
        'kondisi' => 'B',
        'kesiapan' => 90
    ],
    [
        'id' => 'eq-2',
        'unit_id' => 'unit-2',
        'unit_name' => 'KOREM 031/WB',
        'jenis_materil' => 'Meriam',
        'sub_materil' => 'Meriam 105mm',
        'jumlah' => 5,
        'kondisi' => 'RR',
        'kesiapan' => 60
    ]
];

// Filter dan edit dummy
$filterType = $_GET['filter_type'] ?? null;
$filterParent = $_GET['filter_parent'] ?? null;
$filterUnitId = $_GET['filter_unit'] ?? null;
$editUnit = null;
$editEquipment = null;
$activeTab = $_GET['tab'] ?? 'units';

// Filter units
$units = array_filter($allUnits, function($unit) use ($filterType, $filterParent) {
    if ($filterType && $unit['unit_type'] !== $filterType) return false;
    if ($filterParent && $unit['parent_id'] !== $filterParent) return false;
    return true;
});
if (empty($units)) $units = $allUnits;

// Filter equipment
if ($filterUnitId) {
    $equipment = array_filter($equipment, function($eq) use ($filterUnitId) {
        return $eq['unit_id'] === $filterUnitId;
    });
}

// Edit unit
if (isset($_GET['edit_unit'])) {
    foreach ($allUnits as $unit) {
        if ($unit['id'] === $_GET['edit_unit']) {
            $editUnit = $unit;
            break;
        }
    }
}

// Edit equipment
if (isset($_GET['edit_equipment'])) {
    foreach ($equipment as $eq) {
        if ($eq['id'] === $_GET['edit_equipment']) {
            $editEquipment = $eq;
            break;
        }
    }
}

// Notifikasi dummy
$message = '';
$error = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Data Kodam (Demo)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; padding-bottom: 20px; }
        .header { background-color: #343a40; color: white; padding: 15px 0; margin-bottom: 30px; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .card-header { background-color: #f8f9fa; font-weight: 600;}
        .form-group { margin-bottom: 15px;}
        .btn-military { background-color: #d10000; color: white; border: none;}
        .btn-military:hover { background-color: #b10000; color: white;}
        .table-responsive { overflow-x: auto;}
        .nav-tabs .nav-link.active { font-weight: 600; border-bottom: 3px solid #d10000;}
        .filter-section { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #dee2e6;}
        .unit-badge { font-size: 0.8rem; padding: 0.3rem 0.5rem; border-radius: 10px;}
        .badge-kodam { background-color: #d10000; color: white;}
        .badge-korem { background-color: #ff7700; color: white;}
        .badge-kodim { background-color: #ffaa00; color: black;}
        .badge-koramil { background-color: #ffe100; color: black;}
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="m-0">Admin Panel - Data Kodam (Demo)</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="map.php" class="btn btn-success btn-sm ms-2">Lihat Peta</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'units' ? 'active' : ''; ?>" href="admin.php?tab=units">
                    <i class="fas fa-building me-2"></i>Kelola Unit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'equipment' ? 'active' : ''; ?>" href="admin.php?tab=equipment">
                    <i class="fas fa-tools me-2"></i>Kelola Alutsista
                </a>
            </li>
        </ul>
        <?php if ($activeTab === 'units'): ?>
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="tab" value="units">
                    <div class="col-md-5">
                        <label for="filter_type" class="form-label">Filter berdasarkan Jenis Unit:</label>
                        <select class="form-select" id="filter_type" name="filter_type">
                            <option value="">Semua Jenis</option>
                            <option value="KODAM" <?php echo $filterType === 'KODAM' ? 'selected' : ''; ?>>KODAM</option>
                            <option value="KOREM" <?php echo $filterType === 'KOREM' ? 'selected' : ''; ?>>KOREM</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="filter_parent" class="form-label">Filter berdasarkan Unit Induk:</label>
                        <select class="form-select" id="filter_parent" name="filter_parent">
                            <option value="">Semua</option>
                            <?php foreach ($allUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>" <?php echo $filterParent === $unit['id'] ? 'selected' : ''; ?>>
                                    <?php echo $unit['name']; ?> (<?php echo $unit['unit_type']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo $editUnit ? 'Edit Unit' : 'Tambah Unit Baru'; ?>
                        </div>
                        <div class="card-body">
                            <form>
                                <input type="hidden" name="unit_id" value="<?php echo $editUnit ? $editUnit['id'] : ''; ?>">
                                <div class="form-group">
                                    <label for="name">Nama Unit:</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo $editUnit ? $editUnit['name'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="unit_type">Jenis Unit:</label>
                                    <select class="form-select" id="unit_type" name="unit_type" required>
                                        <option value="KODAM" <?php echo $editUnit && $editUnit['unit_type'] === 'KODAM' ? 'selected' : ''; ?>>KODAM</option>
                                        <option value="KOREM" <?php echo $editUnit && $editUnit['unit_type'] === 'KOREM' ? 'selected' : ''; ?>>KOREM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="description">Deskripsi:</label>
                                    <textarea class="form-control" id="description" name="description" rows="2"><?php echo $editUnit ? $editUnit['description'] : ''; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="parent_id">Unit Induk:</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">Tidak Ada (Top Level)</option>
                                        <?php foreach ($allUnits as $unit): ?>
                                            <?php if ($editUnit && $editUnit['id'] === $unit['id']) continue; ?>
                                            <option value="<?php echo $unit['id']; ?>" <?php echo $editUnit && $editUnit['parent_id'] === $unit['id'] ? 'selected' : ''; ?>>
                                                <?php echo $unit['name']; ?> (<?php echo $unit['unit_type']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="latitude">Latitude:</label>
                                    <input type="number" step="0.0000001" class="form-control" id="latitude" name="latitude" required value="<?php echo $editUnit ? $editUnit['latitude'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="longitude">Longitude:</label>
                                    <input type="number" step="0.0000001" class="form-control" id="longitude" name="longitude" required value="<?php echo $editUnit ? $editUnit['longitude'] : ''; ?>">
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-military" disabled>
                                        <i class="fas fa-save me-2"></i><?php echo $editUnit ? 'Update Unit' : 'Simpan Unit'; ?>
                                    </button>
                                    <?php if ($editUnit): ?>
                                        <a href="admin.php?tab=units" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="alert alert-warning mt-2">Demo mode: Form tidak dapat disimpan.</div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Daftar Unit</span>
                            <span class="badge bg-primary"><?php echo count($units); ?> unit</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($units)): ?>
                                <div class="alert alert-info">Tidak ada unit yang ditemukan.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Jenis</th>
                                                <th>Unit Induk</th>
                                                <th>Koordinat</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($units as $unit): ?>
                                                <tr>
                                                    <td><?php echo $unit['name']; ?></td>
                                                    <td>
                                                        <span class="badge unit-badge badge-<?php echo strtolower($unit['unit_type']); ?>">
                                                            <?php echo $unit['unit_type']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $unit['parent_name'] ?? '-'; ?></td>
                                                    <td>
                                                        <small><?php echo $unit['latitude']; ?>, <?php echo $unit['longitude']; ?></small>
                                                    </td>
                                                    <td>
                                                        <a href="admin.php?tab=units&edit_unit=<?php echo $unit['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-danger" disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <a href="admin.php?tab=equipment&filter_unit=<?php echo $unit['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-tools"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($activeTab === 'equipment'): ?>
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="tab" value="equipment">
                    <div class="col-md-10">
                        <label for="filter_unit" class="form-label">Filter berdasarkan Unit:</label>
                        <select class="form-select" id="filter_unit" name="filter_unit">
                            <option value="">Semua Unit</option>
                            <?php foreach ($allUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>" <?php echo $filterUnitId === $unit['id'] ? 'selected' : ''; ?>>
                                    <?php echo $unit['name']; ?> (<?php echo $unit['unit_type']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo $editEquipment ? 'Edit Alutsista' : 'Tambah Alutsista Baru'; ?>
                        </div>
                        <div class="card-body">
                            <form>
                                <input type="hidden" name="equipment_id" value="<?php echo $editEquipment ? $editEquipment['id'] : ''; ?>">
                                <div class="form-group">
                                    <label for="unit_id">Unit:</label>
                                    <select class="form-select" id="unit_id" name="unit_id" required>
                                        <option value="">Pilih Unit</option>
                                        <?php foreach ($allUnits as $unit): ?>
                                            <option value="<?php echo $unit['id']; ?>" <?php echo $editEquipment && $editEquipment['unit_id'] === $unit['id'] ? 'selected' : ''; ?>>
                                                <?php echo $unit['name']; ?> (<?php echo $unit['unit_type']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="jenis_materil">Jenis Materil:</label>
                                    <input type="text" class="form-control" id="jenis_materil" name="jenis_materil" required value="<?php echo $editEquipment ? $editEquipment['jenis_materil'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="sub_materil">Sub Materil:</label>
                                    <input type="text" class="form-control" id="sub_materil" name="sub_materil" required value="<?php echo $editEquipment ? $editEquipment['sub_materil'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="jumlah">Jumlah:</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" required value="<?php echo $editEquipment ? $editEquipment['jumlah'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="kondisi">Kondisi:</label>
                                    <select class="form-select" id="kondisi" name="kondisi" required>
                                        <option value="B" <?php echo $editEquipment && $editEquipment['kondisi'] === 'B' ? 'selected' : ''; ?>>Baik (B)</option>
                                        <option value="RR" <?php echo $editEquipment && $editEquipment['kondisi'] === 'RR' ? 'selected' : ''; ?>>Rusak Ringan (RR)</option>
                                        <option value="RB" <?php echo $editEquipment && $editEquipment['kondisi'] === 'RB' ? 'selected' : ''; ?>>Rusak Berat (RB)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="kesiapan">Kesiapan (%):</label>
                                    <input type="number" class="form-control" id="kesiapan" name="kesiapan" min="0" max="100" required value="<?php echo $editEquipment ? $editEquipment['kesiapan'] : '100'; ?>">
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-military" disabled>
                                        <i class="fas fa-save me-2"></i><?php echo $editEquipment ? 'Update Alutsista' : 'Simpan Alutsista'; ?>
                                    </button>
                                    <?php if ($editEquipment): ?>
                                        <a href="admin.php?tab=equipment" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="alert alert-warning mt-2">Demo mode: Form tidak dapat disimpan.</div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Daftar Alutsista</span>
                            <span class="badge bg-primary"><?php echo count($equipment); ?> item</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($equipment)): ?>
                                <div class="alert alert-info">Tidak ada alutsista yang ditemukan.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Jenis Materil</th>
                                                <th>Sub Materil</th>
                                                <th>Jumlah</th>
                                                <th>Kondisi</th>
                                                <th>Kesiapan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($equipment as $item): ?>
                                                <tr>
                                                    <td><?php echo $item['unit_name']; ?></td>
                                                    <td><?php echo $item['jenis_materil']; ?></td>
                                                    <td><?php echo $item['sub_materil']; ?></td>
                                                    <td><?php echo $item['jumlah']; ?></td>
                                                    <td>
                                                        <?php if ($item['kondisi'] === 'B'): ?>
                                                            <span class="badge bg-success">Baik</span>
                                                        <?php elseif ($item['kondisi'] === 'RR'): ?>
                                                            <span class="badge bg-warning text-dark">Rusak Ringan</span>
                                                        <?php elseif ($item['kondisi'] === 'RB'): ?>
                                                            <span class="badge bg-danger">Rusak Berat</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo $item['kesiapan'] >= 70 ? 'bg-success' : ($item['kesiapan'] >= 40 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                                role="progressbar" 
                                                                style="width: <?php echo $item['kesiapan']; ?>%;" 
                                                                aria-valuenow="<?php echo $item['kesiapan']; ?>" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                                <?php echo $item['kesiapan']; ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="admin.php?tab=equipment&edit_equipment=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-danger" disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>