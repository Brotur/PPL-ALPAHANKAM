<?php
// filepath: c:\xampp\htdocs\PPL-ALPAHANKAM\GARUDA\map.php

// ==== DATA DUMMY (tanpa database) ====
// Contoh data Kodam, Korem, Kodim, Koramil, dan alutsista
$units = [
    [
        'id' => 'kodam1',
        'name' => 'KODAM I Bukit Barisan',
        'unit_type' => 'KODAM',
        'latitude' => 3.5952,
        'longitude' => 98.6722,
        'description' => 'Kodam I Bukit Barisan, Medan',
        'parent_id' => null
    ],
    [
        'id' => 'korem1',
        'name' => 'KOREM 022/PT',
        'unit_type' => 'KOREM',
        'latitude' => 2.9667,
        'longitude' => 99.0667,
        'description' => 'Korem 022/PT, Pematangsiantar',
        'parent_id' => 'kodam1'
    ],
    [
        'id' => 'kodim1',
        'name' => 'KODIM 0207/SML',
        'unit_type' => 'KODIM',
        'latitude' => 2.9586,
        'longitude' => 99.0682,
        'description' => 'Kodim 0207/SML, Simalungun',
        'parent_id' => 'korem1'
    ],
    [
        'id' => 'koramil1',
        'name' => 'KORAMIL 01/SML',
        'unit_type' => 'KORAMIL',
        'latitude' => 2.9500,
        'longitude' => 99.0700,
        'description' => 'Koramil 01/SML, Simalungun',
        'parent_id' => 'kodim1'
    ]
];

$equipment = [
    [
        'unit_id' => 'kodim1',
        'jenis_materil' => 'Senjata',
        'sub_materil' => 'Senjata Infantri Perorangan',
        'jumlah' => 100,
        'kondisi' => 'Baik',
        'kesiapan' => 95
    ],
    [
        'unit_id' => 'kodim1',
        'jenis_materil' => 'Ranmor',
        'sub_materil' => 'Truk',
        'jumlah' => 10,
        'kondisi' => 'Baik',
        'kesiapan' => 90
    ],
    [
        'unit_id' => 'koramil1',
        'jenis_materil' => 'Senjata',
        'sub_materil' => 'Senjata Infantri Perorangan',
        'jumlah' => 30,
        'kondisi' => 'Baik',
        'kesiapan' => 90
    ]
];

// ==== FUNGSI DUMMY ====
function getUnits($type, $parent_id = null) {
    global $units;
    $result = [];
    foreach ($units as $u) {
        if ($u['unit_type'] === $type && ($parent_id === null ? $u['parent_id'] === null : $u['parent_id'] === $parent_id)) {
            $result[] = $u;
        }
    }
    return $result;
}
function getEquipment($unit_id) {
    global $equipment;
    $result = [];
    foreach ($equipment as $e) {
        if ($e['unit_id'] === $unit_id) $result[] = $e;
    }
    return $result;
}
function getUnitById($id) {
    global $units;
    foreach ($units as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}

// ==== HANDLE AJAX ====
$kodam = getUnits('KODAM');
$kodamId = isset($kodam[0]['id']) ? $kodam[0]['id'] : null;

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'get_units') {
        $unitType = $_GET['unitType'] ?? null;
        $parentId = $_GET['parentId'] ?? null;
        echo json_encode(getUnits($unitType, $parentId));
        exit;
    }
    if ($_GET['action'] === 'get_unit_details') {
        $unitId = $_GET['unitId'] ?? null;
        $unit = $unitId ? getUnitById($unitId) : null;
        $equip = $unitId ? getEquipment($unitId) : [];
        echo json_encode(['unit' => $unit, 'equipment' => $equip]);
        exit;
    }
    if ($_GET['action'] === 'search_units') {
        $searchTerm = strtolower($_GET['search'] ?? '');
        $results = [];
        foreach ($units as $u) {
            if (
                in_array($u['unit_type'], ['KODIM', 'KORAMIL']) &&
                (strpos(strtolower($u['name']), $searchTerm) !== false ||
                 strpos(strtolower($u['description']), $searchTerm) !== false)
            ) {
                $results[] = $u;
            }
        }
        echo json_encode($results);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Alutsista TNI</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; margin: 0; }
        #map { height: calc(100vh - 180px); width: 100%; }
        .header { padding: 15px 0; background: #f8f9fa; border-bottom: 1px solid #ddd; }
        .search-container {
            position: absolute; top: 80px; right: 10px; z-index: 999; width: 280px;
            background: white; padding: 10px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        #info-panel {
            position: absolute; bottom: 100px; right: 20px; width: 400px; max-height: 500px; overflow-y: auto;
            background: white; padding: 15px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 999; display: none;
        }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1); padding: 10px 0; z-index: 999;
        }
        .unit-type-btn {
            padding: 8px 16px; margin: 0 5px; border: 2px solid #d10000; background: white; color: #d10000;
            font-weight: 600; border-radius: 4px; cursor: pointer; transition: all 0.3s ease;
        }
        .unit-type-btn.active { background: #d10000; color: white; }
        .unit-header { background: #d10000; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .equipment-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .equipment-table th, .equipment-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .equipment-table th { background: #f2f2f2; font-weight: 600; }
        .custom-marker-kodam { background: #d10000; border-radius: 50%; border: 2px solid white; }
        .custom-marker-korem { background: #ff7700; border-radius: 50%; border: 2px solid white; }
        .custom-marker-kodim { background: #ffaa00; border-radius: 50%; border: 2px solid white; }
        .custom-marker-koramil { background: #ffe100; border-radius: 50%; border: 2px solid white; }
        .popup-btn { margin-top: 8px; padding: 5px 10px; background: #d10000; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .unit-info { margin-top: 5px; font-style: italic; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3"><h2>Peta Alutsista TNI</h2></div>
                <div class="col-md-6 text-center"><h3>KODAM I BUKIT BARISAN</h3></div>
                <div class="col-md-3 text-end"><a href="index.html" class="btn btn-outline-secondary">Dashboard</a></div>
            </div>
        </div>
    </div>
    <div class="search-container">
        <div class="input-group">
            <input type="text" id="search-input" class="form-control" placeholder="Cari unit (KODIM/KORAMIL)...">
            <button class="btn btn-outline-secondary" type="button" id="search-button"><i class="fas fa-search"></i></button>
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
    var map = L.map('map').setView([3.5952, 98.6722], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    map.setMaxBounds([[-6.0, 95.0], [6.0, 106.0]]);
    map.setMinZoom(6);

    let currentMarkers = [];
    let currentUnitType = 'KODAM';
    let kodamId = '<?= $kodamId ?>';

    function createCustomIcon(unitType) {
        let className = 'custom-marker-' + unitType.toLowerCase();
        let size = unitType === 'KODAM' ? 30 : unitType === 'KOREM' ? 25 : unitType === 'KODIM' ? 20 : 15;
        return L.divIcon({
            className: className,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2],
            html: `<div style="width: ${size}px; height: ${size}px;" class="${className}"></div>`
        });
    }
    function clearMarkers() {
        currentMarkers.forEach(marker => map.removeLayer(marker));
        currentMarkers = [];
    }
    function showUnitDetails(unitId) {
        $.get('map.php?action=get_unit_details', { unitId: unitId }, function(data) {
            if (data.unit) {
                let parentInfo = '';
                if (data.unit.parent_id) {
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
                    content += `</tbody></table>`;
                } else {
                    content += `<p>Tidak ada data alutsista tersedia.</p>`;
                }
                $('#info-panel').html(content).show();
            }
        }, 'json').fail(function() {
            $('#info-panel').html('<p>Gagal memuat data unit.</p>').show();
        });
    }
    function loadUnitMarkers(unitType, parentId = null) {
        clearMarkers();
        currentUnitType = unitType;
        $.get('map.php?action=get_units', { unitType: unitType, parentId: parentId }, function(data) {
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
                if (currentMarkers.length > 0) {
                    const group = new L.featureGroup(currentMarkers);
                    map.fitBounds(group.getBounds());
                }
            }
        }, 'json');
    }
    $(document).ready(function() {
        loadUnitMarkers('KODAM');
        $('.unit-type-btn').click(function() {
            $('.unit-type-btn').removeClass('active');
            $(this).addClass('active');
            const unitType = $(this).data('type');
            if (unitType === 'KODAM') {
                loadUnitMarkers('KODAM');
            } else if (unitType === 'KOREM') {
                loadUnitMarkers('KOREM', kodamId);
            } else if (unitType === 'KODIM') {
                $.get('map.php?action=get_units', { unitType: 'KOREM', parentId: kodamId }, function(korems) {
                    clearMarkers();
                    let promises = korems.map(korem =>
                        $.get('map.php?action=get_units', { unitType: 'KODIM', parentId: korem.id })
                    );
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
                        if (currentMarkers.length > 0) {
                            const group = new L.featureGroup(currentMarkers);
                            map.fitBounds(group.getBounds());
                        }
                    });
                }, 'json');
            } else if (unitType === 'KORAMIL') {
                $.get('map.php?action=get_units', { unitType: 'KOREM', parentId: kodamId }, function(korems) {
                    clearMarkers();
                    korems.forEach(korem => {
                        $.get('map.php?action=get_units', { unitType: 'KODIM', parentId: korem.id }, function(kodims) {
                            kodims.forEach(kodim => {
                                $.get('map.php?action=get_units', { unitType: 'KORAMIL', parentId: kodim.id }, function(koramils) {
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
                                        if (currentMarkers.length > 0) {
                                            const group = new L.featureGroup(currentMarkers);
                                            map.fitBounds(group.getBounds());
                                        }
                                    }
                                }, 'json');
                            });
                        }, 'json');
                    });
                }, 'json');
            }
        });
        $('#search-button').click(function() {
            const searchTerm = $('#search-input').val();
            if (searchTerm.trim() !== '') {
                $.get('map.php?action=search_units', { search: searchTerm }, function(data) {
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
                }, 'json');
            }
        });
        $('#search-input').keypress(function(e) {
            if (e.which === 13) {
                $('#search-button').click();
                return false;
            }
        });
        $(document).on('click', '.search-result-item', function(e) {
            e.preventDefault();
            const unitId = $(this).data('id');
            const lat = $(this).data('lat');
            const lon = $(this).data('lon');
            const unitType = $(this).data('type');
            map.setView([lat, lon], 12);
            clearMarkers();
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
            marker.openPopup();
            showUnitDetails(unitId);
            $('#search-results').html('');
            $('#search-input').val('');
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#info-panel, .popup-btn').length) {
                $('#info-panel').hide();
            }
        });
    });
    </script>
</body>
</html>