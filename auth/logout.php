<?php
/**
 * logout.php
 * Proses logout user
 */

session_start();
session_destroy();
header('Location: ../index.php');
exit();
?>
