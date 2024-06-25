<?php
session_start();
require 'php/db_config.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Ukupan broj stavki
$stmt = $pdo->prepare("
    SELECT SUM(items.amount) AS total_items
    FROM items
    JOIN lists ON items.list_id = lists.list_id
    WHERE lists.user_id = ?
");
$stmt->execute([$user_id]);
$total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

// Ukupan broj završenih stavki
$stmt = $pdo->prepare("
    SELECT SUM(items.amount) AS completed_items
    FROM items
    JOIN lists ON items.list_id = lists.list_id
    WHERE lists.user_id = ? AND items.is_checked = 1
");
$stmt->execute([$user_id]);
$completed_items = $stmt->fetch(PDO::FETCH_ASSOC)['completed_items'];

// Broj stavki po listama
$stmt = $pdo->prepare("
    SELECT lists.list_name, lists.list_id, SUM(items.amount) AS total_items
    FROM items
    JOIN lists ON items.list_id = lists.list_id
    WHERE lists.user_id = ?
    GROUP BY lists.list_name, lists.list_id
");
$stmt->execute([$user_id]);
$list_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Broj unetih proizvoda
$stmt = $pdo->prepare("
    SELECT items.item_name, SUM(items.amount) AS total_entered
    FROM items
    JOIN lists ON items.list_id = lists.list_id
    WHERE lists.user_id = ?
    GROUP BY items.item_name
");
$stmt->execute([$user_id]);
$item_entered_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Broj kupljenih proizvoda
$stmt = $pdo->prepare("
    SELECT items.item_name, SUM(items.amount) AS total_purchased
    FROM items
    JOIN lists ON items.list_id = lists.list_id
    WHERE lists.user_id = ? AND items.is_checked = 1
    GROUP BY items.item_name
");
$stmt->execute([$user_id]);
$item_purchased_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Broj ukupnih i završenih listi
$stmt = $pdo->prepare("
    SELECT 
    (SELECT COUNT(*) FROM lists WHERE user_id = ?) AS total_lists, 
    (SELECT COUNT(*) FROM lists WHERE user_id = ? AND status = 'finished') AS completed_lists
");
$stmt->execute([$user_id, $user_id]);
$lists_counts = $stmt->fetch(PDO::FETCH_ASSOC);
$total_lists = $lists_counts['total_lists'];
$completed_lists = $lists_counts['completed_lists'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statistics</title>
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

        body {
            background: linear-gradient(to right, #ffcccb, #add8e6);
            font-family: Arial, sans-serif;
        }

        .navbar {
            border-radius: 15px;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: top 0.3s;
        }

        .navbar-brand {
            font-family: 'Pacifico', cursive;
            font-size: 2rem;
            animation: shimmer 5s infinite;
            color: white;
            transition: color 0.3s;
        }

        .navbar-brand:hover {
            color: gray;
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

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }

        h2 {
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

        .statistics {
            text-align: center;
            font-size: 1.5rem;
            margin-top: 20px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            margin-top: 20px;
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
            bottom: 50px;
            right: 50px;
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

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Back to dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_inbox.php' : 'user_inbox.php'; ?>">Inbox</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Statistics</h2>
        <div class="statistics">
            <p>Total items: <?php echo $total_items; ?></p>
            <p>Completed items: <?php echo $completed_items; ?></p>
        </div>
        <div class="chart-container">
            <canvas id="itemsChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="enteredItemsChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="purchasedItemsChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="listsPieChart"></canvas>
        </div>
    </div>
    <div class="color-choice">
        <button id="color-picker" class="default-color dropdown-toggle" onclick="toggleColorDropdown()">🎨</button>
        <div class="color-dropdown">
            <button id="default-color" class="default-color" onclick="setColor('default')">Default Color</button>
            <button id="baby-pink" class="btn-pink" onclick="setColor('baby-pink')">Baby Pink</button>
            <button id="baby-blue" class="btn-blue" onclick="setColor('baby-blue')">Baby Blue</button>
        </div>
    </div>
    <img src="images/cat-gray.gif" alt="Cat playing with yarn" class="cat-gif">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectedTheme = localStorage.getItem('selectedTheme');
            if (selectedTheme) {
                applyTheme(selectedTheme);
            }
        });

        function setColor(color) {
            localStorage.setItem('selectedTheme', color);
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

        const listData = <?php echo json_encode($list_data); ?>;
        const listLabels = listData.map(data => `${data.list_name} (ID: ${data.list_id})`);
        const listItemsData = listData.map(data => data.total_items);

        const itemEnteredData = <?php echo json_encode($item_entered_data); ?>;
        const itemEnteredLabels = itemEnteredData.map(data => data.item_name);
        const itemEnteredAmounts = itemEnteredData.map(data => data.total_entered);

        const itemPurchasedData = <?php echo json_encode($item_purchased_data); ?>;
        const itemPurchasedLabels = itemPurchasedData.map(data => data.item_name);
        const itemPurchasedAmounts = itemPurchasedData.map(data => data.total_purchased);

        const totalLists = <?php echo $total_lists; ?>;
        const completedLists = <?php echo $completed_lists; ?>;

        // Prvi grafikon: Broj itema po listama
        const ctxItemsChart = document.getElementById('itemsChart').getContext('2d');
        new Chart(ctxItemsChart, {
            type: 'bar',
            data: {
                labels: listLabels,
                datasets: [{
                    label: 'Number of Items',
                    data: listItemsData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Drugi grafikon: Broj unetih proizvoda
        const ctxEnteredItemsChart = document.getElementById('enteredItemsChart').getContext('2d');
        new Chart(ctxEnteredItemsChart, {
            type: 'bar',
            data: {
                labels: itemEnteredLabels,
                datasets: [{
                    label: 'Number of Entered Items',
                    data: itemEnteredAmounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Treći grafikon: Broj kupljenih proizvoda
        const ctxPurchasedItemsChart = document.getElementById('purchasedItemsChart').getContext('2d');
        new Chart(ctxPurchasedItemsChart, {
            type: 'bar',
            data: {
                labels: itemPurchasedLabels,
                datasets: [{
                    label: 'Number of Purchased Items',
                    data: itemPurchasedAmounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie grafikon: Procentualni broj završenih listi
        const ctxListsPieChart = document.getElementById('listsPieChart').getContext('2d');
        new Chart(ctxListsPieChart, {
            type: 'pie',
            data: {
                labels: ['Completed Lists', 'Incomplete Lists'],
                datasets: [{
                    data: [completedLists, totalLists - completedLists],
                    backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                document.body.style.animation = 'fadeOut 1s forwards';
                setTimeout(() => {
                    window.location.href = this.href;
                }, 1000);
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
