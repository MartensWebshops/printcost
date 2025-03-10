<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Fetch total records first
$total_records_stmt = $pdo->query('SELECT COUNT(*) FROM filaments');
$total_records = $total_records_stmt->fetchColumn();

// Fetch paginated filaments
$stmt = $pdo->prepare('SELECT * FROM filaments ORDER BY date_added DESC LIMIT ?, ?');
$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$filaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions (add/update)
if (!empty($_POST) && isset($_POST['update_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("filaments.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $data = sanitize_input($_POST);
    $defaults = ['brand' => '', 'name' => '', 'type' => '', 'color' => '', 'weight' => 0, 'price' => 0.0];
    $data = array_merge($defaults, $data);
    $required = ['brand', 'name', 'type'];

    if (!validate_required($data, $required) || $data['weight'] <= 0 || $data['price'] <= 0) {
        redirect_with_message("filaments.php?page=$page", 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs per gram)!', 'error');
    }

    $stmt = $pdo->prepare('UPDATE filaments SET brand = ?, name = ?, type = ?, color = ?, weight = ?, price = ? WHERE id = ?');
    $stmt->execute([$data['brand'], $data['name'], $data['type'], $data['color'], (int)$data['weight'], (float)$data['price'], (int)$data['update_id']]);
    redirect_with_message("filaments.php?page=$page", 'Filament succesvol bijgewerkt!');
} elseif (!empty($_POST) && !isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("filaments.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $data = sanitize_input($_POST);
    $defaults = ['brand' => '', 'name' => '', 'type' => '', 'color' => '', 'weight' => 0, 'price' => 0.0];
    $data = array_merge($defaults, $data);
    $required = ['brand', 'name', 'type'];

    if (!validate_required($data, $required) || $data['weight'] <= 0 || $data['price'] <= 0) {
        redirect_with_message("filaments.php?page=$page", 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs per gram)!', 'error');
    }

    $stmt = $pdo->prepare('INSERT INTO filaments (brand, name, type, color, weight, price) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$data['brand'], $data['name'], $data['type'], $data['color'], (int)$data['weight'], (float)$data['price']]);
    redirect_with_message("filaments.php?page=$page", 'Filament succesvol toegevoegd!');
}

// Handle delete confirmation
if (!empty($_POST) && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("filaments.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('DELETE FROM filaments WHERE id = ?');
    $stmt->execute([$id]);
    redirect_with_message("filaments.php?page=$page", 'Filament succesvol verwijderd!');
}
?>

<?=template_header('Filamenten')?>

<div class="nav"></div>
<div class="content read">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
            </div>
        </div>
        <h2>Filamenten</h2>
    </div>
    <?=get_flash_message()?>

    <div class="create-filament">
        <h2>Nieuw Filament Toevoegen</h2>
        <form action="filaments.php?page=<?=$page?>" method="post">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
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
                    <input type="text" class="big" id="type" name="type" placeholder="Bijv. Standard" required>
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
                <div class="button-span">
                    <input type="submit" value="Toevoegen">
                    <a href="index.php?page=<?=$page?>" class="back">Terug</a>
                </div>
            </div>
        </form>
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
                    <th>Prijs per gram (€)</th>
                    <th>Datum toegevoegd</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filaments as $filament): ?>
                    <tr class="filament-row" 
                        data-id="<?=$filament['id']?>" 
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
        <?=generate_pagination($page, $total_records, $records_per_page, 'filaments.php')?>
    <?php endif; ?>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close"><i class='bx bx-x'></i></span>
            <h3>Filament Bewerken</h3>
            <form action="filaments.php?page=<?=$page?>" method="post" id="editFilamentForm">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                <input type="hidden" name="update_id" id="edit_id">
                <div class="form-section" id="editFilamentFields">
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
                </div>
                <div class="delete-confirm" id="deleteFilamentConfirm" style="display: none;">
                    <p>Weet je zeker dat je dit filament wilt verwijderen?</p>
                    <input type="hidden" name="delete_id" id="delete_filament_id">
                </div>
                <div class="button-span" id="editFilamentButtons">
                    <input type="submit" value="Opslaan">
                    <button type="button" class="close-btn">Annuleren</button>
                    <button type="button" class="delete-btn" id="deleteFilamentBtn">Verwijderen</button>
                </div>
                <div class="button-span" id="confirmFilamentButtons" style="display: none;">
                    <input type="submit" value="Ja, verwijder" class="delete-btn">
                    <button type="button" class="close-btn" id="cancelFilamentDelete">Nee, annuleren</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?=template_footer()?>