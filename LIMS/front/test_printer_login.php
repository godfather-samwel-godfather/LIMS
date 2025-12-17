<?php
// Test printer login specifically
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Printer Login Test</h2>";

// Check for printer users
$stmt = $conn->prepare("SELECT Id, First_Name, Last_Name, Email, Role, Password FROM users WHERE LOWER(Role) = 'printer'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h3>Found Printer Users:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Password Type</th><th>Test Login</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $passwordType = (strlen($row['Password']) > 50) ? 'Hashed' : 'Plaintext';
        $fullName = $row['First_Name'] . ' ' . $row['Last_Name'];
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Id']) . "</td>";
        echo "<td>" . htmlspecialchars($fullName) . "</td>";
        echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
        echo "<td>" . $passwordType . "</td>";
        echo "<td><a href='login.php' target='_blank'>Go to Login</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Login Instructions:</h3>";
    echo "<ol>";
    echo "<li>Go to <a href='login.php' target='_blank'>Login Page</a></li>";
    echo "<li>Use one of the printer emails above</li>";
    echo "<li>Select 'Printer' as the role</li>";
    echo "<li>Enter the password (if you know it)</li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red;'>❌ No printer users found in the database!</p>";
    echo "<p>You need to create a printer user first.</p>";
    
    echo "<h3>Create Printer User:</h3>";
    echo "<form method='POST' action='../backend/create_test_printer.php'>";
    echo "<p>Email: <input type='email' name='email' value='printer@dartu.ac.tz' required></p>";
    echo "<p>Password: <input type='password' name='password' value='printer123' required></p>";
    echo "<p>First Name: <input type='text' name='first_name' value='Test' required></p>";
    echo "<p>Last Name: <input type='text' name='last_name' value='Printer' required></p>";
    echo "<p><button type='submit'>Create Printer User</button></p>";
    echo "</form>";
}

$stmt->close();
$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    form { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    input { padding: 5px; margin: 5px; }
    button { padding: 8px 15px; background: #9333B9; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>
