<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';

$user_id = $_SESSION['user_id'];
$message_sent = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = htmlspecialchars($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $message]);

    $message_sent = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Caveat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(to right, #ffcccb, #add8e6);
            font-family: Arial, sans-serif;
            transition: background-color 0.5s ease;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            font-family: 'Pacifico', cursive;
            font-size: 2.5rem;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control, .form-control:focus, .form-control:active {
            border-radius: 10px;
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

        .popup-message {
            display: none;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 10px;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        .show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container col-md-6 col-lg-4 col-sm-8">
        <h1>Contact Admin</h1>
        <form method="post" action="contact_admin.php">
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Message</button>
        </form>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-link">Back to Dashboard</a>
        </div>
    </div>
    <div class="color-choice">
        <button id="default-color" class="default-color" onclick="setColor('default')">Default Color</button>
        <button id="baby-pink" class="btn-pink" onclick="setColor('baby-pink')">Baby Pink</button>
        <button id="baby-blue" class="btn-blue" onclick="setColor('baby-blue')">Baby Blue</button>
    </div>
    <img src="images/text-kitty.gif" alt="Cat playing with yarn" class="cat-gif">
    <div id="popup" class="popup-message">Your message has been sent to the administrator.</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const selectedColor = localStorage.getItem('selectedColor') || 'default';
            setColor(selectedColor);
            <?php if ($message_sent): ?>
                showPopup();
            <?php endif; ?>
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

        function showPopup() {
            const popup = document.getElementById('popup');
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
