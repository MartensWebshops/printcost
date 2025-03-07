<?php
require_once 'functions.php';

// Get the PDO connection
$pdo = pdo_connect_mysql();

if (isset($_POST['query'])) {
  $inpText = $_POST['query'];
  // SQL query to search in artikelnaam column
  $sql = 'SELECT * FROM productprintcosts WHERE artikelnaam LIKE :search';
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['search' => '%' . $inpText . '%']);
  $result = $stmt->fetchAll();

  if ($result) {
    foreach ($result as $row) {
      // Display artikelnaam in the search result, link to update.php
      echo '<div class="search-result"><a href="update.php?id=' . $row['id'] . '">' . htmlspecialchars($row['artikelnaam']) . '</a></div>';
      }
  } else {
      echo '<div class="search-result">Geen resultaten</div>';
  }
}
?>