<?php

session_start();
require_once('../db_connect.php');
require_once('../auth/check_users.php');
enforceAdminAccess();

function redirect_products() {
    header("Location: index.php?section=products");
    exit();
}

// ========================================
// FUNGSI UPLOAD GAMBAR
// ========================================
function upload_image($file, $old_image = null) {
    $target_dir = "../uploads/";
    $image_name = uniqid() . '-' . basename($file["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validasi format file
    if ($imageFileType != "webp") {
        die("Maaf, hanya format WebP yang diperbolehkan.");
    }
    
    // Validasi konten file (MIME type / magic bytes)
    // TODO(security): Validate magic bytes/MIME type for file content verification
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);
    if ($mimeType !== 'image/webp') {
        die("Maaf, file yang diupload bukan merupakan gambar WebP yang valid.");
    }
    
    // Hapus gambar lama jika ada
    if ($old_image && file_exists($target_dir . $old_image)) {
        unlink($target_dir . $old_image);
    }
    
    // Upload file baru
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $image_name;
    } else {
        die("Maaf, terjadi error saat upload file.");
    }
}

// ========================================
// PROSES BERDASARKAN AKSI
// ========================================
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$harga = isset($_POST['harga']) ? (float) $_POST['harga'] : 0;
$stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$pavingRate = null;
if ($category === 'Paving Block') {
    $postedPavingRate = isset($_POST['paving_rate']) ? (int) $_POST['paving_rate'] : 27;
    $pavingRate = in_array($postedPavingRate, [27, 44], true) ? $postedPavingRate : 27;
}

switch ($aksi) {
    
    // ========================================
    // TAMBAH PRODUK BARU
    // ========================================
    case 'tambah':
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Upload gambar
            $image = upload_image($_FILES['image']);
            
            // Insert data ke database
            $stmt = $conn->prepare("INSERT INTO products (name, category, ukuran, harga, stok, paving_rate, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiiss", $_POST['name'], $category, $_POST['ukuran'], $harga, $stok, $pavingRate, $_POST['description'], $image);
            
            if ($stmt->execute()) {
                redirect_products();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            die("Gambar wajib diupload.");
        }
        break;
    
    // ========================================
    // EDIT PRODUK
    // ========================================
    case 'edit':
        $id = $_GET['id'];
        
        // Ambil data gambar lama
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_image = $result->fetch_assoc()['image'];
        $stmt->close();
        
        // Set gambar default (gambar lama)
        $image = $old_image;
        
        // Upload gambar baru jika ada
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = upload_image($_FILES['image'], $old_image);
        }
        
        // Update data di database
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, ukuran = ?, harga = ?, stok = ?, paving_rate = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssdiissi", $_POST['name'], $category, $_POST['ukuran'], $harga, $stok, $pavingRate, $_POST['description'], $image, $id);
        
        if ($stmt->execute()) {
            redirect_products();
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
        break;
    
    // ========================================
    // HAPUS PRODUK
    // ========================================
    case 'hapus':
        $id = $_GET['id'];
        
        // Ambil nama file gambar
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        // Hapus file gambar dari server
        if ($row) {
            $image_path = "../uploads/" . $row['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Hapus data dari database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            redirect_products();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        break;
    
    // ========================================
    // AKSI TIDAK DIKENAL
    // ========================================
    default:
        echo "Aksi tidak dikenal.";
        break;
}

// Tutup koneksi database
$conn->close();
?>
