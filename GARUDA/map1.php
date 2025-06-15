<?php
// filepath: c:\xampp\htdocs\PPL-ALPAHANKAM\GARUDA\map.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>Peta Satuan Kewilayahan TNI</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map { 
            height: calc(100vh - 120px); /* Adjust for header and footer */
            width: 100%;
            position: relative;
            z-index: 1;
        }
    </style>

    <!--== Favicon ==-->
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon" />

    <!--== Google Fonts ==-->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,300;1,400&display=swap" rel="stylesheet">

    <!-- build:css assets/css/app.min.css -->
    <!--== jqvmap Min CSS ==-->
    <link href="assets/css/jqvmap.min.css" rel="stylesheet" />
    <!--== ChartJS Min CSS ==-->
    <link href="assets/css/chart.min.css" rel="stylesheet" />
    <!--== DataTables Min CSS ==-->
    <link href="assets/css/datatables.min.css" rel="stylesheet" />
    <!--== Select2 Min CSS ==-->
    <link href="assets/css/select2.min.css" rel="stylesheet" />
    <!--== Bootstrap Min CSS ==-->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />

    <!--== Main Style CSS ==-->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- endbuild -->

    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <header class="header-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3">
                    <div class="navbar-wrap">
                        <nav class="menubar">
                            <ul class="nav">
                                <li><a href="index.html" class="nav-link">SATPUR TNI AD</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="logo-wrap text-center">
                        <a href="index.html" class="d-flex justify-content-center">
                            <img src="assets/img/logo.png" alt="GARUDA" class="img-fluid" />
                        </a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="navbar-wrap">
                        <nav class="menubar">
                            <ul class="nav justify-content-end">
                                <li><a href="map.php" class="nav-link">SATWIL TNI </a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <style>
        #info {
            position: absolute;
            top: 80px;
            right: 10px;
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
            max-width: 300px;
        }
    </style>
        <style>
            #map { height: 900px; width: 100%; }
    
            /* Add these styles for the military force buttons */
            .military-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.9);
                padding: 10px 0;
                z-index: 1000;
                border-top: 2px solid #ccc;
            }
    
            .btn-military {
                display: block;
                width: 100%;
                padding: 10px;
                border: none;
                background: #f1f1f1;
                color: #333;
                font-weight: bold;
                text-transform: uppercase;
                cursor: pointer;
                transition: all 0.3s;
            }
    
            .btn-military.active {
                background: #27c400cf;
                color: white;
            }
    
            .btn-military[data-force="AL"].active {
                background: #020c5d;
            }
    
            .btn-military[data-force="AU"].active {
                background: #0066cc;
            }
    
            .popup-content {
                padding: 10px;
            }
    
            .popup-btn {
                margin-top: 10px;
                padding: 5px 10px;
                background: #d10000;
                color: white;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            }
        </style>
    <div id="map"></div>
    <div id="info"></div>

    <script>
        // ... Seluruh kode JavaScript dari map.html Anda di sini ...
        // (copy-paste seluruh <script>...</script> dari map.html ke sini)
        // Tidak perlu diubah, kecuali jika ingin menambah PHP di masa depan.
    </script>

<footer class="military-footer">
    <div class="container">
        <div class="row justify-content-center main-buttons">
            <div class="col-md-4">
                <button class="btn-military active" data-force="AD">TNI AD</button>
            </div>
            <div class="col-md-4">
                <button class="btn-military" data-force="AL">TNI AL</button>
            </div>
            <div class="col-md-4">
                <button class="btn-military" data-force="AU">TNI AU</button>
            </div>
        </div>
        <div class="row justify-content-center sub-buttons" id="adSubButtons" style="display: none; margin-top: 10px;">
            <div class="col-md-3">
                <button class="btn-military-sub active" data-type="KODAM">KODAM</button>
            </div>
            <div class="col-md-2">
                <button class="btn-military-sub" data-type="KOSTRAD">KOSTRAD</button>
            </div>
            <div class="col-md-3">
                <button class="btn-military-sub" data-type="KOREM">KOREM</button>
            </div>
            <div class="col-md-3">
                <button class="btn-military-sub" data-type="KODIM">KODIM</button>
            </div>
            <div class="col-md-3">
                <button class="btn-military-sub" data-type="KORAMIL">KORAMIL</button>
            </div>
        </div>
    </div>
</footer>
</body>

</html>