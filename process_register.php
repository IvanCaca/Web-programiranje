<?php
session_start();
require 'php/db_config.php'; // Učitavanje konfiguracije baze
require 'vendor/autoload.php'; // Učitavanje PHPMailer-a

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);

    if ($password !== $confirm_password) {
        echo "Lozinke se ne podudaraju!";
        exit;
    }

    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "E-mail već postoji!";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $activation_code = bin2hex(random_bytes(16)); // Generisanje aktivacionog koda

    $sql = "INSERT INTO users (email, password, first_name, last_name, phone, activation_code) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $passwordHash, $first_name, $last_name, $phone, $activation_code]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sentasrbija@gmail.com'; // Gmail korisničko ime
        $mail->Password = 'uufa lvln uukh qcie'; // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('sentasrbija@gmail.com', 'Mailer');
        $mail->addAddress($email, $first_name . ' ' . $last_name);

        $mail->isHTML(true);
        $mail->Subject = 'Activation Link';
        $mail->Body    = '
            <html>
            <body>
                <h1>Welcome, ' . $first_name . '!</h1>
                <p>Thank you for registering on our site. Please click the button below to verify your email address and activate your account.</p>
                <a href="http://localhost/shopping_list/activate.php?code=' . $activation_code . '" style="display:inline-block; padding:10px 20px; color:#ffffff; background-color:#007bff; border-radius:5px; text-decoration:none;">Verify</a>
            </body>
            </html>';

        $mail->send();
        echo 'Activation email has been sent.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request";
}
?>
