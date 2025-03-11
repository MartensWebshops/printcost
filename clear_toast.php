<?php
session_start();
unset($_SESSION['toast_message']);
unset($_SESSION['toast_type']);
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>