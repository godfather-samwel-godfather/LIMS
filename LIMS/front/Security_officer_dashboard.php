<?php
session_start();

// Only allow users with 'security_officer' or 'admin' role
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['security officer', 'admin'])) {
  header("Location: ../front/login.php");
  exit;
}

// Connect to database and fetch printed lost ID reports
$conn = new mysqli('localhost', 'root', '', 'lims');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if verification columns exist, if not add them
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

// Fetch statistics for dashboard
$stats = [];

// Total printed IDs
$printedQuery = "SELECT COUNT(*) as total FROM student WHERE verification_status = 'printed'";
$printedResult = $conn->query($printedQuery);
$stats['printed'] = $printedResult->fetch_assoc()['total'];

// Today's printed IDs
$todayQuery = "SELECT COUNT(*) as today FROM student WHERE verification_status = 'printed' AND DATE(printed_at) = CURDATE()";
$todayResult = $conn->query($todayQuery);
$stats['today'] = $todayResult->fetch_assoc()['today'];

// This week's printed IDs
$weekQuery = "SELECT COUNT(*) as week FROM student WHERE verification_status = 'printed' AND WEEK(printed_at) = WEEK(NOW()) AND YEAR(printed_at) = YEAR(NOW())";
$weekResult = $conn->query($weekQuery);
$stats['week'] = $weekResult->fetch_assoc()['week'];

// This month's printed IDs
$monthQuery = "SELECT COUNT(*) as month FROM student WHERE verification_status = 'printed' AND MONTH(printed_at) = MONTH(NOW()) AND YEAR(printed_at) = YEAR(NOW())";
$monthResult = $conn->query($monthQuery);
$stats['month'] = $monthResult->fetch_assoc()['month'];

// Fetch only printed reports for security officer
$sql = "SELECT s.Track_Number, s.Full_Name, s.Student_ID_No, s.Email_Address, s.Phone_Number, s.verification_status, s.printed_at, 
               DATE_FORMAT(s.printed_at, '%Y-%m-%d %H:%i') as formatted_print_date,
               u.First_Name as printer_first_name, u.Last_Name as printer_last_name
        FROM student s 
        LEFT JOIN users u ON s.printed_by = u.Id
        WHERE s.verification_status = 'printed' 
        ORDER BY s.printed_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title>Security Officer Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
      display: flex;
      flex-direction: column;
      align-items: center;
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

    .stat-card.total { border-left-color: #28a745; }
    .stat-card.today { border-left-color: #007bff; }
    .stat-card.week { border-left-color: #ffc107; }
    .stat-card.month { border-left-color: #dc3545; }

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

    .stat-card.total .stat-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .stat-card.today .stat-icon { background: rgba(0, 123, 255, 0.1); color: #007bff; }
    .stat-card.week .stat-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .stat-card.month .stat-icon { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

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

    .footer {
      text-align: center;
      padding: 15px;
      background-color: #343a40;
      color: white;
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
    
    .export-btn {
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
    
    .export-btn:hover {
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

    .status-badge {
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

    .status-printed {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
      border: 1px solid rgba(40, 167, 69, 0.2);
    }
    
    .track-number {
      font-family: 'Courier New', monospace;
      font-weight: 600;
      color: #495057;
      background: #f8f9fa;
      padding: 0.3rem 0.6rem;
      border-radius: 6px;
      font-size: 0.85rem;
    }
    
    .student-info {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    
    .student-name {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .student-id {
      font-size: 0.8rem;
      color: #6c757d;
    }
    
    .contact-info {
      font-size: 0.85rem;
      color: #495057;
    }
    
    .printer-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .printer-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(45deg, #667eea, #764ba2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 0.8rem;
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
  </style>
</head>

<body>

<div class="wrapper">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-title">Security Officer Panel</div>
    <nav>
      <a href="admin.php">🏠 Home</a>
      
     
      
  
      <a href="../backend/logout.php">🚪 Logout</a>
    </nav>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <h1><i class="fas fa-shield-alt"></i> Security Officer Dashboard</h1>
      <p>Monitor and track all printed ID cards with comprehensive oversight</p>
    </div>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">
          <i class="fas fa-id-card"></i>
        </div>
        <div class="stat-number"><?php echo $stats['printed']; ?></div>
        <div class="stat-label">Total Printed IDs</div>
      </div>
      
      <div class="stat-card today">
        <div class="stat-icon">
          <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-number"><?php echo $stats['today']; ?></div>
        <div class="stat-label">Printed Today</div>
      </div>
      
      <div class="stat-card week">
        <div class="stat-icon">
          <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-number"><?php echo $stats['week']; ?></div>
        <div class="stat-label">This Week</div>
      </div>
      
      <div class="stat-card month">
        <div class="stat-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-number"><?php echo $stats['month']; ?></div>
        <div class="stat-label">This Month</div>
      </div>
    </div>
    
    <!-- Table Section -->
    <div class="table-section">
      <div class="table-header">
        <h2 class="table-title"><i class="fas fa-list"></i> Printed ID Records</h2>
        <div class="table-controls">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search records...">
          </div>
          <a href="#" class="export-btn" onclick="exportToCSV()">
            <i class="fas fa-download"></i> Export CSV
          </a>
        </div>
      </div>
      
      <table id="printedTable" class="modern-table">
        <thead>
          <tr>
            <th>Track Number</th>
            <th>Student Information</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Print Date</th>
            <th>Printed By</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td>
                <span class="track-number"><?php echo htmlspecialchars($row['Track_Number']); ?></span>
              </td>
              <td>
                <div class="student-info">
                  <div class="student-name"><?php echo htmlspecialchars($row['Full_Name']); ?></div>
                  <div class="student-id">ID: <?php echo htmlspecialchars($row['Student_ID_No']); ?></div>
                </div>
              </td>
              <td>
                <div class="contact-info">
                  <?php if($row['Email_Address']): ?>
                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['Email_Address']); ?></div>
                  <?php endif; ?>
                  <?php if($row['Phone_Number']): ?>
                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['Phone_Number']); ?></div>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <span class="status-badge status-printed">
                  <i class="fas fa-print"></i> Printed
                </span>
              </td>
              <td>
                <div class="date-info">
                  <div class="date-primary"><?php echo date('M d, Y', strtotime($row['printed_at'])); ?></div>
                  <div class="date-secondary"><?php echo date('H:i', strtotime($row['printed_at'])); ?></div>
                </div>
              </td>
              <td>
                <?php if ($row['printer_first_name']): ?>
                  <div class="printer-info">
                    <div class="printer-avatar">
                      <?php echo strtoupper(substr($row['printer_first_name'], 0, 1)); ?>
                    </div>
                    <div>
                      <div><?php echo htmlspecialchars($row['printer_first_name'] . ' ' . $row['printer_last_name']); ?></div>
                      <div style="font-size: 0.8rem; color: #6c757d;">Printer</div>
                    </div>
                  </div>
                <?php else: ?>
                  <span style="color: #6c757d; font-style: italic;">N/A</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; color:#888; padding: 3rem;">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                No printed IDs to display yet.
              </td>
            </tr>
          <?php endif; ?>
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
    $('#printedTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Sort by print date descending (5th column - Print Date)
        "columnDefs": [
            { "orderable": false, "targets": [3, 5] } // Disable sorting for status and printed by columns
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search records...",
            "lengthMenu": "Show _MENU_ records per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ records",
            "infoEmpty": "No records available",
            "infoFiltered": "(filtered from _MAX_ total records)",
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
        $('#printedTable').DataTable().search(this.value).draw();
    });
});

// Export to CSV function
function exportToCSV() {
    const table = document.getElementById('printedTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    // Add header row
    const headerRow = [];
    const headers = table.querySelectorAll('thead th');
    headers.forEach(header => {
        headerRow.push('"' + header.textContent.trim() + '"');
    });
    csv.push(headerRow.join(','));
    
    // Add data rows
    const dataRows = table.querySelectorAll('tbody tr');
    dataRows.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length > 0) {
            const rowData = [];
            cols.forEach(col => {
                // Clean up the cell content
                let cellText = col.textContent.trim();
                cellText = cellText.replace(/\s+/g, ' '); // Replace multiple spaces with single space
                rowData.push('"' + cellText + '"');
            });
            csv.push(rowData.join(','));
        }
    });
    
    // Create and download CSV file
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'printed_ids_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

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