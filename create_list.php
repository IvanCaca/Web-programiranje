<?php
session_start();
require 'php/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $list_name = htmlspecialchars($_POST['list_name']);
    $user_id = $_SESSION['user_id'];

    // Check if list with same name exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lists WHERE user_id = ? AND list_name LIKE ?");
    $stmt->execute([$user_id, $list_name . '%']);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $list_name .= ' ' . $count;
    }

    $stmt = $pdo->prepare("INSERT INTO lists (user_id, list_name) VALUES (?, ?)");
    $stmt->execute([$user_id, $list_name]);

    header('Location: dashboard.php');
    exit;
}
?>
