<?php
// filepath: c:\xampp\htdocs\johan\admin.php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "data_kodam";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get military units based on type
function getUnits($conn, $type = null, $parent_id = null) {
    $units = [];
    $sql = "SELECT u.*, p.name as parent_name 
            FROM military_units u 
            LEFT JOIN military_units p ON u.parent_id = p.id";
    $params = [];
    
    if ($type) {
        $sql .= " WHERE u.unit_type = ?";
        $params[] = $type;
        
        if ($parent_id) {
            $sql .= " AND u.parent_id = ?";
            $params[] = $parent_id;
        }
    } else {
        if ($parent_id) {
            $sql .= " WHERE u.parent_id = ?";
            $params[] = $parent_id;
        }
    }
    
    $sql .= " ORDER BY u.unit_type, u.name";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    
    return $units;
}

// Function to get equipment for a unit
function getEquipment($conn, $unit_id = null) {
    $equipment = [];
    $sql = "SELECT e.*, u.name as unit_name 
            FROM military_equipment e 
            JOIN military_units u ON e.unit_id = u.id";
    
    if ($unit_id) {
        $sql .= " WHERE e.unit_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $unit_id);
    } else {
        $sql .= " ORDER BY u.unit_type, u.name";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $equipment[] = $row;
    }
    
    return $equipment;
}

// Function to get a single unit by ID
function getUnitById($conn, $id) {
    $sql = "SELECT * FROM military_units WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Function to get equipment by ID
function getEquipmentById($conn, $id) {
    $sql = "SELECT * FROM military_equipment WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Process form submissions
$message = "";
$error = "";

// Add/Edit Unit
if (isset($_POST['save_unit'])) {
    $id = $_POST['unit_id'] ?? generateId('unit');
    $name = $_POST['name'];
    $unit_type = $_POST['unit_type'];
    $description = $_POST['description'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    // Check if this is an edit or add operation
    $checkUnit = $conn->prepare("SELECT id FROM military_units WHERE id = ?");
    $checkUnit->bind_param("s", $id);
    $checkUnit->execute();
    $unitExists = $checkUnit->get_result()->num_rows > 0;
    
    if ($unitExists) {
        // Update
        $sql = "UPDATE military_units SET name = ?, unit_type = ?, description = ?, parent_id = ?, latitude = ?, longitude = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdds", $name, $unit_type, $description, $parent_id, $latitude, $longitude, $id);
        
        if ($stmt->execute()) {
            $message = "Unit berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui unit: " . $conn->error;
        }
    } else {
        // Insert
        $sql = "INSERT INTO military_units (id, name, unit_type, description, parent_id, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdd", $id, $name, $unit_type, $description, $parent_id, $latitude, $longitude);
        
        if ($stmt->execute()) {
            $message = "Unit baru berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan unit: " . $conn->error;
        }
    }
}

// Add/Edit Equipment
if (isset($_POST['save_equipment'])) {
    $id = $_POST['equipment_id'] ?? generateId('equipment');
    $unit_id = $_POST['unit_id'];
    $jenis_materil = $_POST['jenis_materil'];
    $sub_materil = $_POST['sub_materil'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    $kesiapan = $_POST['kesiapan'];
    
    // Check if this is an edit or add operation
    $checkEquipment = $conn->prepare("SELECT id FROM military_equipment WHERE id = ?");
    $checkEquipment->bind_param("s", $id);
    $checkEquipment->execute();
    $equipmentExists = $checkEquipment->get_result()->num_rows > 0;
    
    if ($equipmentExists) {
        // Update
        $sql = "UPDATE military_equipment SET unit_id = ?, jenis_materil = ?, sub_materil = ?, jumlah = ?, kondisi = ?, kesiapan = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisss", $unit_id, $jenis_materil, $sub_materil, $jumlah, $kondisi, $kesiapan, $id);
        
        if ($stmt->execute()) {
            $message = "Alutsista berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui alutsista: " . $conn->error;
        }
    } else {
        // Insert
        $sql = "INSERT INTO military_equipment (id, unit_id, jenis_materil, sub_materil, jumlah, kondisi, kesiapan) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisi", $id, $unit_id, $jenis_materil, $sub_materil, $jumlah, $kondisi, $kesiapan);
        
        if ($stmt->execute()) {
            $message = "Alutsista baru berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan alutsista: " . $conn->error;
        }
    }
}

// Delete Unit
if (isset($_GET['delete_unit'])) {
    $id = $_GET['delete_unit'];
    
    // Check if there are child units
    $checkChildren = $conn->prepare("SELECT COUNT(*) as count FROM military_units WHERE parent_id = ?");
    $checkChildren->bind_param("s", $id);
    $checkChildren->execute();
    $childCount = $checkChildren->get_result()->fetch_assoc()['count'];
    
    if ($childCount > 0) {
        $error = "Unit tidak dapat dihapus karena memiliki unit anak. Hapus unit anak terlebih dahulu.";
    } else {
        // Delete all equipment first
        $conn->query("DELETE FROM military_equipment WHERE unit_id = '$id'");
        
        // Now delete the unit
        if ($conn->query("DELETE FROM military_units WHERE id = '$id'")) {
            $message = "Unit berhasil dihapus!";
        } else {
            $error = "Gagal menghapus unit: " . $conn->error;
        }
    }
}

// Delete Equipment
if (isset($_GET['delete_equipment'])) {
    $id = $_GET['delete_equipment'];
    
    if ($conn->query("DELETE FROM military_equipment WHERE id = '$id'")) {
        $message = "Alutsista berhasil dihapus!";
    } else {
        $error = "Gagal menghapus alutsista: " . $conn->error;
    }
}

// Generate a unique ID
function generateId($type) {
    $prefix = ($type === 'unit') ? 'unit-' : 'eq-';
    return $prefix . uniqid();
}

// Get all units for dropdown
$allUnits = getUnits($conn);

// Get filtered units if filter is applied
$filterType = $_GET['filter_type'] ?? null;
$filterParent = $_GET['filter_parent'] ?? null;

if ($filterType || $filterParent) {
    $units = getUnits($conn, $filterType, $filterParent);
} else {
    $units = $allUnits;
}

// Get all equipment or filtered by unit
$filterUnitId = $_GET['filter_unit'] ?? null;
$equipment = getEquipment($conn, $filterUnitId);

// Get unit for edit if specified
$editUnit = null;
if (isset($_GET['edit_unit'])) {
    $editUnit = getUnitById($conn, $_GET['edit_unit']);
}

// Get equipment for edit if specified
$editEquipment = null;
if (isset($_GET['edit_equipment'])) {
    $editEquipment = getEquipmentById($conn, $_GET['edit_equipment']);
}

// Default active tab
$activeTab = $_GET['tab'] ?? 'units';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Data Kodam</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding-bottom: 20px;
        }
        
        .header {
            background-color: #343a40;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .btn-military {
            background-color: #d10000;
            color: white;
            border: none;
        }
        
        .btn-military:hover {
            background-color: #b10000;
            color: white;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .nav-tabs .nav-link.active {
            font-weight: 600;
            border-bottom: 3px solid #d10000;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .unit-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
            border-radius: 10px;
        }
        
        .badge-kodam {
            background-color: #d10000;
            color: white;
        }
        
        .badge-korem {
            background-color: #ff7700;
            color: white;
        }
        
        .badge-kodim {
            background-color: #ffaa00;
            color: black;
        }
        
        .badge-koramil {
            background-color: #ffe100;
            color: black;
        }
    </style>
</head>
<body>
    <!-- Admin Dashboard -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="m-0">Admin Panel - Data Kodam</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="map.php" class="btn btn-success btn-sm ms-2">Lihat Peta</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Notifications -->
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
        
        <!-- Navigation Tabs -->
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
        
        <!-- Units Management -->
        <?php if ($activeTab === 'units'): ?>
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="tab" value="units">
                    <div class="col-md-5">
                        <label for="filter_type" class="form-label">Filter berdasarkan Jenis Unit:</label>
                        <select class="form-select" id="filter_type" name="filter_type">
                            <option value="">Semua Jenis</option>
                            <option value="KODAM" <?php echo $filterType === 'KODAM' ? 'selected' : ''; ?>>KODAM</option>
                            <option value="KOREM" <?php echo $filterType === 'KOREM' ? 'selected' : ''; ?>>KOREM</option>
                            <option value="KODIM" <?php echo $filterType === 'KODIM' ? 'selected' : ''; ?>>KODIM</option>
                            <option value="KORAMIL" <?php echo $filterType === 'KORAMIL' ? 'selected' : ''; ?>>KORAMIL</option>
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
                <!-- Add/Edit Unit Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo $editUnit ? 'Edit Unit' : 'Tambah Unit Baru'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
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
                                        <option value="KODIM" <?php echo $editUnit && $editUnit['unit_type'] === 'KODIM' ? 'selected' : ''; ?>>KODIM</option>
                                        <option value="KORAMIL" <?php echo $editUnit && $editUnit['unit_type'] === 'KORAMIL' ? 'selected' : ''; ?>>KORAMIL</option>
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
                                    <button type="submit" name="save_unit" class="btn btn-military">
                                        <i class="fas fa-save me-2"></i><?php echo $editUnit ? 'Update Unit' : 'Simpan Unit'; ?>
                                    </button>
                                    
                                    <?php if ($editUnit): ?>
                                        <a href="admin.php?tab=units" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Units List -->
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
                                                        <a href="admin.php?tab=units&delete_unit=<?php echo $unit['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus unit ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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
        
        <!-- Equipment Management -->
        <?php if ($activeTab === 'equipment'): ?>
            <!-- Filter Section -->
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
                <!-- Add/Edit Equipment Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo $editEquipment ? 'Edit Alutsista' : 'Tambah Alutsista Baru'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
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
                                    <button type="submit" name="save_equipment" class="btn btn-military">
                                        <i class="fas fa-save me-2"></i><?php echo $editEquipment ? 'Update Alutsista' : 'Simpan Alutsista'; ?>
                                    </button>
                                    
                                    <?php if ($editEquipment): ?>
                                        <a href="admin.php?tab=equipment" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Equipment List -->
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
                                                        <a href="admin.php?tab=equipment&delete_equipment=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus alutsista ini?')">
                                                            <i class="fas fa-trash"></i>
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
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show confirmation before page reload/navigation if there are unsaved changes
        const forms = document.querySelectorAll('form');
        let formChanged = false;
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    formChanged = true;
                });
            });
            
            form.addEventListener('submit', () => {
                formChanged = false;
            });
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Prevent accidental form submission when pressing Enter
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.nodeName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
        
        // Form validation for coordinates
        document.addEventListener('DOMContentLoaded', function() {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            
            if (latitudeInput && longitudeInput) {
                latitudeInput.addEventListener('change', validateCoordinates);
                longitudeInput.addEventListener('change', validateCoordinates);
            }
            
            function validateCoordinates() {
                const lat = parseFloat(latitudeInput.value);
                const lng = parseFloat(longitudeInput.value);
                
                if (lat < -90 || lat > 90) {
                    alert('Latitude harus berada di antara -90 dan 90 derajat.');
                    latitudeInput.value = '';
                }
                
                if (lng < -180 || lng > 180) {
                    alert('Longitude harus berada di antara -180 dan 180 derajat.');
                    longitudeInput.value = '';
                }
            }
        });
    </script>
</body>
</html>