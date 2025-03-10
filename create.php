<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

if (!empty($_POST)) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("create.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $data = sanitize_input($_POST);
    $defaults = [
        'artikelnaam' => '',
        'aangemaakt' => date('Y-m-d'),
        'gewicht' => 0,
        'printprijs' => 0.0,
        'verkoopprijs' => 0.0,
        'printtijd' => 0,
        'idnummer2' => '', 'idnummer3' => '', 'idnummer4' => '', 'idnummer5' => '',
        'idnummer6' => '', 'idnummer7' => '', 'idnummer8' => '',
        'orderaantal' => 0,
        'aantal_afwijkend' => 0,
        'geconstateerde_afwijking' => ''
    ];
    $data = array_merge($defaults, $data);
    $required = ['artikelnaam'];

    if (!validate_required($data, $required) || $data['gewicht'] < 0 || $data['printtijd'] < 0) {
        redirect_with_message("create.php?page=$page", 'Vul alle verplichte velden in en zorg dat gewicht en printtijd niet negatief zijn!', 'error');
    }

    $stmt = $pdo->prepare('INSERT INTO productprintcosts (artikelnaam, aangemaakt, gewicht, printprijs, verkoopprijs, printtijd, idnummer2, idnummer3, idnummer4, idnummer5, idnummer6, idnummer7, idnummer8, orderaantal, aantal_afwijkend, geconstateerde_afwijking) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $data['artikelnaam'], $data['aangemaakt'], (int)$data['gewicht'], (float)$data['printprijs'],
        (float)$data['verkoopprijs'], (int)$data['printtijd'], $data['idnummer2'], $data['idnummer3'],
        $data['idnummer4'], $data['idnummer5'], $data['idnummer6'], $data['idnummer7'], $data['idnummer8'],
        (int)$data['orderaantal'], (int)$data['aantal_afwijkend'], $data['geconstateerde_afwijking']
    ]);
    redirect_with_message("index.php?page=$page", 'Artikel succesvol toegevoegd!');
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
            </div>
        </div>
        <h2>Nieuwe Calculatie</h2>
    </div>
    <?=get_flash_message()?>
    <form action="create.php?page=<?=$page?>" method="post" id="createForm">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
        <section>
            <div class="form-section">
                <div>
                    <label class="jumbo">Artikelnaam *</label>
                    <input type="text" class="jumbo" name="artikelnaam" id="artikelnaam" required>
                    <input type="hidden" name="aangemaakt" value="<?=date('Y-m-d')?>">
                </div>
                <div class="form-row">
                    <label class="big">Gewicht (gram)</label>
                    <label class="big">Printtijd (minuten)</label>
                </div>
                <div class="form-row">
                    <input type="number" class="big" name="gewicht" id="gewicht" min="0">
                    <input type="number" class="big" name="printtijd" id="printtijd" min="0">
                </div>
                <div class="form-row">
                    <label class="big">Printprijs (€)</label>
                    <label class="big">Verkoopprijs (€)</label>
                </div>
                <div class="form-row">
                    <input type="number" class="big" name="printprijs" id="printprijs" step="0.01" min="0">
                    <input type="number" class="big" name="verkoopprijs" id="verkoopprijs" step="0.01" min="0">
                </div>
                <div>
                    <label>ID-nummers</label>
                </div>
                <div class="idnummersGrid">
                    <input type="text" placeholder="000" name="idnummer2" id="idnummer2">
                    <input type="text" placeholder="000" name="idnummer3" id="idnummer3">
                    <input type="text" placeholder="000" name="idnummer4" id="idnummer4">
                    <input type="text" placeholder="000" name="idnummer5" id="idnummer5">
                    <input type="text" placeholder="000" name="idnummer6" id="idnummer6">
                    <input type="text" placeholder="000" name="idnummer7" id="idnummer7">
                    <input type="text" placeholder="000" name="idnummer8" id="idnummer8">
                </div>
                <div class="form-row">
                    <label>Orderaantal</label>
                    <label>Aantal afwijkend</label>
                </div>
                <div class="form-row">
                    <select name="orderaantal" id="orderaantal">
                        <option value="" disabled selected>Selecteer</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?=$i?>"><?=$i?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="aantal_afwijkend" id="aantal_afwijkend">
                        <option value="" disabled selected>Selecteer</option>
                        <?php for ($i = 0; $i <= 8; $i++): ?>
                            <option value="<?=$i?>"><?=$i?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="jumbo">Geconstateerde afwijking</label>
                    <textarea name="geconstateerde_afwijking" id="geconstateerde_afwijking" rows="6"></textarea>
                </div>
                <div class="button-span">
                    <input type="submit" value="Opslaan">
                    <a href="index.php?page=<?=$page?>" class="back">Terug</a>
                </div>
            </div>
        </section>
    </form>
</div>

<?=template_footer()?>