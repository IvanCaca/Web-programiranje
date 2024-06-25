<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';
$user_id = $_SESSION['user_id'];
$profile_updated = false;
$account_deleted = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_account'])) {
        // Brisanje naloga
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        session_destroy();
        $account_deleted = true;
    } else {
        $first_name = htmlspecialchars($_POST['first_name']);
        $last_name = htmlspecialchars($_POST['last_name']);
        $phone = htmlspecialchars($_POST['phone']);
        $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        if ($password) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $user_id]);
        }

        $profile_updated = true;
    }
}

// Učitavanje podataka korisnika iz baze
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
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
            position: relative;
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

        .btn-danger {
            background-color: red;
            border: none;
            border-radius: 15px;
        }

        .btn-danger:hover {
            background-color: darkred;
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

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-password {
            position: absolute;
            right: 10px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .popup-message {
            display: none;
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #32CD32;
            color: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            animation: fadeInOut 5s forwards;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Profile</h1>
        <?php if ($account_deleted): ?>
            <div class="popup-message" id="popup-message">Your account has been deleted.</div>
        <?php else: ?>
            <form method="post" action="profile.php">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <div class="form-group password-toggle">
                    <label for="password">New Password (leave blank if not changing):</label>
                    <input type="password" class="form-control" name="password" id="password">
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePassword()"></i>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Profile</button>
            </form>
            <form method="post" action="profile.php" class="mt-3">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">Delete Account</button>
            </form>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
        </div>
        <?php if ($profile_updated): ?>
            <div class="popup-message" id="popup-message">Profile updated successfully!</div>
        <?php endif; ?>
    </div>
    <div class="color-choice">
        <button id="default-color" class="default-color" onclick="setColor('default')">Default Color</button>
        <button id="baby-pink" class="btn-pink" onclick="setColor('baby-pink')">Baby Pink</button>
        <button id="baby-blue" class="btn-blue" onclick="setColor('baby-blue')">Baby Blue</button>
    </div>
    <img src="images/cat-cute.gif" alt="Cat playing with yarn" class="cat-gif">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const selectedColor = localStorage.getItem('selectedColor') || 'default';
            setColor(selectedColor);
            if (document.getElementById('popup-message')) {
                const popup = document.getElementById('popup-message');
                popup.style.display = 'block';
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 5000);
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

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordToggle = document.querySelector('.toggle-password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordToggle.classList.remove('bi-eye-slash');
                passwordToggle.classList.add('bi-eye');
            } else {
                passwordField.type = 'password';
                passwordToggle.classList.remove('bi-eye');
                passwordToggle.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>
</html>
