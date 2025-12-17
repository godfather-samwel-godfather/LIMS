<?php
// filepath: c:\xampp\htdocs\LIMS\backend\login_process.php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['Email']);
    $password = trim($_POST['Password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $storedPassword = $user['Password'];
        $role = strtolower($user['Role']);
        $name = $user['Name']; // Assuming you have a column 'Name'

        // 🔐 If you use hashed passwords:
        // $isValid = password_verify($password, $storedPassword);
        $isValid = $password === $storedPassword; // ❗ Change to password_verify() if using hashing

        if ($isValid) {
            // Store shared session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect based on role
            switch ($role) {
                case 'admin':
                    $_SESSION['admin_logged_in'] = true;
                    header("Location: ../front/Admin.php");
                    break;

                case 'printer':
                    $_SESSION['printer_logged_in'] = true;
                    header("Location: ../front/Printer_dashboard.php");
                    break;

                case 'security officer':
                case 'security_officer':
                    $_SESSION['security_logged_in'] = true;
                    header("Location: ../front/Security_officer_dashboard.php");
                    break;

                default:
                    $_SESSION['login_error'] = "Unauthorized role.";
                    header("Location: ../front/login.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password.";
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
    }

    header("Location: ../front/login.php");
    exit();
} else {
    $_SESSION['login_error'] = "Invalid request method.";
    header("Location: ../front/login.php");
    exit();
}
?>
