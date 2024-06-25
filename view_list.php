<?php
session_start();
require 'php/db_config.php';

$list_id = $_GET['list_id'];
$stmt = $pdo->prepare("SELECT * FROM lists WHERE list_id = ?");
$stmt->execute([$list_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM items WHERE list_id = ?");
$stmt->execute([$list_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_name']) && isset($_POST['item_amount'])) {
    $item_name = strtolower(htmlspecialchars($_POST['item_name']));
    $item_amount = (int)$_POST['item_amount'] ?: 1;
    $stmt = $pdo->prepare("INSERT INTO items (list_id, item_name, amount) VALUES (?, ?, ?)");
    $stmt->execute([$list_id, $item_name, $item_amount]);
    header('Location: view_list.php?list_id=' . $list_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_list'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE list_id = ? AND is_checked = 0");
    $stmt->execute([$list_id]);
    $remaining_items = $stmt->fetchColumn();

    if ($remaining_items == 0) {
        $stmt = $pdo->prepare("UPDATE lists SET status = 'finished' WHERE list_id = ?");
        $stmt->execute([$list_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE lists SET status = 'created' WHERE list_id = ?");
        $stmt->execute([$list_id]);
    }

    header('Location: view_list.php?list_id=' . $list_id);
    exit;
}

if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    header('Location: view_list.php?list_id=' . $list_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View List</title>
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

        h1, h2 {
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

        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #ffffff;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 15px;
            text-decoration: none;
            margin: 10px 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: rgba(0, 0, 0, 0.8);
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
            h1, h2 {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .navbar-nav .nav-link {
                font-size: 1.2rem;
            }

            .container {
                width: 100%;
                padding: 25px;
                margin: 15px 0;
            }

            .list-container {
                flex: 1 1 100%;
                margin: 10px 0;
            }

            li {
                font-size: 1.25rem;
                padding: 20px;
            }

            .color-choice button {
                padding: 10px;
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
                    <li class="nav-item" onclick="window.location='inbox.php'">
                        <a class="nav-link" href="inbox.php">Inbox</a>
                    </li>
                    <li class="nav-item" onclick="window.location='logout.php'">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>View List: <?php echo htmlspecialchars($list['list_name']); ?></h2>
        <form method="post" action="view_list.php?list_id=<?php echo $list_id; ?>" class="mb-4">
            <div class="input-group">
                <input type="text" name="item_name" class="form-control" placeholder="Enter item name" required>
                <input type="number" name="item_amount" class="form-control" value="1" min="1" placeholder="Amount">
                <button type="submit" class="btn btn-primary">Add Item</button>
            </div>
        </form>
        <form method="post" action="view_list.php?list_id=<?php echo $list_id; ?>">
            <ul id="item-list">
                <?php foreach ($items as $item): ?>
                    <li id="item-<?php echo $item['item_id']; ?>" onclick="toggleCheck(<?php echo $item['item_id']; ?>)">
                        <div class="checkbox-container">
                            <input type="checkbox" name="check_item" value="<?php echo $item['item_id']; ?>" <?php echo $item['is_checked'] ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($item['item_name']); ?> (<?php echo $item['amount']; ?>)
                        </div>
                        <i class="bi bi-trash3" onclick="deleteItem(<?php echo $item['item_id']; ?>)" style="cursor: pointer;"></i>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">Back</a>
                <button type="submit" name="submit_list" class="btn btn-success">Submit</button>
            </div>
        </form>
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

        function toggleCheck(itemId) {
            const checkbox = document.querySelector(`#item-${itemId} input[type="checkbox"]`);
            checkbox.checked = !checkbox.checked;
            checkItem(checkbox);
        }

        function checkItem(checkbox) {
            const itemId = checkbox.value;
            const isChecked = checkbox.checked ? 1 : 0;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_item.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Item check status updated
                }
            };
            xhr.send(`item_id=${itemId}&is_checked=${isChecked}`);
        }

        function deleteItem(itemId) {
            event.stopPropagation();
            if (confirm('Are you sure you want to delete this item?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'delete_item.php?item_id=' + itemId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const itemElement = document.getElementById('item-' + itemId);
                        if (itemElement) {
                            itemElement.parentNode.removeChild(itemElement);
                        }
                    }
                };
                xhr.send();
            }
        }

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
