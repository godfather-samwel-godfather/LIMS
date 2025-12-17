<!DOCTYPE html>
<html>
<head>
  <title>Add New User</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
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
      display: flex;
      align-items: center;
      justify-content: center;
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
    .card-form {
      background: #9333B9;
      border-radius: 18px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.10);
      padding: 36px 32px 28px 32px;
      max-width: 520px;
      width: 100%;
      margin: 32px 0;
    }
    .card-form h2 {
      text-align: center;
      margin-bottom: 28px;
      font-size: 2rem;
      font-weight: 600;
      color: #fff;
    }
    .card-form label {
      display: block;
      margin-bottom: 6px;
      margin-top: 18px;
      color: #fff;
      font-size: 1rem;
      font-weight: 500;
    }
    .card-form input,
    .card-form select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #cfd8dc;
      border-radius: 6px;
      font-size: 1rem;
      background: #fff;
      margin-bottom: 8px;
      transition: border 0.2s;
    }
    .card-form input:focus,
    .card-form select:focus {
      border: 1.5px solid #9333B9;
      outline: none;
      background: #f8fafc;
    }
    .card-form button[type="submit"] {
      margin-top: 22px;
      background: #9333B9;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 12px 0;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: background 0.2s;
    }
    .card-form button[type="submit"]:hover {
      background: #17406a;
    }
    .extra-fields {
      display: none;
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
    <div class="card-form">
    <h2>New User Registration</h2>
    <form action="save_user.php" method="POST">
      <label for="first_name">First Name</label>
      <input type="text" id="first_name" name="first_name" required>

      <label for="last_name">Last Name</label>
      <input type="text" id="last_name" name="last_name" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="role">Role</label>
      <select id="role" name="role" required onchange="toggleExtraFields()">
        <option value="">-- Select Role --</option>
        <option value="Admin">Admin</option>
        <option value="Printer">Printer</option>
        <option value="Security Officer">Security Officer</option>
      </select>

    

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Save</button>
    </form>
    </div>
  </main>
</div>

<!-- Footer -->
<footer class="footer" style="text-align: center; padding: 15px; background-color: #343a40; color: white;">
  &copy; 2025 DarTU. All rights reserved.
</footer>

<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleExtraFields() {
      const role = document.getElementById('role').value.toLowerCase();
      document.getElementById('printerFields').style.display = (role === 'printer') ? 'block' : 'none';
      document.getElementById('securityFields').style.display = (role === 'security officer') ? 'block' : 'none';
    }
  </script>
</body>
</html>
