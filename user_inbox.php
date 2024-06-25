<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'php/db_config.php';

// Učitavanje poruka za trenutnog korisnika iz baze
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ?");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Odpovedanje na poruke
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reply_message_id'])) {
        $reply_message_id = $_POST['reply_message_id'];
        $user_reply = htmlspecialchars($_POST['user_reply']);
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, parent_message_id) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $user_reply, $reply_message_id]);
        header('Location: user_inbox.php');
        exit;
    } elseif (isset($_POST['new_message'])) {
        $new_message = htmlspecialchars($_POST['new_message']);
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $new_message]);
        $_SESSION['message_sent'] = true;
        header('Location: user_inbox.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Inbox</title>
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
            position: relative;
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
        .alert-success {
            display: none;
            position: absolute;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
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
        <?php if (isset($_SESSION['message_sent']) && $_SESSION['message_sent']): ?>
            <div class="alert alert-success" id="success-alert">
                Message sent successfully!
            </div>
            <?php unset($_SESSION['message_sent']); ?>
        <?php endif; ?>
        
        <h1 class="mb-4">User Inbox</h1>
        
        <form method="post" action="user_inbox.php" class="mb-4">
            <div class="input-group">
                <textarea name="new_message" class="form-control" placeholder="Send a new message" required></textarea>
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Sent At</th>
                    <th>Admin Reply</th>
                    <th>Replied At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                        <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($message['admin_reply']); ?></td>
                        <td><?php echo htmlspecialchars($message['replied_at']); ?></td>
                        <td>
                            <?php if (!is_null($message['admin_reply'])): ?>
                                <form method="post" action="user_inbox.php" class="mb-0">
                                    <div class="input-group">
                                        <textarea name="user_reply" class="form-control" required></textarea>
                                        <input type="hidden" name="reply_message_id" value="<?php echo $message['message_id']; ?>">
                                        <button type="submit" class="btn btn-primary">Reply</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    <img src="images/text-kitty.gif" alt="Cat playing with yarn" class="cat-gif">
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

        <?php if (isset($_SESSION['message_sent']) && $_SESSION['message_sent']): ?>
            const successAlert = document.getElementById('success-alert');
            successAlert.style.display = 'block';
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
