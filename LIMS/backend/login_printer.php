<?php


$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$email = trim($_POST['Email'] ?? '');
$password = trim($_POST['Password'] ?? '');

if (!$email || !$password) {
    $_SESSION['login_error'] = "Email and password required.";
    header("Location: ../front/login.php");
    exit();
}

// Query user with role printer
$stmt = $conn->prepare("SELECT * FROM users WHERE Email = ? AND LOWER(Role) = 'printer'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if password is hashed or plaintext
    if (password_verify($password, $user['Password']) || $password === $user['Password']) {
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['user_name'] = $user['First_Name'] . ' ' . $user['Last_Name'];
        $_SESSION['role'] = 'printer';

        header("Location: ../front/printer_dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect password.";
    }
} else {
    $_SESSION['login_error'] = "User not found or not a printer.";
}

header("Location: ../front/login.php");
exit();
