<?php
include 'functions.php';

$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Handle file upload validation via AJAX
if (!empty($_POST) && isset($_POST['upload_file'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    if (!isset($_FILES['gcode_file']) || $_FILES['gcode_file']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geen geldig bestand geüpload!']);
        exit;
    }

    $file = $_FILES['gcode_file'];
    $file_name = sanitize_input($file['name']);
    if (pathinfo($file_name, PATHINFO_EXTENSION) !== 'gcode') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Alleen .gcode-bestanden zijn toegestaan!']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'file_name' => $file_name]);
    exit;
}

?>

<?=template_header('Printer Beheer')?>

<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="images/zintuigspel_logo.svg" alt="Zintuigspel Logo" class="sidebar-logo">
            <i class='bx bx-menu toggle-btn'></i>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class='bx bx-home'></i><span>Overzicht</span></a></li>
            <li><a href="create.php?page=<?=$page?>"><i class='bx bx-plus'></i><span>Nieuwe Calculatie</span></a></li>
            <li><a href="filaments.php?page=<?=$page?>"><i class='bx bx-color-fill'></i><span>Filamenten</span></a></li>
            <li><a href="costs.php?page=<?=$page?>"><i class='bx bx-dollar'></i><span>Kosten</span></a></li>
            <li><a href="printer.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'printer.php' ? 'active' : '' ?>"><i class='bx bx-printer'></i><span>Printer Beheer</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content update">
            <div class="create-article">
                <h2>Bambu Lab A1 Combo Beheer</h2>
            </div>

            <!-- Printer Status Section -->
            <div class="form-section">
                <h3>Printer Status</h3>
                <p>Status: <span id="printer-status">Onbekend</span></p>
                <p>Bed Temperatuur: <span id="bed-temp">N/A</span> °C</p>
                <p>Nozzle Temperatuur: <span id="nozzle-temp">N/A</span> °C</p>
                <p>Voortgang: <span id="progress">0%</span></p>
            </div>

            <!-- File Upload Section -->
            <form action="printer.php?page=<?=$page?>" method="post" enctype="multipart/form-data" id="printerForm">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                <input type="hidden" name="upload_file" value="1">
                <div class="form-section">
                    <h3>Bestand Verzenden</h3>
                    <label for="gcode_file">Kies een .gcode-bestand *</label>
                    <input type="file" class="big" id="gcode_file" name="gcode_file" accept=".gcode" required>
                    <div class="button-span">
                        <input type="submit" value="Verzenden">
                        <a href="index.php?page=<?=$page?>" class="back">Terug</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
<script>
    // Pass page-specific data to main.js
    const printerConfig = {
        page: '<?=$page?>',
        printerIp: '192.168.1.xxx', // Replace with your printer’s LAN IP
        accessCode: 'your_access_code',
        deviceSerial: 'your_device_serial'
    };
</script>

<?=template_footer()?>