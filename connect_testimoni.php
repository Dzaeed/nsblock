<?php
require_once('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($name !== '' && $message !== '') {
        if ($email === '') { $email = null; }
        $stmt = $conn->prepare("INSERT INTO testimonials (name, email, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
}
header("Location: index.php?msg=terkirim#contact");
exit();
?>