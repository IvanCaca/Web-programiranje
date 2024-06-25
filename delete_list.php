<?php
session_start();
require 'php/db_config.php';

if (isset($_GET['list_id'])) {
    $list_id = $_GET['list_id'];
    $stmt = $pdo->prepare("DELETE FROM lists WHERE list_id = ?");
    $stmt->execute([$list_id]);
}
?>
