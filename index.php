<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$stmt = $pdo->prepare('SELECT * FROM productprintcosts ORDER BY id DESC LIMIT ?, ?');
$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_records = $pdo->query('SELECT COUNT(*) FROM productprintcosts')->fetchColumn();
?>

<?=template_header('Producten')?>

<div class="nav"></div>
<div class="content read">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
            </div>
        </div>
        <h2>Overzicht</h2>
        <div class="search-stats">
            <form method="POST" action="">
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
                <th>Actie</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr class="clickable-row" data-href="update.php?id=<?=$product['id']?>&page=<?=$page?>">
                <td><?=htmlspecialchars($product['artikelnaam'])?></td>
                <td><?=htmlspecialchars($product['gewicht'])?>gr</td>
                <td><?=format_duration($product['printtijd'])?></td>
                <td>€<?=number_format($product['printprijs'], 2)?></td>
                <td>€<?=number_format($product['verkoopprijs'], 2)?></td>
                <td><?=date('d-m-Y', strtotime($product['aangemaakt']))?></td>
                <td><a href="delete.php?id=<?=$product['id']?>&type=product&page=<?=$page?>" class="delete-link">Verwijderen</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?=generate_pagination($page, $total_records, $records_per_page, 'index.php')?>
</div>

<?=template_footer()?>