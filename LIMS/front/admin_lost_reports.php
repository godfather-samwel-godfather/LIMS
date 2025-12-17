<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Admin view for all lost ID reports (from student table)
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_track_number'])) {
    $trackNumber = trim($_POST['verify_track_number']);
    $adminId = $_SESSION['user_id'];

    // First check if columns exist, if not add them
    $checkColumn = $conn->query("SHOW COLUMNS FROM student LIKE 'verification_status'");
    if ($checkColumn->num_rows == 0) {
        $conn->query("ALTER TABLE student ADD COLUMN verification_status ENUM('pending', 'verified', 'printed') DEFAULT 'pending'");
        $conn->query("ALTER TABLE student ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL");
        $conn->query("ALTER TABLE student ADD COLUMN verified_by INT NULL DEFAULT NULL");
        $conn->query("ALTER TABLE student ADD COLUMN printed_at TIMESTAMP NULL DEFAULT NULL");
        $conn->query("ALTER TABLE student ADD COLUMN printed_by INT NULL DEFAULT NULL");
    } else {
        // Check if 'printed' status exists in enum, if not add it
        $enumCheck = $conn->query("SHOW COLUMNS FROM student WHERE Field = 'verification_status'");
        if ($enumCheck->num_rows > 0) {
            $enumRow = $enumCheck->fetch_assoc();
            if (strpos($enumRow['Type'], 'printed') === false) {
                $conn->query("ALTER TABLE student MODIFY verification_status ENUM('pending', 'verified', 'printed') DEFAULT 'pending'");
            }
        }
        // Add printed columns if they don't exist
        $printedAtCheck = $conn->query("SHOW COLUMNS FROM student LIKE 'printed_at'");
        if ($printedAtCheck->num_rows == 0) {
            $conn->query("ALTER TABLE student ADD COLUMN printed_at TIMESTAMP NULL DEFAULT NULL");
            $conn->query("ALTER TABLE student ADD COLUMN printed_by INT NULL DEFAULT NULL");
        }
    }

    // Update verification status
    $stmt = $conn->prepare("UPDATE student SET verification_status = 'verified', verified_at = NOW(), verified_by = ? WHERE Track_Number = ?");
    $stmt->bind_param("is", $adminId, $trackNumber);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Lost ID report verified successfully!";
    } else {
        $_SESSION['error_message'] = "Error verifying report: " . $stmt->error;
    }
    $stmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to format phone number to +255XXXXXXXXX
function formatPhoneNumber($phone) {
    // Remove all non-digit characters
    $digits = preg_replace('/\D+/', '', $phone);

    // Remove leading 0 if it exists
    if (substr($digits, 0, 1) === '0') {
        $digits = substr($digits, 1);
    }

    // Prepend +255
    return '+255' . $digits;
}

// Check if verification columns exist first
$checkColumn = $conn->query("SHOW COLUMNS FROM student LIKE 'verification_status'");
if ($checkColumn->num_rows == 0) {
    // Columns don't exist yet, add them
    $conn->query("ALTER TABLE student ADD COLUMN verification_status ENUM('pending', 'verified') DEFAULT 'pending'");
    $conn->query("ALTER TABLE student ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL");
    $conn->query("ALTER TABLE student ADD COLUMN verified_by INT NULL DEFAULT NULL");
}

$sql = "SELECT s.*, u.First_Name as admin_first_name, u.Last_Name as admin_last_name 
        FROM student s 
        LEFT JOIN users u ON s.verified_by = u.Id 
        ORDER BY s.verification_status ASC, s.Track_Number DESC";
$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Lost ID Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    
    <!-- Custom Styling -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 20px 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        
        .sidebar-title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .sidebar nav a {
            display: block;
            padding: 12px 24px;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar nav a:hover {
            background-color: #495057;
            color: #fff;
            transform: translateX(5px);
        }
        
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }
        
        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .wrapper {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .main-content {
                padding: 20px;
            }
        }
        .alert {
            padding: 12px 16px;
            margin: 16px 0;
            border-radius: 4px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-verified {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .verify-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
        }
        .verify-btn:hover {
            background-color: #218838;
        }
        .verified-text {
            color: #28a745;
            font-weight: 600;
        }
        
        /* View Buttons */
        .btn-view {
            display: inline-block;
            padding: 6px 12px;
            background-color: #9333B9;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-view:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
            text-decoration: none;
            color: white;
        }
        
        .no-file {
            color: #6c757d;
            font-style: italic;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-title">Admin Panel</div>
        <nav>
            <a href="admin.php">🏠 Home</a>
            <a href="admin_lost_reports.php">📄 View Lost ID Requests</a>
            <a href="add_user.php">➕ Add User</a>
            <a href="printer_dashboard.php">🖨️ Printer Dashboard</a>
            <a href="security_officer_dashboard.php">🛡️ Security Dashboard</a>
            <a href="../backend/logout.php">🚪 Logout</a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
    <h1>All Lost Student ID Reports</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="table-container">
        <table id="lostReportsTable">
            <thead>
                <tr>
                    <th>Track Number</th>
                    <th>Full Name</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Loss Report</th>
                    <th>Payment Receipt</th>
                    <th>Status</th>
                    <th>Verified By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Track_Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Student_ID_No']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email_Address']); ?></td>
                    <td><?php echo htmlspecialchars(formatPhoneNumber($row['Phone_Number'])); ?></td>
                    <td>
                        <?php 
                            if ($row['Upload_Loss_Report']) {
                                echo '<a href="' . htmlspecialchars($row['Upload_Loss_Report']) . '" target="_blank" class="btn-view">📄 View</a>';
                            } else {
                                echo '<span class="no-file">No file</span>';
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                            if ($row['Upload_Payment_Receipt']) {
                                echo '<a href="' . htmlspecialchars($row['Upload_Payment_Receipt']) . '" target="_blank" class="btn-view">🧾 View</a>';
                            } else {
                                echo '<span class="no-file">No file</span>';
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                        $status = $row['verification_status'] ?? 'pending';
                        if ($status === 'printed'): ?>
                            <span class="status-badge" style="background-color: #d1ecf1; color: #0c5460;">🖨️ Printed</span>
                        <?php elseif ($status === 'verified'): ?>
                            <span class="status-badge status-verified">✓ Verified</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">⏳ Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                            if ($row['admin_first_name']) {
                                echo htmlspecialchars($row['admin_first_name'] . ' ' . $row['admin_last_name']);
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td>
                        <?php if (($row['verification_status'] ?? 'pending') === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="verify_track_number" value="<?php echo htmlspecialchars($row['Track_Number']); ?>">
                                <button type="submit" class="verify-btn" onclick="return confirm('Are you sure you want to verify this lost ID report?')">
                                    Verify
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="verified-text">✓ Verified</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </main>
</div>

<!-- Footer -->
<footer class="footer" style="text-align: center; padding: 15px; background-color: #343a40; color: white;">
    &copy; 2025 DarTU. All rights reserved.
</footer>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
    $('#lostReportsTable').DataTable();
  });
</script>
</body>
</html>
<?php $conn->close(); ?>
