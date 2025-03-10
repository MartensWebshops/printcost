<?php
require_once 'functions.php';
$pdo = pdo_connect_mysql();

if (!isset($_POST['query'])) {
    exit('Geen zoekterm opgegeven');
}

$search_term = '%' . trim($_POST['query']) . '%';
$stmt = $pdo->prepare('SELECT id, artikelnaam FROM productprintcosts WHERE artikelnaam LIKE ? LIMIT 10');
$stmt->execute([$search_term]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo '<div class="search-result">Geen resultaten</div>';
} else {
    foreach ($results as $result) {
        $id = htmlspecialchars($result['id']);
        $name = htmlspecialchars($result['artikelnaam']);
        echo "<div class='search-result'><a href='update.php?id=$id'>$name</a></div>";
    }
}