<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connect to database and fetch statistics
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch statistics for dashboard
$stats = [];

// Total users
$usersQuery = "SELECT COUNT(*) as total FROM users";
$usersResult = $conn->query($usersQuery);
$stats['users'] = $usersResult->fetch_assoc()['total'];

// Total lost ID requests
$requestsQuery = "SELECT COUNT(*) as total FROM student";
$requestsResult = $conn->query($requestsQuery);
$stats['requests'] = $requestsResult->fetch_assoc()['total'];

// Pending requests
$pendingQuery = "SELECT COUNT(*) as pending FROM student WHERE verification_status = 'pending' OR verification_status IS NULL";
$pendingResult = $conn->query($pendingQuery);
$stats['pending'] = $pendingResult->fetch_assoc()['pending'];

// Printed IDs
$printedQuery = "SELECT COUNT(*) as printed FROM student WHERE verification_status = 'printed'";
$printedResult = $conn->query($printedQuery);
$stats['printed'] = $printedResult->fetch_assoc()['printed'];

// Users by role
$roleQuery = "SELECT Role, COUNT(*) as count FROM users GROUP BY Role";
$roleResult = $conn->query($roleQuery);
$roleStats = [];
while($row = $roleResult->fetch_assoc()) {
    $roleStats[$row['Role']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

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
    
    .dashboard-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      border-radius: 15px;
      margin-bottom: 2rem;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .dashboard-header h1 {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .dashboard-header p {
      margin: 0.5rem 0 0 0;
      opacity: 0.9;
      font-size: 1.1rem;
    }

    .stats-grid {
      display: flex;
      justify-content: space-between;
      gap: 1.5rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .stat-card {
      flex: 1;
      min-width: 200px;
      background: #fff;
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-card.users { border-left-color: #28a745; }
    .stat-card.requests { border-left-color: #007bff; }
    .stat-card.pending { border-left-color: #ffc107; }
    .stat-card.printed { border-left-color: #dc3545; }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    .stat-card.users .stat-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .stat-card.requests .stat-icon { background: rgba(0, 123, 255, 0.1); color: #007bff; }
    .stat-card.pending .stat-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .stat-card.printed .stat-icon { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      line-height: 1;
    }

    .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
      font-weight: 500;
      margin: 0.5rem 0 0 0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .table-section {
      background: #fff;
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      margin-top: 2rem;
    }
    
    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .table-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2c3e50;
      margin: 0;
    }
    
    .table-controls {
      display: flex;
      gap: 1rem;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .search-box {
      position: relative;
    }
    
    .search-box input {
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      border: 2px solid #e9ecef;
      border-radius: 25px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      width: 250px;
    }
    
    .search-box input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .search-box i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    
    .add-user-btn {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: white;
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 25px;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .add-user-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
      color: white;
      text-decoration: none;
    }

    .modern-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .modern-table thead th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: none;
    }

    .modern-table tbody td {
      padding: 1rem;
      border-bottom: 1px solid #f1f3f4;
      vertical-align: middle;
      font-size: 0.9rem;
    }

    .modern-table tbody tr {
      transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
      background-color: #f8f9ff;
      transform: scale(1.01);
    }

    .modern-table tbody tr:last-child td {
      border-bottom: none;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(45deg, #667eea, #764ba2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 1rem;
    }
    
    .user-details {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    
    .user-name {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .user-email {
      font-size: 0.8rem;
      color: #6c757d;
    }
    
    .role-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .role-admin {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.2);
    }
    
    .role-printer {
      background: rgba(0, 123, 255, 0.1);
      color: #007bff;
      border: 1px solid rgba(0, 123, 255, 0.2);
    }
    
    .role-security {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
      border: 1px solid rgba(40, 167, 69, 0.2);
    }
    
    .action-buttons {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
    }
    
    .btn-edit {
      background: linear-gradient(45deg, #17a2b8, #138496);
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }
    
    .btn-edit:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
      color: white;
      text-decoration: none;
    }
    
    .btn-delete {
      background: linear-gradient(45deg, #dc3545, #c82333);
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
    }
    
    .btn-delete:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
      color: white;
      text-decoration: none;
    }
    
    .date-info {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    
    .date-primary {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .date-secondary {
      font-size: 0.8rem;
      color: #6c757d;
    }

    .footer {
      text-align: center;
      padding: 15px;
      background-color: #343a40;
      color: white;
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
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
      <p>Comprehensive system management and user oversight</p>
    </div>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
      <div class="stat-card users">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?php echo $stats['users']; ?></div>
        <div class="stat-label">Total Users</div>
      </div>
      
      <div class="stat-card requests">
        <div class="stat-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-number"><?php echo $stats['requests']; ?></div>
        <div class="stat-label">ID Requests</div>
      </div>
      
      <div class="stat-card pending">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">Pending</div>
      </div>
      
      <div class="stat-card printed">
        <div class="stat-icon">
          <i class="fas fa-print"></i>
        </div>
        <div class="stat-number"><?php echo $stats['printed']; ?></div>
        <div class="stat-label">Printed IDs</div>
      </div>
    </div>
    
    <!-- Table Section -->
    <div class="table-section">
      <div class="table-header">
        <h2 class="table-title"><i class="fas fa-users-cog"></i> User Management</h2>
        <div class="table-controls">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search users...">
          </div>
          <a href="add_user.php" class="add-user-btn">
            <i class="fas fa-user-plus"></i> Add User
          </a>
        </div>
      </div>
      
      <table id="userTable" class="modern-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Created Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}
$sql = "SELECT id, First_Name, Last_Name, Email, Role, Created_at FROM users";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $initials = strtoupper(substr($row['First_Name'], 0, 1) . substr($row['Last_Name'], 0, 1));
    $roleClass = 'role-' . strtolower(str_replace(' ', '', $row['Role']));
    $roleIcon = '';
    switch(strtolower($row['Role'])) {
      case 'admin': $roleIcon = 'fas fa-user-shield'; break;
      case 'printer': $roleIcon = 'fas fa-print'; break;
      case 'security officer': $roleIcon = 'fas fa-shield-alt'; break;
      default: $roleIcon = 'fas fa-user';
    }
    
    echo '<tr>';
    echo '<td>
            <div class="user-info">
              <div class="user-avatar">' . $initials . '</div>
              <div class="user-details">
                <div class="user-name">' . htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']) . '</div>
                <div class="user-email">' . htmlspecialchars($row['Email']) . '</div>
              </div>
            </div>
          </td>';
    echo '<td>
            <span class="role-badge ' . $roleClass . '">
              <i class="' . $roleIcon . '"></i> ' . htmlspecialchars($row['Role']) . '
            </span>
          </td>';
    echo '<td>
            <div class="date-info">
              <div class="date-primary">' . date('M d, Y', strtotime($row['Created_at'])) . '</div>
              <div class="date-secondary">' . date('H:i', strtotime($row['Created_at'])) . '</div>
            </div>
          </td>';
    echo '<td>
            <div class="action-buttons">
              <a href="../backend/edit.php?id=' . $row['id'] . '" class="btn-edit">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="../backend/delete_logic.php?id=' . $row['id'] . '" class="btn-delete" onclick="return confirm(\'Are you sure you want to delete this user?\')">
                <i class="fas fa-trash"></i> Delete
              </a>
            </div>
          </td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="4" style="text-align:center; color:#888; padding: 3rem;">
          <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
          No users found.
        </td></tr>';
}
?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Footer -->
<footer class="footer">
  &copy; 2025 DarTU. All rights reserved.
</footer>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with custom options
    $('#userTable').DataTable({
        "pageLength": 25,
        "order": [[ 2, "desc" ]], // Sort by created date descending
        "columnDefs": [
            { "orderable": false, "targets": [3] } // Disable sorting for actions column
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search users...",
            "lengthMenu": "Show _MENU_ users per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ users",
            "infoEmpty": "No users available",
            "infoFiltered": "(filtered from _MAX_ total users)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": '<"top"fl>rt<"bottom"ip><"clear">'
    });
    
    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        $('#userTable').DataTable().search(this.value).draw();
    });
});

// Add some interactive animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate stat cards on load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }, index * 150);
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
