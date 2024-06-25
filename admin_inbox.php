<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';

// Učitavanje svih poruka iz baze
$stmt = $pdo->prepare("SELECT messages.message_id, users.email, messages.message, messages.created_at, messages.admin_reply, messages.replied_at FROM messages JOIN users ON messages.user_id = users.user_id");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Odpovedanje na poruke
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reply_message_id'])) {
        $reply_message_id = $_POST['reply_message_id'];
        $admin_reply = htmlspecialchars($_POST['admin_reply']);
        $stmt = $pdo->prepare("UPDATE messages SET admin_reply = ?, replied_at = NOW() WHERE message_id = ?");
        $stmt->execute([$admin_reply, $reply_message_id]);
    } elseif (isset($_POST['delete_message_id'])) {
        $delete_message_id = $_POST['delete_message_id'];

        // Prvo obrišite sve odgovore vezane za ovu poruku
        $stmt = $pdo->prepare("DELETE FROM messages WHERE parent_message_id = ?");
        $stmt->execute([$delete_message_id]);

        // Onda obrišite glavnu poruku
        $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$delete_message_id]);
    }
    header('Location: admin_inbox.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Inbox</title>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        <h2>Admin Inbox</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                        <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($message['admin_reply']); ?></td>
                        <td>
                            <?php if (is_null($message['admin_reply'])): ?>
                                <form method="post" action="admin_inbox.php" style="display: inline-block;">
                                    <textarea name="admin_reply" class="form-control" required></textarea>
                                    <input type="hidden" name="reply_message_id" value="<?php echo $message['message_id']; ?>">
                                    <button type="submit" class="btn btn-primary mt-2">Reply</button>
                                </form>
                            <?php else: ?>
                                <span>Replied at <?php echo htmlspecialchars($message['replied_at']); ?></span>
                            <?php endif; ?>
                            <form method="post" action="admin_inbox.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                <input type="hidden" name="delete_message_id" value="<?php echo $message['message_id']; ?>">
                                <button type="submit" class="btn btn-danger mt-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    <img src="images/text-kitty.gif" alt="Cat playing with yarn" class="cat-gif">
</body>
</html>
