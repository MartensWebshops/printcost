<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Fetch all filaments from the database
$stmt = $pdo->query('SELECT * FROM filaments ORDER BY date_added DESC');
$filaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to add a new filament
if (!empty($_POST) && !isset($_POST['update_id'])) {
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $weight = isset($_POST['weight']) ? intval($_POST['weight']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;

    if (empty($brand) || empty($name) || empty($type) || $weight <= 0 || $price <= 0) {
        $msg = 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs per gram)!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO filaments (brand, name, type, color, weight, price) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$brand, $name, $type, $color, $weight, $price]);
        $msg = 'Filament succesvol toegevoegd!';
        header('Location: filaments.php?page=' . $page);
        exit;
    }
}

// Handle form submission to update a filament
if (!empty($_POST) && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $weight = isset($_POST['weight']) ? intval($_POST['weight']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;

    if (empty($brand) || empty($name) || empty($type) || $weight <= 0 || $price <= 0) {
        $msg = 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs)!';
    } else {
        $stmt = $pdo->prepare('UPDATE filaments SET brand = ?, name = ?, type = ?, color = ?, weight = ?, price = ? WHERE id = ?');
        $stmt->execute([$brand, $name, $type, $color, $weight, $price, $id]);
        $msg = 'Filament succesvol bijgewerkt!';
        header('Location: filaments.php?page=' . $page);
        exit;
    }
}
?>

<?=template_header('Filaments')?>

<div class="nav"></div>
<div class="content read">
  <div class="create-article">
    <div class="hamburger-menu">
      <span class="hamburger-icon">☰</span>
      <div class="dropdown-content">
        <a href="index.php?page=<?=$page?>">Overzicht</a>
        <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
        <a href="filaments.php?page=<?=$page?>">Filamenten</a>
      </div>
    </div>
    <h2>Filamenten</h2>
  </div>
    
  <?php if (empty($filaments)): ?>
      <p>Geen filamenten gevonden.</p>
  <?php else: ?>
      <table>
          <thead>
              <tr>
                  <th>Merk</th>
                  <th>Naam</th>
                  <th>Type</th>
                  <th>Kleur</th>
                  <th>Gewicht (g)</th>
                  <th>Prijs</th>
                  <th>Datum toegevoegd</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($filaments as $filament): ?>
                  <tr class="filament-row" data-id="<?=$filament['id']?>" 
                      data-brand="<?=htmlspecialchars($filament['brand'])?>" 
                      data-name="<?=htmlspecialchars($filament['name'])?>" 
                      data-type="<?=htmlspecialchars($filament['type'])?>" 
                      data-color="<?=htmlspecialchars($filament['color'] ?: '')?>" 
                      data-weight="<?=$filament['weight']?>" 
                      data-price="<?=$filament['price']?>">
                      <td><?=htmlspecialchars($filament['brand'])?></td>
                      <td><?=htmlspecialchars($filament['name'])?></td>
                      <td><?=htmlspecialchars($filament['type'])?></td>
                      <td><?=htmlspecialchars($filament['color'] ?: 'N/A')?></td>
                      <td><?=htmlspecialchars($filament['weight'])?>gr</td>
                      <td>€<?=number_format($filament['price'], 2)?></td>
                      <td><?=date('d-m-Y', strtotime($filament['date_added']))?></td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  <?php endif; ?>

  <!-- Form to Add New Filament -->
  <div class="create-filament">
    <h2>Nieuw Filament Toevoegen</h2>
    <?php if ($msg && !isset($_POST['update_id'])): ?>
        <p class="msg"><?=$msg?></p>
    <?php endif; ?>
    <form action="filaments.php?page=<?=$page?>" method="post">
        <div class="form-section">
            <div>
                <label for="brand">Merk *</label>
                <input type="text" class="big" id="brand" name="brand" placeholder="Bijv. Prusa" required>
            </div>
            <div>
                <label for="name">Naam *</label>
                <input type="text" class="big" id="name" name="name" placeholder="Bijv. PLA" required>
            </div>
            <div>
                <label for="type">Type *</label>
                <input type="text" class="big" id="type" name="type" required>
            </div>
            <div>
                <label for="color">Kleur</label>
                <input type="text" class="big" id="color" name="color" placeholder="Bijv. Rood">
            </div>
            <div>
                <label for="weight">Gewicht (g) *</label>
                <input type="number" class="big" id="weight" name="weight" min="1" placeholder="Bijv. 1000" required>
            </div>
            <div>
                <label for="price">Prijs per gram (€) *</label>
                <input type="number" class="big" id="price" name="price" step="0.01" min="0.01" placeholder="Bijv. 0.03" required>
            </div>
            <!-- <div class="button-span">
                <input type="submit" value="Toevoegen">
                <a href="index.php?page=<?=$page?>" class="back">Terug</a>
            </div> -->
            <span class="button-span">
                <input type="submit" value="Opslaan">
                <a class="back" href="index.php?page=<?php echo isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1; ?>">Terug</a>
            </span>
        </div>
    </form>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
      <div class="modal-content">
          <span class="close">×</span>
          <h3>Filament Bewerken</h3>
          <?php if ($msg && isset($_POST['update_id'])): ?>
              <p class="msg"><?=$msg?></p>
          <?php endif; ?>
          <form action="filaments.php?page=<?=$page?>" method="post">
              <input type="hidden" name="update_id" id="edit_id">
              <div class="form-section">
                  <div>
                      <label for="edit_brand">Merk *</label>
                      <input type="text" class="big" id="edit_brand" name="brand" required>
                  </div>
                  <div>
                      <label for="edit_name">Naam *</label>
                      <input type="text" class="big" id="edit_name" name="name" required>
                  </div>
                  <div>
                      <label for="edit_type">Type *</label>
                      <input type="text" class="big" id="edit_type" name="type" required>
                  </div>
                  <div>
                      <label for="edit_color">Kleur</label>
                      <input type="text" class="big" id="edit_color" name="color">
                  </div>
                  <div>
                      <label for="edit_weight">Gewicht (g) *</label>
                      <input type="number" class="big" id="edit_weight" name="weight" min="1" required>
                  </div>
                  <div>
                      <label for="edit_price">Prijs per gram (€) *</label>
                      <input type="number" class="big" id="edit_price" name="price" step="0.01" min="0.01" required>
                  </div>
                  <span class="button-span">
                      <input type="submit" value="Opslaan">
                      <a class="back" href="filaments.php?page=<?php echo isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1; ?>">Terug</a>
                  </span>
              </div>
          </form>
      </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');
    const closeButton = document.querySelector('.close-btn');
    const rows = document.querySelectorAll('.filament-row');

    rows.forEach(row => {
        row.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_brand').value = this.dataset.brand;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_color').value = this.dataset.color;
            document.getElementById('edit_weight').value = this.dataset.weight;
            document.getElementById('edit_price').value = this.dataset.price;
            modal.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?=template_footer()?>