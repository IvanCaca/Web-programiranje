<?php
session_start();
require 'php/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $stmt = $pdo->prepare("UPDATE items SET is_checked = !is_checked WHERE item_id = ?");
    $stmt->execute([$item_id]);
}
?>
