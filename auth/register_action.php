<?php
/**
 * register_action.php
 * Proses registrasi user
 */

session_start();
require_once(__DIR__ . '/../db_connect.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert ke database menggunakan prepared statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
                // Reset form
                $username = '';
                $email = '';
                $password = '';
                $confirm_password = '';
            } else {
                // Cek error tipe
                if (strpos($stmt->error, 'Duplicate entry') !== false) {
                    if (strpos($stmt->error, 'username') !== false) {
                        $error = 'Username sudah terdaftar!';
                    } else {
                        $error = 'Email sudah terdaftar!';
                    }
                } else {
                    $error = 'Terjadi kesalahan saat registrasi.';
                }
            }
            $stmt->close();
        } else {
            $error = 'Kesalahan sistem database.';
        }
    }
}

// Redirect dengan pesan
if ($error || $success) {
    $_SESSION['message'] = $error ?: $success;
    $_SESSION['message_type'] = $error ? 'error' : 'success';
    header('Location: ' . BASE_URL . ($error ? 'auth/login.php?mode=register' : 'auth/login.php'));
    exit();
}
?>
