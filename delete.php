<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) && in_array($_GET['type'], ['product', 'filament']) ? $_GET['type'] : '';

if (!$id || !$type) {
    redirect_with_message("index.php?page=$page", 'Ongeldig ID of type!', 'error');
}

if (!empty($_POST)) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_message("delete.php?id=$id&type=$type&page=$page", 'Ongeldige CSRF-token', 'error');
    }

    $table = $type === 'product' ? 'productprintcosts' : 'filaments';
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);

    $redirect_page = $type === 'product' ? "index.php?page=$page" : "filaments.php?page=$page";
    redirect_with_message($redirect_page, ucfirst($type) . ' succesvol verwijderd!');
}
?>

<?=template_header('Verwijderen Bevestigen')?>

<div class="nav"></div>
<div class="content delete">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
            </div>
        </div>
        <h2><?= $type === 'product' ? 'Product' : 'Filament' ?> Verwijderen</h2>
    </div>
    <?=get_flash_message()?>
    <div class="delete-confirm">
        <p>Weet je zeker dat je dit <?=$type === 'product' ? 'product' : 'filament'?> wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>
        <form action="delete.php?id=<?=$id?>&type=<?=$type?>&page=<?=$page?>" method="post">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <div class="button-span">
                <input type="submit" value="Verwijderen" class="trash">
                <a href="<?= $type === 'product' ? 'index.php' : 'filaments.php' ?>?page=<?=$page?>" class="back">Annuleren</a>
            </div>
        </form>
    </div>
</div>

<?=template_footer()?>