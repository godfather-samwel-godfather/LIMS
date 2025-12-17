<?php
session_start();

// Error Handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// --- DB Constants ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lims');

// --- Database Connection ---
function dbConnect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// --- Function to Update User ---
function editUser($id, $first_name, $last_name, $email, $role, $password = null) {
    $conn = dbConnect();
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET First_Name=?, Last_Name=?, Email=?, Role=?, Password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $role, $hashedPassword, $id);
    } else {
        $sql = "UPDATE users SET First_Name=?, Last_Name=?, Email=?, Role=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "User updated successfully.";
        header("Location: ../front/Admin.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// --- Handle POST Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $id = intval($_POST['id']);
    $first_name = trim($_POST['First_Name']);
    $last_name = trim($_POST['Last_Name']);
    $email = trim($_POST['Email']);
    $role = trim($_POST['Role']);
    $password = isset($_POST['Password']) ? trim($_POST['Password']) : null;

    if (empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
        die("All fields except password are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    editUser($id, $first_name, $last_name, $email, $role, $password);
}

// --- Handle GET Request ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn = dbConnect();
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        die("User not found.");
    }

    $stmt->close();
    $conn->close();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die("No user ID provided.");
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($user)) : ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="../styles/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="edit-user-container">
    <h2>Edit User</h2>
    <form method="POST" action="edit.php" class="edit-user-form">
        <input type="hidden" name="id" value="<?php echo isset($user['id']) ? htmlspecialchars($user['id']) : ''; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input id="first_name" type="text" name="First_Name" value="<?php echo isset($user['First_Name']) ? htmlspecialchars($user['First_Name']) : ''; ?>" required>

            <label for="last_name">Last Name:</label>
            <input id="last_name" type="text" name="Last_Name" value="<?php echo isset($user['Last_Name']) ? htmlspecialchars($user['Last_Name']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input id="email" type="email" name="Email" value="<?php echo isset($user['Email']) ? htmlspecialchars($user['Email']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="Role" required>
                <option value="Admin" <?php if(isset($user['Role']) && $user['Role'] === 'Admin') echo 'selected'; ?>>Admin</option>
                <option value="Printer" <?php if(isset($user['Role']) && $user['Role'] === 'Printer') echo 'selected'; ?>>Printer</option>
                <option value="Security Officer" <?php if(isset($user['Role']) && $user['Role'] === 'Security Officer') echo 'selected'; ?>>Security Officer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current):</label>
            <input id="password" type="password" name="Password" placeholder="Enter new password">
        </div>

        <button type="submit" class="update-btn">Update User</button>
    </form>
    <a href="../front/Admin.php" class="back-link">&larr; Back to Admin Dashboard</a>
</div>

</body>
</html>
<?php endif; ?>
