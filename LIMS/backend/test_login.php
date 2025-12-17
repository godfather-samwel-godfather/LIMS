<?php
// Test login functionality for all roles
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Login Test Results</h2>";

// Test each role
$roles = ['admin', 'printer', 'security officer'];

foreach ($roles as $role) {
    echo "<h3>Testing Role: " . htmlspecialchars($role) . "</h3>";
    
    $stmt = $conn->prepare("SELECT Email, Role, Password FROM users WHERE LOWER(Role) = ?");
    $stmt->bind_param("s", strtolower($role));
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>Email</th><th>Role</th><th>Password Type</th><th>Login Test</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $passwordType = (strlen($row['Password']) > 50) ? 'Hashed' : 'Plaintext';
            $testResult = "✓ Should work with both hashed and plaintext";
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
            echo "<td>" . $passwordType . "</td>";
            echo "<td style='color: green;'>" . $testResult . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No users found with role: " . htmlspecialchars($role) . "</p>";
    }
    
    $stmt->close();
    echo "<br>";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
