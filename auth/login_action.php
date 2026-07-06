<?php
/**
 * login_action.php
 * Proses login user
 */

session_start();
require_once(__DIR__ . '/../db_connect.php');
require_once(__DIR__ . '/check_users.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Prepared statement untuk cari user
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Login berhasil - set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $email;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['role'] = getUserRole($user['id']);
                    $_SESSION['is_admin'] = isAdmin($user['id']);
                    $_SESSION['message'] = 'Login berhasil!';
                    $_SESSION['message_type'] = 'success';

                    header('Location: ' . BASE_URL . ($_SESSION['is_admin'] ? 'admin/index.php' : 'index.php'));
                    exit();
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Email tidak ditemukan!';
            }
            $stmt->close();
        } else {
            $error = 'Kesalahan sistem database.';
        }
    }
}

// Jika ada error, set session message dan redirect
if ($error) {
    $_SESSION['message'] = $error;
    $_SESSION['message_type'] = 'error';
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}
?>
