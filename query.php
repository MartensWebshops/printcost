<?php

// Connect to MySQL database
$pdo = pdo_connect_mysql();

$query = <<<'SQL'
  SELECT COUNT(*) FROM `productprintcosts`
  SQL;

// execute query
$result = $pdo->query($query);

//  you can just fetchAll as you are not manipulating the result set
$count = $result->fetchAll(PDO::FETCH_ASSOC);

// send header so jQuery knows what it's getting
header('Content-Type: application/json; charset=utf-8');
echo json_encode($count);
?>