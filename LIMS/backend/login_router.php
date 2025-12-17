<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front/login.php");
    exit();
}

$role = strtolower(trim($_POST['Role'] ?? ''));

switch ($role) {
    case 'admin':
        include 'login_admin.php';
        break;
    case 'printer':
        include 'login_printer.php';
        break;
    case 'security officer':
        include 'login_security.php';
        break;
    default:
        $_SESSION['login_error'] = "Invalid role selected.";
        header("Location: ../front/login.php");
        exit();
}
?>
