<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';

// Učitavanje svih korisnika iz baze
$stmt = $pdo->prepare("SELECT users.user_id, users.email, users.first_name, users.last_name, users.phone, users.role, COUNT(lists.list_id) as list_count FROM users LEFT JOIN lists ON users.user_id = lists.user_id GROUP BY users.user_id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Banovanje, unbanovanje, promena role i brisanje naloga
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ban_user_id'])) {
        $user_id = $_POST['ban_user_id'];
        $stmt = $pdo->prepare("UPDATE users SET role = 'banned' WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    if (isset($_POST['unban_user_id'])) {
        $user_id = $_POST['unban_user_id'];
        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    if (isset($_POST['make_admin_user_id'])) {
        $user_id = $_POST['make_admin_user_id'];
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    if (isset($_POST['delete_user_id'])) {
        $user_id = $_POST['delete_user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    header('Location: admin_tool.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Caveat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <style>
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
            margin-top: 20px;
        }
        .cat-gif {
            position: fixed;
            bottom: 50px;
            right: 50px;
            width: 200px;
        }
        @keyframes shimmer {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
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
                        <a class="nav-link" href="admin_inbox.php">Inbox</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="mb-4">Admin Tool</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>List Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['list_count']); ?></td>
                        <td>
                            <?php if ($user['role'] != 'admin'): ?>
                                <form method="post" action="admin_tool.php" style="display:inline;">
                                    <input type="hidden" name="ban_user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-warning">Ban</button>
                                </form>
                                <form method="post" action="admin_tool.php" style="display:inline;">
                                    <input type="hidden" name="unban_user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-success">Unban</button>
                                </form>
                                <form method="post" action="admin_tool.php" style="display:inline;">
                                    <input type="hidden" name="make_admin_user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-primary">Make Admin</button>
                                </form>
                                <form method="post" action="admin_tool.php" style="display:inline;">
                                    <input type="hidden" name="delete_user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    <img src="images/scratch-cat-claws.gif" alt="Cat playing with yarn" class="cat-gif">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
