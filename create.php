<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$stmt = $pdo->query('SELECT * FROM filaments');
$filaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($_POST)) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    $data = sanitize_input($_POST);
    $defaults = [
        'artikelnaam' => '', 'gewicht' => 0, 'printtijd' => 0, 'filament' => 0, 'orderaantal' => 0,
        'aantal_afwijkend' => 0, 'geconstateerde_afwijking' => '', 'idnummer2' => '', 'idnummer3' => '',
        'idnummer4' => '', 'idnummer5' => '', 'idnummer6' => '', 'idnummer7' => '', 'idnummer8' => ''
    ];
    $data = array_merge($defaults, $data);
    $required = ['artikelnaam', 'filament'];

    if (!validate_required($data, $required) || $data['gewicht'] < 0 || $data['printtijd'] < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in en zorg dat gewicht en printtijd niet negatief zijn!']);
        exit;
    }

    $filament = array_filter($filaments, fn($f) => $f['id'] == $data['filament'])[array_key_first(array_filter($filaments, fn($f) => $f['id'] == $data['filament']))];
    $printprijs = ($data['gewicht'] * $filament['price_per_gram']) + ($data['printtijd'] * 0.03);
    $verkoopprijs = $printprijs * 1.3;

    $stmt = $pdo->prepare('INSERT INTO productprintcosts (artikelnaam, gewicht, printtijd, printprijs, verkoopprijs, filament_id, orderaantal, aantal_afwijkend, geconstateerde_afwijking, idnummer2, idnummer3, idnummer4, idnummer5, idnummer6, idnummer7, idnummer8) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $data['artikelnaam'], (int)$data['gewicht'], (int)$data['printtijd'], $printprijs, $verkoopprijs, (int)$data['filament'],
        (int)$data['orderaantal'], (int)$data['aantal_afwijkend'], $data['geconstateerde_afwijking'],
        $data['idnummer2'], $data['idnummer3'], $data['idnummer4'], $data['idnummer5'], $data['idnummer6'], $data['idnummer7'], $data['idnummer8']
    ]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Calculatie succesvol toegevoegd!']);
    exit;
}
?>

<?=template_header('Nieuwe Calculatie')?>

<div class="nav"></div>
<div class="content update">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
                <a href="costs.php?page=<?=$page?>">Kosten</a>
                <a href="printer.php?page=<?=$page?>">Printer Beheer</a>
            </div>
        </div>
        <h2>Nieuwe Calculatie</h2>
    </div>
    <form action="create.php?page=<?=$page?>" method="post" id="createForm">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
        <div class="form-section">
            <div>
                <label for="artikelnaam">Artikelnaam *</label>
                <input type="text" class="big" id="artikelnaam" name="artikelnaam" placeholder="Bijv. Sleutelhanger" required>
            </div>
            <div class="form-row">
                <div>
                    <label for="gewicht">Gewicht (g)</label>
                    <input type="number" class="big" id="gewicht" name="gewicht" min="0" placeholder="Bijv. 20">
                </div>
                <div>
                    <label for="printtijd">Printtijd (min)</label>
                    <input type="number" class="big" id="printtijd" name="printtijd" min="0" placeholder="Bijv. 60">
                </div>
            </div>
            <div>
                <label for="filament">Filament *</label>
                <select class="big" id="filament" name="filament" required>
                    <option value="" disabled selected>Kies een filament</option>
                    <?php foreach ($filaments as $filament): ?>
                        <option value="<?=$filament['id']?>"><?=htmlspecialchars($filament['brand'] . ' ' . $filament['name'] . ' (' . $filament['type'] . ')')?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>ID-nummers</label>
                <div class="idnummersGrid">
                    <input type="text" class="big" id="idnummer2" name="idnummer2" placeholder="000">
                    <input type="text" class="big" id="idnummer3" name="idnummer3" placeholder="000">
                    <input type="text" class="big" id="idnummer4" name="idnummer4" placeholder="000">
                    <input type="text" class="big" id="idnummer5" name="idnummer5" placeholder="000">
                    <input type="text" class="big" id="idnummer6" name="idnummer6" placeholder="000">
                    <input type="text" class="big" id="idnummer7" name="idnummer7" placeholder="000">
                    <input type="text" class="big" id="idnummer8" name="idnummer8" placeholder="000">
                </div>
            </div>
            <div class="form-row">
                <div>
                    <label for="orderaantal">Orderaantal</label>
                    <select class="big" id="orderaantal" name="orderaantal">
                        <option value="" disabled selected>Selecteer</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?=$i?>"><?=$i?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="aantal_afwijkend">Aantal afwijkend</label>
                    <select class="big" id="aantal_afwijkend" name="aantal_afwijkend">
                        <option value="" disabled selected>Selecteer</option>
                        <?php for ($i = 0; $i <= 8; $i++): ?>
                            <option value="<?=$i?>"><?=$i?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div>
                <label for="geconstateerde_afwijking">Geconstateerde afwijking</label>
                <textarea class="big" id="geconstateerde_afwijking" name="geconstateerde_afwijking" rows="4" placeholder="Bijv. Kleine kras"></textarea>
            </div>
            <div class="button-span">
                <input type="submit" value="Toevoegen">
                <a href="index.php?page=<?=$page?>" class="back">Terug</a>
            </div>
        </div>
    </form>
</div>

<?=template_footer()?>