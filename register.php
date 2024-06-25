<?php
session_start();
require 'php/db_config.php'; // Učitavanje konfiguracije baze
require 'vendor/autoload.php'; // Učitavanje PHPMailer-a

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);

    if ($password !== $confirm_password) {
        $message = "Lozinke se ne podudaraju!";
        $messageType = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Lozinka mora imati najmanje 6 karaktera!";
        $messageType = "danger";
    } else {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "E-mail već postoji!";
            $messageType = "danger";
        } else {
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
                $mail->Body = '
                    <html>
                    <body>
                        <h1>Welcome, ' . $first_name . '!</h1>
                        <p>Thank you for registering on our site. Please click the button below to verify your email address and activate your account.</p>
                        <a href="http://localhost/shopping_list/activate.php?code=' . $activation_code . '" style="display:inline-block; padding:10px 20px; color:#ffffff; background-color:#007bff; border-radius:5px; text-decoration:none;">Verify</a>
                    </body>
                    </html>';

                $mail->send();
                $message = 'Activation email has been sent. Please check your Allmail or Spam folder if you do not see it in your inbox.';
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $messageType = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
            position: relative;
        }

        h2 {
            text-align: center;
            font-family: 'Pacifico', cursive;
            font-size: 2.5rem;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-control {
            border-radius: 10px;
            padding-right: 40px;
        }

        .btn-primary {
            background-color: rgba(0, 0, 0, 0.6);
            border: none;
            border-radius: 15px;
        }

        .btn-primary:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .btn-link {
            color: black;
            text-decoration: underline;
        }

        .btn-link:hover {
            color: #555;
        }

        .password-toggle {
            position: absolute;
            top: 70%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.5rem;
            color: #000;
        }

        .cat-gif {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 150px;
        }

        .color-choice {
            position: fixed;
            bottom: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
        }

        .color-choice button {
            margin-top: 5px;
            border-radius: 15px;
            padding: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .default-color {
            background: linear-gradient(to right, #ffcccb, #add8e6);
            color: white;
            border: 2px solid #32CD32;
        }

        .btn-pink {
            background-color: #ffcccb;
            color: black;
            border: 2px solid #ff69b4;
        }

        .btn-blue {
            background-color: #add8e6;
            color: black;
            border: 2px solid #1e90ff;
        }

        .active {
            border: 2px solid #32CD32;
        }

        .alert {
            display: none;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 5.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container col-md-6 col-lg-4 col-sm-8">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" id="alert"><?php echo $message; ?></div>
        <?php endif; ?>
        <h2>Register</h2>
        <form id="register-form" method="post" action="">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" name="first_name" id="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" name="last_name" id="last_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-link">Login</a>
        </div>
    </div>
    <div class="color-choice">
        <button id="default-color" class="default-color" onclick="setColor('default')">Default Color</button>
        <button id="baby-pink" class="btn-pink" onclick="setColor('baby-pink')">Baby Pink</button>
        <button id="baby-blue" class="btn-blue" onclick="setColor('baby-blue')">Baby Blue</button>
    </div>
    <img src="images/kawaii-cat.gif" alt="Cat playing with yarn" class="cat-gif">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const selectedColor = localStorage.getItem('selectedColor') || 'default';
            setColor(selectedColor);

            // Display message if exists
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.display = 'block';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, <?php echo ($messageType == "success") ? "15000" : "6000"; ?>);
            }
        });

        function setColor(color) {
            localStorage.setItem('selectedColor', color);
            if (color === 'default') {
                document.body.style.background = 'linear-gradient(to right, #ffcccb, #add8e6)';
                document.querySelectorAll('.container').forEach(container => {
                    container.style.backgroundColor = 'white';
                });
                document.querySelectorAll('button').forEach(button => {
                    button.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
                    button.style.color = 'white';
                });
                document.querySelectorAll('.color-choice button').forEach(button => {
                    button.classList.remove('active');
                });
                document.getElementById('default-color').classList.add('active');
            } else if (color === 'baby-pink') {
                document.body.style.backgroundColor = '#ffcccb';
                document.querySelectorAll('.container').forEach(container => {
                    container.style.backgroundColor = '#ffb6c1';
                });
                document.querySelectorAll('button').forEach(button => {
                    button.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
                    button.style.color = 'white';
                });
                document.querySelectorAll('.color-choice button').forEach(button => {
                    button.classList.remove('active');
                });
                document.getElementById('baby-pink').classList.add('active');
            } else if (color === 'baby-blue') {
                document.body.style.backgroundColor = '#add8e6';
                document.querySelectorAll('.container').forEach(container => {
                    container.style.backgroundColor = '#87ceeb';
                });
                document.querySelectorAll('button').forEach(button => {
                    button.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
                    button.style.color = 'white';
                });
                document.querySelectorAll('.color-choice button').forEach(button => {
                    button.classList.remove('active');
                });
                document.getElementById('baby-blue').classList.add('active');
            }
        }

        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('click', function(e) {
                if (!this.closest('form')) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');
                    document.body.classList.add('fade-out');
                    setTimeout(() => {
                        if (href) {
                            if (target) {
                                window.open(href, target);
                            } else {
                                window.location.href = href;
                            }
                        } else {
                            this.closest('form').submit();
                        }
                    }, 500);
                }
            });
        });

        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('confirm_password');
        toggleConfirmPassword.addEventListener('click', function (e) {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Form validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;

            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters long.');
            } else if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Passwords do not match.');
            } else if (!firstName || !lastName || !email || !phone) {
                e.preventDefault();
                showAlert('All fields are required.');
            }
        });

        function showAlert(message) {
            const alert = document.getElementById('error-alert');
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 6000);
        }
    </script>
</body>
</html>
