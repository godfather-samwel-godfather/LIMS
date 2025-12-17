<?php
// Debug script to check users table
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Users Table Structure:</h2>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>All Users Data:</h2>";
$result = $conn->query("SELECT Id, First_Name, Last_Name, Email, Role, Password FROM users");
if ($result) {
    echo "<table border='1'><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Role</th><th>Password (first 20 chars)</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['First_Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Last_Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['Password'], 0, 20)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>Role Count:</h2>";
$result = $conn->query("SELECT Role, COUNT(*) as count FROM users GROUP BY Role");
if ($result) {
    echo "<table border='1'><tr><th>Role</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($row['Role']) . "</td><td>" . $row['count'] . "</td></tr>";
    }
    echo "</table>";
}

$conn->close();
?>
