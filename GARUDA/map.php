<?php
// filepath: c:\xampp\htdocs\johan\map.php
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
function getUnits($conn, $type, $parent_id = null) {
    $units = [];
    $sql = "SELECT * FROM military_units WHERE unit_type = ?";
    $params = [$type];
    
    if ($parent_id) {
        $sql .= " AND parent_id = ?";
        $params[] = $parent_id;
    }

   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH

    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    
    return $units;
}

// Function to get equipment for a unit
function getEquipment($conn, $unit_id) {
    $equipment = [];
    $sql = "SELECT * FROM military_equipment WHERE unit_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $unit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $equipment[] = $row;
    }
    
    return $equipment;
}

// Get KODAM I Bukit Barisan
$kodam = getUnits($conn, 'KODAM');
$kodamId = isset($kodam[0]['id']) ? $kodam[0]['id'] : null;

// Handle AJAX requests directly in this file
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'get_units') {
        $unitType = isset($_GET['unitType']) ? $_GET['unitType'] : null;
        $parentId = isset($_GET['parentId']) ? $_GET['parentId'] : null;
        $units = getUnits($conn, $unitType, $parentId);
        echo json_encode($units);
        exit;
    }
    
    if ($_GET['action'] === 'get_unit_details') {
        $unitId = isset($_GET['unitId']) ? $_GET['unitId'] : null;
        $response = ['unit' => null, 'equipment' => []];
        
        if ($unitId) {
            // Get unit details
            $sql = "SELECT * FROM military_units WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $unitId);
            $stmt->execute();
            $unit = $stmt->get_result()->fetch_assoc();
            
            if ($unit) {
                $response['unit'] = $unit;
                $response['equipment'] = getEquipment($conn, $unitId);
            }
        }
        
        echo json_encode($response);
        exit;
    }
    
    if ($_GET['action'] === 'search_units') {
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        $results = [];
        
        if (!empty($searchTerm)) {
            $sql = "SELECT * FROM military_units WHERE 
                    (name LIKE ? OR description LIKE ?) 
                    AND (unit_type = 'KODIM' OR unit_type = 'KORAMIL')";
            $stmt = $conn->prepare($sql);
            $searchParam = "%$searchTerm%";
            $stmt->bind_param("ss", $searchParam, $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        }
        
        echo json_encode($results);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>Peta Alutsista TNI</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    
    <!-- jQuery and Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!--== Google Fonts ==-->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            padding: 0;
            margin: 0;
        }
        
        #map { 
            height: calc(100vh - 180px);
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .header {
            padding: 15px 0;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        
        .search-container {
            position: absolute;
            top: 80px;
            right: 10px;
            z-index: 999;
            width: 280px;
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        #info-panel {
            position: absolute;
            bottom: 100px;
            right: 20px;
            width: 400px;
            max-height: 500px;
            overflow-y: auto;
            background: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 999;
            display: none;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
            z-index: 999;
        }
        
        .unit-type-btn {
            padding: 8px 16px;
            margin: 0 5px;
            border: 2px solid #d10000;
            background: white;
            color: #d10000;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .unit-type-btn.active {
            background: #d10000;
            color: white;
        }
        
        .unit-header {
            background-color: #d10000;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .equipment-table th, .equipment-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .equipment-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        
        .custom-marker-kodam {
            background-color: #d10000;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .custom-marker-korem {
            background-color: #ff7700;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .custom-marker-kodim {
            background-color: #ffaa00;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .custom-marker-koramil {
            background-color: #ffe100;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .popup-btn {
            margin-top: 8px;
            padding: 5px 10px;
            background: #d10000;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .unit-info {
            margin-top: 5px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h2>Peta Alutsista TNI</h2>
                </div>
                <div class="col-md-6 text-center">
                    <h3>KODAM I BUKIT BARISAN</h3>
                </div>
                <div class="col-md-3 text-end">
                    <a href="index.html" class="btn btn-outline-secondary">Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="search-container">
        <div class="input-group">
            <input type="text" id="search-input" class="form-control" placeholder="Cari unit (KODIM/KORAMIL)...">
            <button class="btn btn-outline-secondary" type="button" id="search-button">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div id="search-results" class="mt-2"></div>
    </div>
    
    <div id="map"></div>
    <div id="info-panel"></div>
    
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <button class="unit-type-btn active" data-type="KODAM">KODAM</button>
                    <button class="unit-type-btn" data-type="KOREM">KOREM</button>
                    <button class="unit-type-btn" data-type="KODIM">KODIM</button>
                    <button class="unit-type-btn" data-type="KORAMIL">KORAMIL</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize map centered at Kodam I Bukit Barisan
        var map = L.map('map').setView([3.5952, 98.6722], 8);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // Set map boundaries for Indonesia/Sumatra
        var sumatraBounds = [
            [-6.0, 95.0], // Southwest coordinates
            [6.0, 106.0]  // Northeast coordinates
        ];
        
        // Restrict map panning
        map.setMaxBounds(sumatraBounds);
        map.setMinZoom(6);
        
        // Track current markers to clear them when switching between unit types
        let currentMarkers = [];
        let currentUnitType = 'KODAM';
        let kodamId = '<?php echo $kodamId; ?>';
        
        // Define custom icons
        function createCustomIcon(unitType) {
            let className = 'custom-marker-' + unitType.toLowerCase();
            let size = unitType === 'KODAM' ? 30 : 
                      unitType === 'KOREM' ? 25 : 
                      unitType === 'KODIM' ? 20 : 15;
                      
            return L.divIcon({
                className: className,
                iconSize: [size, size],
                iconAnchor: [size/2, size/2],
                html: `<div style="width: ${size}px; height: ${size}px;" class="${className}"></div>`
            });
        }
        
        // Function to clear all markers from map
        function clearMarkers() {
            currentMarkers.forEach(marker => map.removeLayer(marker));
            currentMarkers = [];
        }
        
        // Function to show unit details in info panel
        function showUnitDetails(unitId) {
            $.ajax({
                url: 'map.php?action=get_unit_details',
                type: 'GET',
                data: { unitId: unitId },
                dataType: 'json',
                success: function(data) {
                    if (data.unit) {
                        let parentInfo = '';
                        if (data.unit.parent_id) {
                            // Get parent unit name
                            $.ajax({
                                url: 'map.php?action=get_unit_details',
                                type: 'GET',
                                data: { unitId: data.unit.parent_id },
                                dataType: 'json',
                                async: false,
                                success: function(parentData) {
                                    if (parentData.unit) {
                                        parentInfo = `<div class="unit-info">Bagian dari: ${parentData.unit.name}</div>`;
                                    }
                                }
                            });
                        }
                        
                        let content = `
                            <div class="unit-header">
                                <h4>${data.unit.name}</h4>
                                <p>${data.unit.description || ''}</p>
                                ${parentInfo}
                            </div>`;
                        
                        if (data.equipment && data.equipment.length > 0) {
                            content += `
                                <h5>Data Alutsista</h5>
                                <table class="equipment-table">
                                    <thead>
                                        <tr>
                                            <th>Jenis Materil</th>
                                            <th>Sub Materil</th>
                                            <th>Jumlah</th>
                                            <th>Kondisi</th>
                                            <th>Kesiapan</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                                    
                            data.equipment.forEach(item => {
                                content += `
                                    <tr>
                                        <td>${item.jenis_materil}</td>
                                        <td>${item.sub_materil}</td>
                                        <td>${item.jumlah}</td>
                                        <td>${item.kondisi}</td>
                                        <td>${item.kesiapan}%</td>
                                    </tr>`;
                            });
                            
                            content += `
                                    </tbody>
                                </table>`;
                        } else {
                            content += `<p>Tidak ada data alutsista tersedia.</p>`;
                        }
                         // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
                        $('#info-panel').html(content).show();
                    }
                },
                error: function() {
                    $('#info-panel').html('<p>Gagal memuat data unit.</p>').show();
                }
            });
        }
        
        // Function to load and display markers for a specific unit type
        function loadUnitMarkers(unitType, parentId = null) {
            clearMarkers();
            currentUnitType = unitType;
            
            $.ajax({
                url: 'map.php?action=get_units',
                type: 'GET',
                data: { 
                    unitType: unitType,
                    parentId: parentId 
                },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        data.forEach(unit => {
                            const icon = createCustomIcon(unitType);
                            const marker = L.marker([unit.latitude, unit.longitude], {icon: icon})
                                .bindPopup(`
                                    <div class="popup-content">
                                        <h4>${unit.name}</h4>
                                        <p>${unit.description || ""}</p>
                                        <button onclick="showUnitDetails('${unit.id}')" class="popup-btn">
                                            Lihat Data Alutsista
                                        </button>
                                    </div>
                                `);
                            marker.addTo(map);
                            currentMarkers.push(marker);
                        });
                        
                        // Fit map to these markers
                        if (currentMarkers.length > 0) {
                            const group = new L.featureGroup(currentMarkers);
                            map.fitBounds(group.getBounds());
                        }
                    } else {
                        console.log(`Tidak ada ${unitType} yang ditemukan${parentId ? ' untuk unit induk yang dipilih' : ''}.`);
                    }
                },
                error: function() {
                    console.error('Gagal memuat data unit dari server.');
                }
            });
        }
        
        $(document).ready(function() {
            // Initialize with KODAM I Bukit Barisan
            loadUnitMarkers('KODAM');
            
            // Handle unit type button clicks
            $('.unit-type-btn').click(function() {
                $('.unit-type-btn').removeClass('active');
                $(this).addClass('active');
                
                const unitType = $(this).data('type');
                
                if (unitType === 'KODAM') {
                    loadUnitMarkers('KODAM');
                } else if (unitType === 'KOREM') {
                    loadUnitMarkers('KOREM', kodamId);
                } else if (unitType === 'KODIM') {
                    // First get all KOREMs under this KODAM
                    $.ajax({
                        url: 'map.php?action=get_units',
                        type: 'GET',
                        data: { 
                            unitType: 'KOREM',
                            parentId: kodamId
                        },
                        dataType: 'json',
                        success: function(korems) {
                            clearMarkers();
                            
                            // For each KOREM, get its KODIMs
                            let promises = [];
                            
                            korems.forEach(korem => {
                                let promise = $.ajax({
                                    url: 'map.php?action=get_units',
                                    type: 'GET',
                                    data: { 
                                        unitType: 'KODIM',
                                        parentId: korem.id
                                    },
                                    dataType: 'json'
                                });
                                promises.push(promise);
                            });
                             // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
   // MADE BY RIZQULLAH
                            // When all promises resolve, add markers
                            Promise.all(promises).then(results => {
                                results.forEach(kodims => {
                                    if (kodims && kodims.length > 0) {
                                        kodims.forEach(unit => {
                                            const icon = createCustomIcon('KODIM');
                                            const marker = L.marker([unit.latitude, unit.longitude], {icon: icon})
                                                .bindPopup(`
                                                    <div class="popup-content">
                                                        <h4>${unit.name}</h4>
                                                        <p>${unit.description || ""}</p>
                                                        <button onclick="showUnitDetails('${unit.id}')" class="popup-btn">
                                                            Lihat Data Alutsista
                                                        </button>
                                                    </div>
                                                `);
                                            marker.addTo(map);
                                            currentMarkers.push(marker);
                                        });
                                    }
                                });
                                
                                // Fit map to these markers
                                if (currentMarkers.length > 0) {
                                    const group = new L.featureGroup(currentMarkers);
                                    map.fitBounds(group.getBounds());
                                }
                            });
                        }
                    });
                } else if (unitType === 'KORAMIL') {
                    // First get all KOREMs under this KODAM
                    $.ajax({
                        url: 'map.php?action=get_units',
                        type: 'GET',
                        data: { 
                            unitType: 'KOREM',
                            parentId: kodamId
                        },
                        dataType: 'json',
                        success: function(korems) {
                            clearMarkers();
                            
                            // For each KOREM, get its KODIMs
                            korems.forEach(korem => {
                                $.ajax({
                                    url: 'map.php?action=get_units',
                                    type: 'GET',
                                    data: { 
                                        unitType: 'KODIM',
                                        parentId: korem.id
                                    },
                                    dataType: 'json',
                                    success: function(kodims) {
                                        // For each KODIM, get its KORAMILs
                                        kodims.forEach(kodim => {
                                            $.ajax({
                                                url: 'map.php?action=get_units',
                                                type: 'GET',
                                                data: { 
                                                    unitType: 'KORAMIL',
                                                    parentId: kodim.id
                                                },
                                                dataType: 'json',
                                                success: function(koramils) {
                                                    if (koramils && koramils.length > 0) {
                                                        koramils.forEach(unit => {
                                                            const icon = createCustomIcon('KORAMIL');
                                                            const marker = L.marker([unit.latitude, unit.longitude], {icon: icon})
                                                                .bindPopup(`
                                                                    <div class="popup-content">
                                                                        <h4>${unit.name}</h4>
                                                                        <p>${unit.description || ""}</p>
                                                                        <button onclick="showUnitDetails('${unit.id}')" class="popup-btn">
                                                                            Lihat Data Alutsista
                                                                        </button>
                                                                    </div>
                                                                `);
                                                            marker.addTo(map);
                                                            currentMarkers.push(marker);
                                                        });
                                                        
                                                        // Fit map to these markers
                                                        if (currentMarkers.length > 0) {
                                                            const group = new L.featureGroup(currentMarkers);
                                                            map.fitBounds(group.getBounds());
                                                        }
                                                    }
                                                }
                                            });
                                        });
                                    }
                                });
                            });
                        }
                    });
                }
            });
            
            // Handle search button click
            $('#search-button').click(function() {
                const searchTerm = $('#search-input').val();
                if (searchTerm.trim() !== '') {
                    $.ajax({
                        url: 'map.php?action=search_units',
                        type: 'GET',
                        data: { search: searchTerm },
                        dataType: 'json',
                        success: function(data) {
                            let resultsHtml = '';
                            
                            if (data && data.length > 0) {
                                resultsHtml = '<ul class="list-group">';
                                data.forEach(unit => {
                                    resultsHtml += `
                                        <li class="list-group-item">
                                            <a href="#" class="search-result-item" 
                                               data-id="${unit.id}" 
                                               data-lat="${unit.latitude}" 
                                               data-lon="${unit.longitude}"
                                               data-type="${unit.unit_type}">
                                                ${unit.name}
                                            </a>
                                        </li>`;
                                });
                                resultsHtml += '</ul>';
                            } else {
                                resultsHtml = '<p>Tidak ada hasil yang ditemukan.</p>';
                            }
                            
                            $('#search-results').html(resultsHtml);
                        },
                        error: function() {
                            $('#search-results').html('<p>Gagal melakukan pencarian.</p>');
                        }
                    });
                }
            });
            
            // Handle search input enter key press
            $('#search-input').keypress(function(e) {
                if (e.which === 13) {
                    $('#search-button').click();
                    return false;
                }
            });
            
            // Handle search result item click
            $(document).on('click', '.search-result-item', function(e) {
                e.preventDefault();
                const unitId = $(this).data('id');
                const lat = $(this).data('lat');
                const lon = $(this).data('lon');
                const unitType = $(this).data('type');
                
                // Center map on the selected unit
                map.setView([lat, lon], 12);
                
                // Clear current markers and show only the selected unit
                clearMarkers();
                
                // Create a marker for the selected unit
                const icon = createCustomIcon(unitType);
                const marker = L.marker([lat, lon], {icon: icon})
                    .bindPopup(`
                        <div class="popup-content">
                            <h4>${$(this).text()}</h4>
                            <button onclick="showUnitDetails('${unitId}')" class="popup-btn">
                                Lihat Data Alutsista
                            </button>
                        </div>
                    `);
                marker.addTo(map);
                currentMarkers.push(marker);
                
                // Open the popup
                marker.openPopup();
                
                // Show unit details
                showUnitDetails(unitId);
                
                // Clear search results
                $('#search-results').html('');
                $('#search-input').val('');
            });
            
            // Close info panel when clicking outside of it
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#info-panel, .popup-btn').length) {
                    $('#info-panel').hide();
                }
            });
        });
    </script>
</body>
</html>