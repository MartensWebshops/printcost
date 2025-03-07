<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
// Check that the artikel ID exists
if (isset($_GET['id'])) {
  // Select the record that is going to be deleted
  $stmt = $pdo->prepare('SELECT * FROM productprintcosts WHERE id = ?');
  $stmt->execute([$_GET['id']]);
  $artikel = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$artikel) {
    exit('Artikel met dit ID  bestaat niet!');
  }
  // Make sure the user confirms beore deletion
  if (isset($_GET['confirm'])) {

    // Get the page number from the URL, default to 1 if not set or invalid
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

    if ($_GET['confirm'] == 'yes') {
      // User clicked the "Yes" button, delete record
      $stmt = $pdo->prepare('DELETE FROM productprintcosts WHERE id = ?');
      $stmt->execute([$_GET['id']]);

      // Redirect to index.php with the page parameter
      header("Location: index.php?page=$page");
      exit;
  } else {
      // User clicked the "No" button, redirect them back to the read page with the page parameter
      header("Location: index.php?page=$page");
      exit;
    }
  }
} else {
    exit('No ID specified!');
}
?>

<?=template_header('Delete')?>

<div class="nav"></div>
<div class="content delete">
	<h2>Artikel: "<?=$artikel['artikelnaam']?>" verwijderen</h2>
	<p>Weet u zeker dat u dit artikel wilt verwijderen?</p>
    <div class="yesno">
    <a class="trash" href="delete.php?id=<?=$artikel['id']?>&page=<?=isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1?>&confirm=yes">Ja</a>
    <a class="back" href="delete.php?id=<?=$artikel['id']?>&page=<?=isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1?>&confirm=no">Nee</a>
    </div>
</div>

<?=template_footer()?>