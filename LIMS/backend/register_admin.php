<?php
// File: register_admin.php
// Purpose: One-time secure admin registration

$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Admin credentials
$firstName = 'Admin';
$lastName = 'User';
$email = 'admin@gmail.com';
$password = '1234567'; // You can change this
$role = 'admin';

// Check if an admin already exists
$check = $conn->prepare("SELECT Id FROM users WHERE Role = 'admin'");
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    echo "❌ An admin already exists. Registration not allowed.";
    exit();
}
$check->close();

// Hash the password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin user
$stmt = $conn->prepare("
    INSERT INTO users (First_Name, Last_Name, Email, Password, Role, Created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "✅ Admin registered securely. You can now log in.";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
