<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';

$user_id = $_SESSION['user_id'];
$is_guest = ($_SESSION['user_id'] == 'guest');
$is_banned = false;

if ($is_guest) {
    $user = ['first_name' => 'Guest', 'role' => 'guest'];
} else {
    $stmt = $pdo->prepare("SELECT first_name, last_name, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['role'] == 'banned') {
        $is_banned = true;
    } else {
        $is_banned = false;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['list_name'])) {
            $list_name = htmlspecialchars($_POST['list_name']);
            $stmt = $pdo->prepare("INSERT INTO lists (user_id, list_name) VALUES (?, ?)");
            $stmt->execute([$user_id, $list_name]);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_list_id'])) {
            $list_id = $_POST['delete_list_id'];
            $stmt = $pdo->prepare("DELETE FROM lists WHERE list_id = ? AND user_id = ?");
            $stmt->execute([$list_id, $user_id]);
        }

        // Fetch TODO lists
        $stmt = $pdo->prepare("SELECT * FROM lists WHERE user_id = ? AND status = 'created'");
        $stmt->execute([$user_id]);
        $todo_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Finished lists
        $stmt = $pdo->prepare("SELECT * FROM lists WHERE user_id = ? AND status = 'finished'");
        $stmt->execute([$user_id]);
        $finished_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Caveat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        @keyframes shimmer {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        body {
            background: linear-gradient(to right, #ffcccb, #add8e6);
            font-family: Arial, sans-serif;
            transition: background-color 0.5s ease;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }

        .navbar {
            border-radius: 0 0 20px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: top 0.3s;
        }

        .navbar-brand {
            font-family: 'Pacifico', cursive;
            font-size: 2rem;
            animation: shimmer 5s infinite;
        }

        .navbar-nav .nav-link {
            font-weight: bold;
            font-size: 1.5rem;
            color: white;
            transition: color 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: gray;
        }

        .navbar-nav .nav-item {
            margin-right: 10px;
            padding: 10px;
            cursor: pointer;
        }

        .navbar-nav .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 100%; /* Updated for full width */
            margin: 20px auto;
            transition: background-color 0.5s ease;
        }

        h1 {
            text-align: center;
            font-family: 'Pacifico', cursive;
            font-size: 2.5rem;
        }

        .subtext {
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 1.5rem;
            margin-top: -10px;
        }

        .lists {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .list-container {
            flex: 1;
            margin: 10px;
            padding: 10px;
            background-color: #dcdcdc;
            border-radius: 30px;
        }

        .list-container h3 {
            text-align: center;
            font-size: 1.5rem;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #ffb6c1;
            margin: 5px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Caveat', cursive;
            font-size: 1.75rem;
            position: relative;
            cursor: pointer;
        }

        .todo-lists li::after {
            content: '...';
            position: absolute;
            right: 60px;
            animation: bounce 1s infinite;
        }

        .finished-lists li {
            background-color: #c8e6c9;
        }

        .finished-lists li::after {
            content: '✔️';
            position: absolute;
            right: 60px;
            animation: pop 2s infinite;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 15px;
            position: absolute;
            right: 10px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
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

        .cat-gif {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 200px;
        }

        .color-dropdown {
            display: none;
            flex-direction: column;
            position: absolute;
            bottom: 50px;
            left: 10px;
            background-color: gray;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        @media (max-width: 767.98px) {
            h1 {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .navbar-nav .nav-link {
                font-size: 1.2rem;
            }

            .container {
                width: 100%; /* Updated for full width */
                padding: 25px; /* Povećano za mobilne uređaje */
                margin: 15px 0;
            }

            .list-container {
                flex: 1 1 100%;
                margin: 10px 0;
            }

            li {
                font-size: 1.25rem;
                padding: 20px; /* Povećano za mobilne uređaje */
            }

            .color-choice button {
                padding: 10px; /* Povećano za mobilne uređaje */
            }

            .cat-gif {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Digital Online Lists</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item" onclick="window.location='profile.php'">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item" onclick="window.location='statistics.php'">
                        <a class="nav-link" href="statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item" onclick="window.location='<?php echo ($user['role'] == 'admin') ? 'admin_inbox.php' : 'user_inbox.php'; ?>'">
                        <a class="nav-link" href="<?php echo ($user['role'] == 'admin') ? 'admin_inbox.php' : 'user_inbox.php'; ?>">Inbox</a>
                    </li>
                    <?php if ($user['role'] == 'admin'): ?>
                        <li class="nav-item" onclick="window.location='admin_tool.php'">
                            <a class="nav-link" href="admin_tool.php">Admin Tool</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-auto" onclick="window.location='logout.php'">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
        <br>
        <p class="subtext">Let's create some lists</p>
        <?php if ($is_banned): ?>
            <p>You are banned from creating lists or adding items.</p>
            <p>If you think this is a mistake, please <a href="contact_admin.php">contact the administrator</a>.</p>
        <?php elseif ($is_guest): ?>
            <p>You are logged in as a guest. You can view the application but cannot create lists or add items.</p>
            <p>Please <a href="register.php">register</a> to gain full access.</p>
        <?php else: ?>
            <form method="post" action="dashboard.php" class="mb-4">
                <div class="input-group">
                    <input type="text" name="list_name" class="form-control" placeholder="Enter list name" required>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
            <div class="lists">
                <div class="list-container todo-lists">
                    <h3>TODO Lists <i class="bi bi-three-dots"></i></h3>
                    <ul id="todo-lists">
                        <?php foreach ($todo_lists as $list): ?>
                            <li id="list-<?php echo $list['list_id']; ?>">
                                <a href="view_list.php?list_id=<?php echo $list['list_id']; ?>" style="flex-grow: 1;"><?php echo htmlspecialchars($list['list_name']); ?></a>
                                <form method="post" action="dashboard.php" style="display:inline;">
                                    <input type="hidden" name="delete_list_id" value="<?php echo $list['list_id']; ?>">
                                    <i class="bi bi-trash" onclick="event.stopPropagation(); if(confirm('Are you sure you want to delete this list?')) { this.parentNode.submit(); }" style="cursor: pointer;"></i>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="list-container finished-lists">
                    <h3>Finished Lists <i class="bi bi-check"></i></h3>
                    <ul id="finished-lists">
                        <?php foreach ($finished_lists as $list): ?>
                            <li id="list-<?php echo $list['list_id']; ?>">
                                <a href="view_list.php?list_id=<?php echo $list['list_id']; ?>" style="flex-grow: 1;"><?php echo htmlspecialchars($list['list_name']); ?></a>
                                <form method="post" action="dashboard.php" style="display:inline;">
                                    <input type="hidden" name="delete_list_id" value="<?php echo $list['list_id']; ?>">
                                    <i class="bi bi-trash" onclick="event.stopPropagation(); if(confirm('Are you sure you want to delete this list?')) { this.parentNode.submit(); }" style="cursor: pointer;"></i>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="color-choice">
        <button id="color-picker" class="default-color dropdown-toggle" onclick="toggleColorDropdown()">🎨</button>
        <div class="color-dropdown">
            <button id="default-color" class="default-color" onclick="setColor('default')">Default Color</button>
            <button id="baby-pink" class="btn-pink" onclick="setColor('baby-pink')">Baby Pink</button>
            <button id="baby-blue" class="btn-blue" onclick="setColor('baby-blue')">Baby Blue</button>
        </div>
    </div>
    <img src="images/cat-cute.gif" alt="Cat playing with yarn" class="cat-gif">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const selectedColor = localStorage.getItem('selectedColor') || 'default';
            setColor(selectedColor);
        });

        function setColor(color) {
            localStorage.setItem('selectedColor', color);
            applyTheme(color);
        }

        function applyTheme(color) {
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

        function toggleColorDropdown() {
            const dropdown = document.querySelector('.color-dropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.color-dropdown');
            if (!event.target.closest('.color-choice')) {
                dropdown.style.display = 'none';
            }
        });

        document.querySelectorAll('.nav-item, .list-container li').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.querySelector('a').href;
                document.body.classList.add('fade-out');
                setTimeout(() => {
                    window.location.href = href;
                }, 500);
            });
        });

        document.body.classList.add('fade-in');

        // Animacije za fade-in i fade-out
        const style = document.createElement('style');
        style.innerHTML = `
            .fade-in {
                animation: fadeIn 0.5s ease-in-out;
            }

            .fade-out {
                animation: fadeOut 0.5s ease-in-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
