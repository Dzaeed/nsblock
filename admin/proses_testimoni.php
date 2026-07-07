<?php
// File: admin/proses_testimoni.php
session_start();
require_once('../db_connect.php');
require_once('../auth/check_users.php');
enforceAdminAccess();

function redirect_back(): void {
    header('Location: index.php#testimonials');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? null;

// Handle direct actions via GET (used by table action buttons)
if ($action !== null && $id > 0) {
    $success = false;
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE testimonials SET is_approved = 1 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'unapprove' || $action === 'hide') { // 'hide' as alias
        $stmt = $conn->prepare("UPDATE testimonials SET is_approved = 0 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
        }
    }
    $conn->close();
    redirect_back();
}

// Fallback interactive page (optional)
$status = '';
if (!is_numeric($id) || $id === 0) {
    $status = 'tidak valid';
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $success = false;
    if (in_array($postAction, ['approve', 'hide'], true)) {
        $new_status = ($postAction === 'approve') ? 1 : 0;
        $stmt = $conn->prepare("UPDATE testimonials SET is_approved = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $new_status, $id);
            $success = $stmt->execute();
            $stmt->close();
        }
    } elseif ($postAction === 'delete') {
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
        }
    }
    $status = $success ? 'berhasil' : 'tidak valid';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Proses Testimoni</title>
<style>
    body { font-family: 'Poppins', sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .container { background: #fff; padding: 30px 40px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); text-align: center; width: 350px; }
    h2 { color: #2c3e50; margin-bottom: 20px; }
    button { margin: 8px; padding: 12px 25px; border:none; border-radius: 10px; font-weight:bold; cursor:pointer; transition: transform 0.2s; }
    button:hover { transform: scale(1.05); }
    .approve { background: #27ae60; color: #fff; }
    .hide { background: #f39c12; color: #fff; }
    .delete { background: #e74c3c; color: #fff; }
    .message { margin-top: 20px; font-size: 22px; font-weight: bold; padding: 15px; border-radius: 10px; }
    .success { background: #27ae60; color: #fff; }
    .error { background: #e74c3c; color: #fff; }
    a { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; font-weight: bold; }
    a:hover { text-decoration: underline; }
    
    /* Custom Confirmation Modal Styles */
    .confirm-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 10, 10, 0.75);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .confirm-modal-overlay.active {
        display: flex;
        opacity: 1;
    }
    .confirm-modal {
        background: #fff;
        padding: 25px;
        border-radius: 20px;
        max-width: 340px;
        width: 90%;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        font-family: 'Poppins', sans-serif;
    }
    .confirm-modal h3 {
        margin: 0 0 10px;
        color: #e74c3c;
        font-size: 1.3rem;
    }
    .confirm-modal p {
        color: #666;
        font-size: 0.9rem;
        margin: 0 0 20px;
        line-height: 1.5;
    }
    .confirm-modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .confirm-modal-actions button {
        margin: 0;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.85rem;
    }
    .btn-secondary {
        background: #e9ecef;
        color: #495057;
    }
    .btn-secondary:hover {
        background: #dee2e6;
        transform: none;
    }
    .btn-danger {
        background: #e74c3c;
        color: #fff;
    }
    .btn-danger:hover {
        background: #c0392b;
        transform: none;
    }
</style>
</head>
<body>
<div class="container">
    <h2>🛠️ Testimoni Admin</h2>

    <?php if ($status === ''): ?>
        <p>ID Testimoni: <strong><?= htmlspecialchars($id) ?></strong></p>
        <form method="POST" id="testimoniForm">
            <button type="submit" name="action" value="approve" class="approve">✅ Approve</button>
            <button type="submit" name="action" value="hide" class="hide">👁️ Hide</button>
            <button type="button" id="deleteBtn" class="delete">🗑️ Delete</button>
        </form>
    <?php else: ?>
        <div class="message <?= $status === 'berhasil' ? 'success' : 'error' ?>">
            <?= $status === 'berhasil' ? '✔ Berhasil' : '❌ Tidak valid' ?>
        </div>
    <?php endif; ?>

    <a href="index.php#testimonials">← Kembali ke daftar testimoni</a>
</div>

<div class="confirm-modal-overlay" id="confirmDeleteOverlay">
    <div class="confirm-modal">
        <h3>Konfirmasi Hapus</h3>
        <p>Yakin ingin menghapus testimoni ini?</p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn-secondary" id="cancelBtn">Batal</button>
            <button type="button" class="btn-danger" id="confirmBtn">Hapus</button>
        </div>
    </div>
</div>

<script>
(function() {
    var deleteBtn = document.getElementById('deleteBtn');
    var overlay = document.getElementById('confirmDeleteOverlay');
    var cancelBtn = document.getElementById('cancelBtn');
    var confirmBtn = document.getElementById('confirmBtn');
    var form = document.getElementById('testimoniForm');

    if (deleteBtn && overlay && form) {
        deleteBtn.addEventListener('click', function() {
            overlay.style.display = 'flex';
            setTimeout(function() {
                overlay.classList.add('active');
            }, 10);
        });

        var closeDialog = function() {
            overlay.classList.remove('active');
            setTimeout(function() {
                overlay.style.display = 'none';
            }, 200);
        };

        cancelBtn.addEventListener('click', closeDialog);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeDialog();
            }
        });

        confirmBtn.addEventListener('click', function() {
            overlay.classList.remove('active');
            setTimeout(function() {
                overlay.style.display = 'none';
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'action';
                hiddenInput.value = 'delete';
                form.appendChild(hiddenInput);
                form.submit();
            }, 200);
        });
    }
})();
</script>
</body>
</html>
