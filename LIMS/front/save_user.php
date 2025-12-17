<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize and validate input
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$role = trim($_POST['role']);
$password = trim($_POST['password']);

// Simple validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($role) || empty($password)) {
    die("All fields are required.");
}


// Hashing the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$created_at = date("Y-m-d H:i:s");

//  SQL with optional fields
$sql = "INSERT INTO users 
        (First_Name, Last_Name, Email, Role, Password, Created_at) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param(
    "ssssss",
    $first_name,
    $last_name,
    $email,
    $role,
    $hashed_password,
    $created_at
);

// Execute and redirect
if ($stmt->execute()) {
    header("Location: Admin.php?success=1");
    exit();
} else {
    echo "Error saving user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
