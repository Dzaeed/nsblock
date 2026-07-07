<?php

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ?: 'ns_block_db';
$db_port = getenv('DB_PORT') ?: 3306;
$db_ssl  = getenv('DB_SSL') === 'true' || getenv('DB_SSL') === '1';

if ($db_ssl) {
    $conn = mysqli_init();
    if (!$conn) {
        die("mysqli_init failed");
    }
    // Set SSL connection options (TiDB serverless uses standard trusted Let's Encrypt certificates)
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port, NULL, MYSQLI_CLIENT_SSL);
} else {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
}

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

$base_url = getenv('BASE_URL') ?: 'http://localhost/nsblock/';
define('BASE_URL', $base_url);
?>
