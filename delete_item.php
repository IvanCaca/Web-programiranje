<?php
session_start();
require 'php/db_config.php';

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
}
?>
