<?php
session_start();
require_once(__DIR__ . '/../db_connect.php');

if (isset($_SESSION['user_id'])) {
    require_once(__DIR__ . '/check_users.php');
    header('Location: ' . BASE_URL . (isAdmin($_SESSION['user_id']) ? 'admin/index.php' : 'index.php'));
    exit();
}

$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

$mode = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NS BLOCK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #191919;
            color: #f5f5f5;
        }

        .auth-page {
            width: min(100%, 520px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .auth-brand {
            margin-bottom: 1.6rem;
            color: #ffffff;
            font-size: clamp(2.2rem, 5vw, 3.1rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0;
            text-decoration: none;
        }

        .auth-brand span:first-child {
            color: #ef4444;
        }

        .auth-card {
            width: 100%;
            min-height: 560px;
            padding: clamp(1.5rem, 5vw, 3rem);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            background: #1d1d1d;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.28);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .auth-title {
            margin-bottom: 1.6rem;
            font-size: clamp(1.8rem, 4vw, 2.2rem);
            line-height: 1.1;
            font-weight: 900;
            color: #ffffff;
        }

        .auth-field {
            width: min(100%, 360px);
            margin-bottom: 1.35rem;
            text-align: left;
        }

        .auth-field label {
            display: block;
            margin-bottom: 0.55rem;
            font-weight: 700;
            color: #ffffff;
        }

        .auth-field input {
            width: 100%;
            min-height: 56px;
            padding: 1rem 1.05rem;
            border: 2px solid rgba(255, 255, 255, 0.22);
            border-radius: 6px;
            outline: none;
            background: #1d1d1d;
            color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-field input::placeholder {
            color: #888888;
        }

        .auth-field input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.16);
        }

        .auth-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 118px;
            min-height: 50px;
            margin-top: 0.35rem;
            padding: 0.8rem 1.65rem;
            border: none;
            border-radius: 999px;
            background: #ef3b3b;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-button:hover {
            transform: translateY(-2px);
            background: #dc2626;
            box-shadow: 0 12px 24px rgba(239, 68, 68, 0.22);
        }

        .auth-bottom-dialog {
            margin-top: 1.25rem;
            font-weight: 600;
            color: #ffffff;
        }

        .auth-bottom-dialog a {
            color: #ef4444;
            text-decoration: none;
            font-weight: 800;
        }

        .auth-bottom-dialog a:hover {
            text-decoration: underline;
        }

        .auth-message {
            margin-bottom: 1.25rem;
            padding: 0.9rem 1rem;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }

        .auth-message.error {
            border-color: rgba(239, 68, 68, 0.42);
        }

        .auth-message.success {
            border-color: rgba(34, 197, 94, 0.42);
        }

        .back-link {
            display: inline-flex;
            margin-top: 1rem;
            color: #bdbdbd;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <a class="auth-brand" href="<?php echo BASE_URL; ?>index.php" aria-label="NS BLOCK">
            <span>NS</span><span>BLOCK</span>
        </a>
        <section class="auth-card" aria-label="Halaman akun">
            <?php if ($message): ?>
                <div class="auth-message <?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" class="auth-form <?php echo $mode === 'login' ? 'active' : ''; ?>" action="<?php echo BASE_URL; ?>auth/login_action.php" method="POST">
                <h1 class="auth-title">Login</h1>
                <div class="auth-field">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" placeholder="Masukkan email" required autofocus>
                </div>
                <div class="auth-field">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="auth-button">Masuk</button>
                <p class="auth-bottom-dialog">Don't have an account? <a href="?mode=register" data-auth-switch="register">Create one</a></p>
            </form>

            <form id="registerForm" class="auth-form <?php echo $mode === 'register' ? 'active' : ''; ?>" action="<?php echo BASE_URL; ?>auth/register_action.php" method="POST">
                <h1 class="auth-title">Registrasi</h1>
                <div class="auth-field">
                    <label for="register_username">Username</label>
                    <input type="text" id="register_username" name="username" placeholder="Masukkan username" minlength="3" required>
                </div>
                <div class="auth-field">
                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" placeholder="Masukkan email" required>
                </div>
                <div class="auth-field">
                    <label for="register_password">Password</label>
                    <input type="password" id="register_password" name="password" placeholder="Minimal 6 karakter" minlength="6" required>
                </div>
                <div class="auth-field">
                    <label for="register_confirm_password">Konfirmasi Password</label>
                    <input type="password" id="register_confirm_password" name="confirm_password" placeholder="Ulangi password" minlength="6" required>
                </div>
                <button type="submit" class="auth-button">Daftar</button>
                <p class="auth-bottom-dialog">Already have an account? <a href="?mode=login" data-auth-switch="login">Login here</a></p>
            </form>

            <a class="back-link" href="<?php echo BASE_URL; ?>index.php">Kembali ke halaman utama</a>
        </section>
    </main>

    <script>
        (function () {
            const forms = {
                login: document.getElementById('loginForm'),
                register: document.getElementById('registerForm')
            };

            function showForm(mode) {
                const activeMode = mode === 'register' ? 'register' : 'login';
                Object.keys(forms).forEach(function (key) {
                    if (forms[key]) forms[key].classList.toggle('active', key === activeMode);
                });
                history.replaceState(null, '', '?mode=' + activeMode);
            }

            document.querySelectorAll('[data-auth-switch]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    showForm(this.dataset.authSwitch);
                });
            });
        })();
    </script>
</body>
</html>
