<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ns_block_db'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("KONEKSI GAGAL: " . $conn->connect_error);
}

function ensureColumnExists($conn, $table, $column, $definition) {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");

    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `$table` ADD `$column` $definition");
    }

    if ($result) {
        $result->close();
    }
}

ensureColumnExists($conn, 'products', 'harga', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
ensureColumnExists($conn, 'products', 'stok', 'INT NOT NULL DEFAULT 0');
ensureColumnExists($conn, 'products', 'paving_rate', 'INT NULL DEFAULT NULL');

define('BASE_URL', 'http://localhost/nsblock/');
?>
