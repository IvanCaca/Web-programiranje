<?php
require 'php/db_config.php';

$message = "";
$messageType = "";

if (isset($_GET['code'])) {
    $code = $_GET['code']; // Kod preuzet iz URL-a

    // Pronađi korisnika sa datim aktivacionim kodom
    $sql = "SELECT * FROM users WHERE activation_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $user = $stmt->fetch();

    if ($user) {
        // Aktiviraj nalog korisnika
        $sql = "UPDATE users SET active = 1, activation_code = NULL WHERE activation_code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);

        if ($stmt->rowCount() > 0) {
            $message = "Your account has been activated. You can now <a href='index.php' class='alert-link'>log in</a>.";
            $messageType = "success";
        } else {
            $message = "Failed to activate your account. Please try again.";
            $messageType = "danger";
        }
    } else {
        $message = "Invalid activation code.";
        $messageType = "danger";
    }
} else {
    $message = "No activation code provided.";
    $messageType = "danger";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Activation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Caveat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(to right, #ffcccb, #add8e6);
            font-family: Arial, sans-serif;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 600px;
            text-align: center;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
