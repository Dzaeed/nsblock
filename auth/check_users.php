<?php
/**
 * check_users.php
 * Fungsi untuk mengecek apakah tabel users kosong atau sudah ada user
 */

require_once(__DIR__ . '/../db_connect.php');

function hasUsers() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total'] > 0;
}

function getUserById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Get the first (earliest) user id — considered the main admin account.
 */
function getPrimaryUserId() {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row['id'] : null;
}

function getUserRole($userId) {
    $primaryId = getPrimaryUserId();
    return $primaryId !== null && (int) $userId === $primaryId ? 'admin' : 'pelanggan';
}

function isAdmin($userId) {
    return getUserRole($userId) === 'admin';
}

function enforceAdminAccess() {
    if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Akses ditolak. Anda tidak memiliki izin masuk admin.';
        $_SESSION['message_type'] = 'error';
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

function enforceUserAccess($message = 'Silakan login atau registrasi terlebih dahulu untuk memesan.') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = 'error';
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

?>
