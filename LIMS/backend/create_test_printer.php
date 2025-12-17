<?php
// Create a test printer user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'lims');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $role = 'printer';

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT Id FROM users WHERE Email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<h2>User Already Exists</h2>";
        echo "<p>A user with email " . htmlspecialchars($email) . " already exists.</p>";
        echo "<a href='../front/test_printer_login.php'>← Back to Test</a>";
    } else {
        // Insert new printer user
        $stmt = $conn->prepare("INSERT INTO users (First_Name, Last_Name, Email, Password, Role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            echo "<h2>✅ Printer User Created Successfully!</h2>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
            echo "<p><strong>Role:</strong> " . htmlspecialchars($role) . "</p>";
            echo "<br>";
            echo "<p><a href='../front/login.php' target='_blank'>🔗 Test Login Now</a></p>";
            echo "<p><a href='../front/test_printer_login.php'>← Back to Test Page</a></p>";
        } else {
            echo "<h2>❌ Error Creating User</h2>";
            echo "<p>Error: " . $stmt->error . "</p>";
            echo "<a href='../front/test_printer_login.php'>← Back to Test</a>";
        }
        $stmt->close();
    }
    
    $checkStmt->close();
    $conn->close();
} else {
    header("Location: ../front/test_printer_login.php");
    exit();
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    a { color: #9333B9; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>
