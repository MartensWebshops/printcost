<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';
// Get the page via GET request (URL param: page), if none exists default to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Check if the checklist id exists
if (isset($_GET['id'])) {
    if (!empty($_POST)) {
        // This part updates the record
        $id = isset($_POST['id']) ? $_POST['id'] : NULL;
        $artikelnaam = isset($_POST['artikelnaam']) ? $_POST['artikelnaam'] : '';
        // Convert aangemaakt from d-m-Y to Y-m-d for MySQL
        $aangemaakt_input = isset($_POST['aangemaakt']) ? $_POST['aangemaakt'] : date('d-m-Y');
        $aangemaakt = DateTime::createFromFormat('d-m-Y', $aangemaakt_input) 
            ? DateTime::createFromFormat('d-m-Y', $aangemaakt_input)->format('Y-m-d') 
            : date('Y-m-d'); // Fallback to today if invalid
        $gewicht = isset($_POST['gewicht']) ? $_POST['gewicht'] : '';
        $printprijs = isset($_POST['printprijs']) ? $_POST['printprijs'] : '';
        $verkoopprijs = isset($_POST['verkoopprijs']) ? $_POST['verkoopprijs'] : '';
        $printtijd = isset($_POST['printtijd']) ? $_POST['printtijd'] : '';
        $idnummer2 = isset($_POST['idnummer2']) ? $_POST['idnummer2'] : '';
        $idnummer3 = isset($_POST['idnummer3']) ? $_POST['idnummer3'] : '';
        $idnummer4 = isset($_POST['idnummer4']) ? $_POST['idnummer4'] : '';
        $idnummer5 = isset($_POST['idnummer5']) ? $_POST['idnummer5'] : '';
        $idnummer6 = isset($_POST['idnummer6']) ? $_POST['idnummer6'] : '';
        $idnummer7 = isset($_POST['idnummer7']) ? $_POST['idnummer7'] : '';
        $idnummer8 = isset($_POST['idnummer8']) ? $_POST['idnummer8'] : '';
        $orderaantal = isset($_POST['orderaantal']) ? $_POST['orderaantal'] : '';
        $aantal_afwijkend = isset($_POST['aantal_afwijkend']) ? $_POST['aantal_afwijkend'] : '';
        $geconstateerde_afwijking = isset($_POST['geconstateerde_afwijking']) ? $_POST['geconstateerde_afwijking'] : '';

        // Update the record
        $stmt = $pdo->prepare('UPDATE productprintcosts SET artikelnaam = ?, aangemaakt = ?, gewicht = ?, printprijs = ?, verkoopprijs = ?, printtijd = ?, idnummer2 = ?, idnummer3 = ?, idnummer4 = ?, idnummer5 = ?, idnummer6 = ?, idnummer7 = ?, idnummer8 = ?, orderaantal = ?, aantal_afwijkend = ?, geconstateerde_afwijking = ? WHERE id = ?');
        $stmt->execute([$artikelnaam, $aangemaakt, $gewicht, $printprijs, $verkoopprijs, $printtijd, $idnummer2, $idnummer3, $idnummer4, $idnummer5, $idnummer6, $idnummer7, $idnummer8, $orderaantal, $aantal_afwijkend, $geconstateerde_afwijking, $_GET['id']]);

        $msg = 'Het artikel is met succes bijgewerkt!';
        header("Location: index.php?page=$page"); // Redirect with page param
        exit;
    }
    // Get the checklist from the productprintcosts table
    $stmt = $pdo->prepare('SELECT * FROM productprintcosts WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $productprintcosts = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$productprintcosts) {
        exit('Artikel met dat ID bestaat niet!');
    }
} else {
    exit('Geen ID gespecificeerd!');
}
?>

<?=template_header('Read')?>

<div class="nav"></div>
<div class="content update">
    <form id="disableSubmit" action="update.php?id=<?=$productprintcosts['id']?>" method="post">
        <section>
            <div class="form-section">
                <div>
                  <label class="jumbo" for="artikelnaam">Artikelnaam</label>
                </div>
                <div>
                  <input type="text" class="jumbo" name="artikelnaam" id="artikelnaam" value="<?=$productprintcosts['artikelnaam']?>">
                  <input type="text" class="hidden" name="aangemaakt" id="aangemaakt" value="<?=date('d-m-Y', strtotime($productprintcosts['aangemaakt']))?>">
                </div>
                <div>
                  <label class="big" for="gewicht">Gewicht (gram)</label>
                  <label class="big" for="gewicht">Print Tijd (minuten)</label>
                </div>
                <div>
                  <input type="text" class="big" name="gewicht" id="gewicht" value="<?=$productprintcosts['gewicht']?>">
                  <input type="text" class="big" name="printtijd" id="printtijd" value="<?=$productprintcosts['printtijd']?>">
                </div>
                <div>
                  <label for="printtijd">ID nummers</label>
                </div>
                <div class="idnummersGrid">
                  <div><input type="text" name="idnummer2" id="idnummer2" value="<?=$productprintcosts['idnummer2']?>"></div>
                  <div><input type="text" name="idnummer3" id="idnummer3" value="<?=$productprintcosts['idnummer3']?>"></div>
                  <div><input type="text" name="idnummer4" id="idnummer4" value="<?=$productprintcosts['idnummer4']?>"></div>
                  <div><input type="text" name="idnummer5" id="idnummer5" value="<?=$productprintcosts['idnummer5']?>"></div>
                  <div><input type="text" name="idnummer6" id="idnummer6" value="<?=$productprintcosts['idnummer6']?>"></div>
                  <div><input type="text" name="idnummer7" id="idnummer7" value="<?=$productprintcosts['idnummer7']?>"></div>
                  <div><input type="text" name="idnummer8" id="idnummer8" value="<?=$productprintcosts['idnummer8']?>"></div>
                </div>
                <div>
                  <label class="jumbo" for="geconstateerde_afwijking">Geef hieronder aan wat de geconstateerde afwijking is, voorzien van product ID.</label>
                </div>
                <div>
                  <textarea name="geconstateerde_afwijking" id="geconstateerde_afwijking" rows="6"><?=$productprintcosts['geconstateerde_afwijking']?></textarea>
                </div>
                <div>
                  <label class="medium" for="printprijs">Print Prijs</label>
                  <label class="medium" for="verkoopprijs">Verkoop Prijs</label>
                </div>
                <div>
                  <input type="text" class="medium" name="printprijs" id="printprijs" value="<?=$productprintcosts['printprijs']?>">
                  <input type="text" class="medium" name="verkoopprijs" id="verkoopprijs" value="<?=$productprintcosts['verkoopprijs']?>">
                </div>
            </div>
        </section>
        <span class="button-span">
            <input type="submit" value="Opslaan">
            <a href="index.php?page=<?=$page?>" class="back">Terug</a>
            <a href="delete.php?id=<?=$productprintcosts['id']?>&page=<?=$page?>" class="trash" onclick="event.stopPropagation();">Verwijderen</a>
        </span>
        <?php if ($msg): ?>
            <?=$msg?>
        <?php endif; ?>
    </form>
</div>

<script>
  document.getElementById('disableSubmit').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevents form submission
    }
  });
</script>

<?=template_footer()?>