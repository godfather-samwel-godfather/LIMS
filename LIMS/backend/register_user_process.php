<?php
session_start();

// Allow only admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// DB connection
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['First_Name'] ?? '');
    $lastName  = trim($_POST['Last_Name'] ?? '');
    $email     = trim($_POST['Email'] ?? '');
    $password  = trim($_POST['Password'] ?? '');
    $role      = strtolower(trim($_POST['Role'] ?? ''));

    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        die("First and last names are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (strlen($password) < 6) {
        die("Password must be at least 6 characters.");
    }

    if (!in_array($role, ['printer', 'security officer'])) {
        die("Invalid role. Allowed: Printer or Security Officer.");
    }

    // Check if user already exists
    $check = $conn->prepare("SELECT Id FROM users WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        die("A user with this email already exists.");
    }
    $check->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (First_Name, Last_Name, Email, Password, Role, Created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: admin.php?msg=UserAdded");
        exit();
    } else {
        echo "Error adding user: " . $stmt->error;
    }
}
?>
