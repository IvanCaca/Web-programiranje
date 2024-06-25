<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            transition: background-color 0.5s ease;
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
    </style>
</head>
<body class="fade-in">
    <div class="container col-md-6 col-lg-4 col-sm-8">
        <h2>Login</h2>
        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="d-flex justify-content-between mt-3">
            <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
            <a href="register.php" class="btn btn-link">Register</a>
        </div>
        <div class="text-center mt-3">
            <form method="post" action="guest.php">
                <button type="submit" class="btn btn-secondary w-100">Continue as Guest</button>
            </form>
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

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
