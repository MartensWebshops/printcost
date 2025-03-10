<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Handle AJAX search request
if (isset($_POST['query']) && !isset($_POST['update_id']) && !isset($_POST['delete_id'])) {
    $query = trim($_POST['query']);
    $output = '';

    if ($query) {
        $stmt = $pdo->prepare('SELECT * FROM productprintcosts WHERE artikelnaam LIKE ?');
        $stmt->execute(["%$query%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            foreach ($results as $row) {
                $output .= '<div class="search-result"><a href="#" data-id="' . $row['id'] . '">' . htmlspecialchars($row['artikelnaam']) . '</a></div>';
            }
        } else {
            $output = '<div class="search-result">Geen producten gevonden.</div>';
        }
    }
    echo $output;
    exit;
}

// Fetch products
$stmt = $pdo->prepare('SELECT * FROM productprintcosts ORDER BY id DESC LIMIT ?, ?');
$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_records = $pdo->query('SELECT COUNT(*) FROM productprintcosts')->fetchColumn();

// Handle edit form submission
if (!empty($_POST) && isset($_POST['update_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("index.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $data = sanitize_input($_POST);
    $defaults = [
        'artikelnaam' => '', 'gewicht' => 0, 'printtijd' => 0, 'printprijs' => 0.0, 'verkoopprijs' => 0.0,
        'idnummer2' => '', 'idnummer3' => '', 'idnummer4' => '', 'idnummer5' => '', 'idnummer6' => '',
        'idnummer7' => '', 'idnummer8' => '', 'orderaantal' => 0, 'aantal_afwijkend' => 0,
        'geconstateerde_afwijking' => ''
    ];
    $data = array_merge($defaults, $data);
    $required = ['artikelnaam'];

    if (!validate_required($data, $required) || $data['gewicht'] < 0 || $data['printtijd'] < 0) {
        redirect_with_message("index.php?page=$page", 'Vul alle verplichte velden in en zorg dat gewicht en printtijd niet negatief zijn!', 'error');
    }

    $stmt = $pdo->prepare('UPDATE productprintcosts SET artikelnaam = ?, gewicht = ?, printtijd = ?, printprijs = ?, verkoopprijs = ?, idnummer2 = ?, idnummer3 = ?, idnummer4 = ?, idnummer5 = ?, idnummer6 = ?, idnummer7 = ?, idnummer8 = ?, orderaantal = ?, aantal_afwijkend = ?, geconstateerde_afwijking = ? WHERE id = ?');
    $stmt->execute([
        $data['artikelnaam'], (int)$data['gewicht'], (int)$data['printtijd'], (float)$data['printprijs'],
        (float)$data['verkoopprijs'], $data['idnummer2'], $data['idnummer3'], $data['idnummer4'],
        $data['idnummer5'], $data['idnummer6'], $data['idnummer7'], $data['idnummer8'],
        (int)$data['orderaantal'], (int)$data['aantal_afwijkend'], $data['geconstateerde_afwijking'],
        (int)$data['update_id']
    ]);
    redirect_with_message("index.php?page=$page", 'Product succesvol bijgewerkt!');
}

// Handle delete confirmation
if (!empty($_POST) && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("index.php?page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('DELETE FROM productprintcosts WHERE id = ?');
    $stmt->execute([$id]);
    redirect_with_message("index.php?page=$page", 'Product succesvol verwijderd!');
}
?>

<?=template_header('Producten')?>

<div class="nav"></div>
<div class="content read">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
                <a href="costs.php?page=<?=$page?>">Kosten</a>
            </div>
        </div>
        <h2>Overzicht</h2>
        <div class="search-stats">
            <form method="POST" action="index.php?page=<?=$page?>">
                <div class="search-container">
                    <input type="text" class="big" id="search" name="search" placeholder="Zoeken..." autocomplete="off">
                    <span class="clear-search"><i class='bx bx-x'></i></span>
                </div>
                <div class="list-group" id="show-list"></div>
            </form>
        </div>
    </div>
    <?=get_flash_message()?>
    <table>
        <thead>
            <tr>
                <th>Artikelnaam</th>
                <th>Gewicht (g)</th>
                <th>Printtijd</th>
                <th>Printprijs (€)</th>
                <th>Verkoopprijs (€)</th>
                <th>Aangemaakt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr class="product-row" 
                data-id="<?=$product['id']?>"
                data-artikelnaam="<?=htmlspecialchars($product['artikelnaam'])?>"
                data-gewicht="<?=$product['gewicht']?>"
                data-printtijd="<?=$product['printtijd']?>"
                data-printprijs="<?=$product['printprijs']?>"
                data-verkoopprijs="<?=$product['verkoopprijs']?>"
                data-idnummer2="<?=htmlspecialchars($product['idnummer2'])?>"
                data-idnummer3="<?=htmlspecialchars($product['idnummer3'])?>"
                data-idnummer4="<?=htmlspecialchars($product['idnummer4'])?>"
                data-idnummer5="<?=htmlspecialchars($product['idnummer5'])?>"
                data-idnummer6="<?=htmlspecialchars($product['idnummer6'])?>"
                data-idnummer7="<?=htmlspecialchars($product['idnummer7'])?>"
                data-idnummer8="<?=htmlspecialchars($product['idnummer8'])?>"
                data-orderaantal="<?=$product['orderaantal']?>"
                data-aantal_afwijkend="<?=$product['aantal_afwijkend']?>"
                data-geconstateerde_afwijking="<?=htmlspecialchars($product['geconstateerde_afwijking'])?>">
                <td><?=htmlspecialchars($product['artikelnaam'])?></td>
                <td><?=htmlspecialchars($product['gewicht'])?>gr</td>
                <td><?=format_duration($product['printtijd'])?></td>
                <td>€<?=number_format($product['printprijs'], 2)?></td>
                <td>€<?=number_format($product['verkoopprijs'], 2)?></td>
                <td><?=date('d-m-Y', strtotime($product['aangemaakt']))?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?=generate_pagination($page, $total_records, $records_per_page, 'index.php')?>

    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close"><i class='bx bx-x'></i></span>
            <h3>Product Bewerken</h3>
            <form action="index.php?page=<?=$page?>" method="post" id="editProductForm">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                <input type="hidden" name="update_id" id="edit_product_id">
                <div class="form-section" id="editFormFields">
                    <div>
                        <label for="edit_artikelnaam">Artikelnaam *</label>
                        <input type="text" class="big" id="edit_artikelnaam" name="artikelnaam" required>
                    </div>
                    <div class="form-row">
                        <div>
                            <label for="edit_gewicht">Gewicht (g)</label>
                            <input type="number" class="big" id="edit_gewicht" name="gewicht" min="0">
                        </div>
                        <div>
                            <label for="edit_printtijd">Printtijd (min)</label>
                            <input type="number" class="big" id="edit_printtijd" name="printtijd" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div>
                            <label for="edit_printprijs">Printprijs (€)</label>
                            <input type="number" class="big" id="edit_printprijs" name="printprijs" step="0.01" min="0">
                        </div>
                        <div>
                            <label for="edit_verkoopprijs">Verkoopprijs (€)</label>
                            <input type="number" class="big" id="edit_verkoopprijs" name="verkoopprijs" step="0.01" min="0">
                        </div>
                    </div>
                    <div>
                        <label>ID-nummers</label>
                        <div class="idnummersGrid">
                            <input type="text" class="big" id="edit_idnummer2" name="idnummer2" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer3" name="idnummer3" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer4" name="idnummer4" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer5" name="idnummer5" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer6" name="idnummer6" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer7" name="idnummer7" placeholder="000">
                            <input type="text" class="big" id="edit_idnummer8" name="idnummer8" placeholder="000">
                        </div>
                    </div>
                    <div class="form-row">
                        <div>
                            <label for="edit_orderaantal">Orderaantal</label>
                            <select class="big" id="edit_orderaantal" name="orderaantal">
                                <option value="" disabled>Selecteer</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?=$i?>"><?=$i?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="edit_aantal_afwijkend">Aantal afwijkend</label>
                            <select class="big" id="edit_aantal_afwijkend" name="aantal_afwijkend">
                                <option value="" disabled>Selecteer</option>
                                <?php for ($i = 0; $i <= 8; $i++): ?>
                                    <option value="<?=$i?>"><?=$i?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="edit_geconstateerde_afwijking">Geconstateerde afwijking</label>
                        <textarea class="big" id="edit_geconstateerde_afwijking" name="geconstateerde_afwijking" rows="4"></textarea>
                    </div>
                </div>
                <div class="delete-confirm" id="deleteConfirm" style="display: none;">
                    <p>Weet je zeker dat je dit product wilt verwijderen?</p>
                    <input type="hidden" name="delete_id" id="delete_product_id">
                </div>
                <div class="button-span" id="editButtons">
                    <input type="submit" value="Opslaan">
                    <button type="button" class="close-btn">Annuleren</button>
                    <button type="button" class="delete-btn" id="deleteProductBtn">Verwijderen</button>
                </div>
                <div class="button-span" id="confirmButtons" style="display: none;">
                    <input type="submit" value="Ja, verwijder" class="delete-btn">
                    <button type="button" class="close-btn" id="cancelDelete">Nee, annuleren</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?=template_footer()?>