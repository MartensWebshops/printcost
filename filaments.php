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
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    $data = sanitize_input($_POST);
    $defaults = ['brand' => '', 'name' => '', 'type' => '', 'color' => '', 'weight' => 0, 'price' => 0.0];
    $data = array_merge($defaults, $data);
    $required = ['brand', 'name', 'type'];

    if (!validate_required($data, $required) || $data['weight'] <= 0 || $data['price'] <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs per gram)!']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE filaments SET brand = ?, name = ?, type = ?, color = ?, weight = ?, price = ? WHERE id = ?');
    $stmt->execute([$data['brand'], $data['name'], $data['type'], $data['color'], (int)$data['weight'], (float)$data['price'], (int)$data['update_id']]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Filament succesvol bijgewerkt!']);
    exit;
} elseif (!empty($_POST) && !isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    $data = sanitize_input($_POST);
    $defaults = ['brand' => '', 'name' => '', 'type' => '', 'color' => '', 'weight' => 0, 'price' => 0.0];
    $data = array_merge($defaults, $data);
    $required = ['brand', 'name', 'type'];

    if (!validate_required($data, $required) || $data['weight'] <= 0 || $data['price'] <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in (merk, naam, type, gewicht, prijs per gram)!']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO filaments (brand, name, type, color, weight, price) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$data['brand'], $data['name'], $data['type'], $data['color'], (int)$data['weight'], (float)$data['price']]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Filament succesvol toegevoegd!']);
    exit;
}

// Handle delete confirmation
if (!empty($_POST) && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('DELETE FROM filaments WHERE id = ?');
    $stmt->execute([$id]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Filament succesvol verwijderd!']);
    exit;
}
?>

<?=template_header('Filamenten')?>

<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="images/zintuigspel_logo.svg" alt="Zintuigspel Logo" class="sidebar-logo">
            <i class='bx bx-menu toggle-btn'></i>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class='bx bx-home'></i><span>Overzicht</span></a></li>
            <li><a href="filaments.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'filaments.php' ? 'active' : '' ?>"><i class='bx bx-sushi'></i><span>Filamenten</span></a></li>
            <li><a href="costs.php?page=<?=$page?>"><i class='bx bx-dollar'></i><span>Kosten</span></a></li>
            <li><a href="printer.php?page=<?=$page?>"><i class='bx bx-printer'></i><span>Printer Beheer</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content read">
            <div class="create-article">
                <h2>Filamenten</h2>
                <button id="create-filament-btn" class="btn-add">Nieuw Filament</button>
            </div>

            <?php if (empty($filaments) && !$total_records): ?>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?=generate_pagination($page, $total_records, $records_per_page, 'filaments.php')?>
            <?php endif; ?>

            <!-- Edit Filament Modal -->
            <div id="edit-modal" class="modal">
                <div class="modal-content">
                    <span class="close">×</span>
                    <h2>Filament Bewerken</h2>
                    <form action="filaments.php?page=<?=$page?>" method="post" id="edit-filament-form">
                        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                        <input type="hidden" name="update_id" id="edit-id">
                        <div class="form-group">
                            <label for="edit-brand">Merk *</label>
                            <input type="text" id="edit-brand" name="brand" placeholder="Merk" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-name">Naam *</label>
                            <input type="text" id="edit-name" name="name" placeholder="Naam" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-type">Type *</label>
                            <input type="text" id="edit-type" name="type" placeholder="Type" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-color">Kleur</label>
                            <input type="text" id="edit-color" name="color" placeholder="Kleur">
                        </div>
                        <div class="form-group">
                            <label for="edit-weight">Gewicht (g) *</label>
                            <input type="number" id="edit-weight" name="weight" min="1" step="1" placeholder="Gewicht (g)" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-price">Prijs per gram (€) *</label>
                            <input type="number" id="edit-price" name="price" step="0.01" min="0.01" placeholder="Prijs (€)" required>
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="btn-save">Opslaan</button>
                            <button type="button" class="btn-cancel">Annuleren</button>
                            <button type="button" class="trash" id="delete-filament-btn">Verwijderen</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="delete-filament-modal" class="modal">
                <div class="modal-content">
                    <span class="close">×</span>
                    <h2>Filament Verwijderen</h2>
                    <form action="filaments.php?page=<?=$page?>" method="post" id="delete-filament-form">
                        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                        <input type="hidden" name="delete_id" id="delete-filament-id">
                        <div class="form-group">
                            <p>Weet je zeker dat je dit filament wilt verwijderen?</p>
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="trash">Ja</button>
                            <button type="button" class="btn-cancel" id="cancel-filament-delete">Nee</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Create Filament Modal -->
            <div id="create-filament-modal" class="modal">
                <div class="modal-content">
                    <span class="close">×</span>
                    <h2>Nieuw Filament Toevoegen</h2>
                    <form action="filaments.php?page=<?=$page?>" method="post" id="create-filament-form">
                        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                        <div class="form-group">
                            <label for="create-brand">Merk *</label>
                            <input type="text" id="create-brand" name="brand" placeholder="Merk" required>
                        </div>
                        <div class="form-group">
                            <label for="create-name">Naam *</label>
                            <input type="text" id="create-name" name="name" placeholder="Naam" required>
                        </div>
                        <div class="form-group">
                            <label for="create-type">Type *</label>
                            <input type="text" id="create-type" name="type" placeholder="Type" required>
                        </div>
                        <div class="form-group">
                            <label for="create-color">Kleur</label>
                            <input type="text" id="create-color" name="color" placeholder="Kleur">
                        </div>
                        <div class="form-group">
                            <label for="create-weight">Gewicht (g) *</label>
                            <input type="number" id="create-weight" name="weight" min="1" step="1" placeholder="Gewicht (g)" required>
                        </div>
                        <div class="form-group">
                            <label for="create-price">Prijs per gram (€) *</label>
                            <input type="number" id="create-price" name="price" step="0.01" min="0.01" placeholder="Prijs (€)" required>
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="btn-add">Toevoegen</button>
                            <button type="button" class="btn-cancel">Annuleren</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?=template_footer()?>