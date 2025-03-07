<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';
// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Check if POST data is not empty
if (!empty($_POST)) {
  // If post data is not empty insert a new record
  // Set-up the variables that are going to be inserted, we must check if the POST variables exist if not we can default them to blank
  $id = isset($_POST['id']) && !empty($_POST['id']) && $_POST['id'] != 'auto' ? $_POST['id'] : NULL;
  // Check if POST variable "naam" exists, if not default the value to blank, basically the same for all variables
  $artikelnaam = isset($_POST['artikelnaam']) ? $_POST['artikelnaam'] : '';
  $aangemaakt = isset($_POST['aangemaakt']) ? $_POST['aangemaakt'] : date('d-m-Y');
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

  // Insert new record into the productprintcosts table
  $stmt = $pdo->prepare('INSERT INTO productprintcosts VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute([$id, $artikelnaam, $aangemaakt, $gewicht, $printprijs, $verkoopprijs, $printtijd, $idnummer2, $idnummer3, $idnummer4, $idnummer5, $idnummer6, $idnummer7, $idnummer8, $orderaantal, $aantal_afwijkend, $geconstateerde_afwijking]);
  // Output message
  $msg = 'Artikel is met success toegevoegd.';
  // Redirect to index.php
  header("Location: index.php");
  exit;
}
?>

<?=template_header('Create')?>

<div class="nav"></div>
<div class="content update">
  <div class="create-article">
    <div class="hamburger-menu">
      <span class="hamburger-icon">☰</span>
      <div class="dropdown-content">
        <a href="index.php?page=<?=$page?>">Overzicht</a>
        <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
        <a href="filaments.php?page=<?=$page?>">Filamenten</a>
      </div>
    </div>
    <h2>Nieuwe Calculatie</h2>
  </div>
  <form id="disableSubmit" action="create.php" method="post">
    <section>
      <div class="form-section">
        <?php if ($msg): ?>
        <?=$msg?>
        <a href="index.php" style="margin-bottom:20px">Terug</a>
        <?php endif; ?>
        <div>
          <label class="jumbo">Artikelnaam</label>
        </div>
        <div>
          <input type="text" class="jumbo" name="artikelnaam" id="artikelnaam">
          <input type="text" class="hidden" name="aangemaakt" value="<?php echo date('Y-m-d');?>" id="aangemaakt">
        </div>
        <div>
          <label class="big">Gewicht (gram)</label>
          <label class="big">Print Tijd (minuten)</label>
        </div>
        <div>
          <input type="text" class="big" name="gewicht" id="gewicht">
          <input type="text" class="big" name="printtijd" id="printtijd">
        </div>
        <div>
          <label class="big">Print Prijs</label>
          <label class="big">Verkoop Prijs</label>
        </div>
        <div>
          <input type="text" class="big" name="printprijs" id="printprijs">
          <input type="text" class="big" name="verkoopprijs" id="verkoopprijs">
        </div>
        <div>
          <label>ID nummers</label>
        </div>
        <div class="idnummersGrid">
          <div>
            <input type="text" placeholder="000" name="printtijd" id="printtijd">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer2" id="idnummer2">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer3" id="idnummer3">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer4" id="idnummer4">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer5" id="idnummer5">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer6" id="idnummer6">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer7" id="idnummer7">
          </div>
          <div>
            <input type="text" placeholder="000" name="idnummer8" id="idnummer8">
          </div>
        </div>
        <div>
          <label>Order aantal</label>
          <label>Aantal afwijkend</label>
        </div>
        <div>
          <select name="orderaantal" id="orderaantal">
            <option value="" disabled selected>Selecteer</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
          <select name="aantal_afwijkend" id="aantal_afwijkend">
            <option value="" disabled selected>Selecteer</option>
            <option value="0">0</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
        </div>
        <div>
          <label class="jumbo">Geef hieronder aan wat de geconstateerde afwijking is, voorzien van product ID.</label>
        </div>
        <div>
          <textarea name="geconstateerde_afwijking" id="geconstateerde_afwijking" rows="6"></textarea>
        </div>
      </div>
    </section>
    <span class="button-span">
      <input type="submit" value="Opslaan">
      <a class="back" href="index.php?page=<?php echo isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1; ?>">Terug</a>
    </span>
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