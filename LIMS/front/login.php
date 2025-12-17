<?php
// Start session at the top
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarTU LIMS - Login</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Consolidated Styles -->
    <link rel="stylesheet" href="../styles/main-consolidated.css">
</head>

<body class="login-body">
    <!-- Floating background elements -->
    <div class="floating-element">
        <i class="fas fa-graduation-cap" style="font-size: 4rem; color: white;"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-book" style="font-size: 3rem; color: white;"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-university" style="font-size: 3.5rem; color: white;"></i>
    </div>

    <div class="login-container">
        <div class="login-header">
            <h1>Dar es Salaam Tumaini University</h1>
            <p>Lost ID Management System</p>
        </div>

        <form action="../backend/login_router.php" method="POST" class="login-form" id="loginForm">
            <?php if (isset($_SESSION['login_error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php 
            echo $_SESSION['login_error']; 
            unset($_SESSION['login_error']); 
          ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']); 
          ?>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="Email" class="form-input"
                        placeholder="Enter your email address" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="Password" class="form-input"
                        placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Login As</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-tag input-icon"></i>
                    <select id="role" name="Role" class="form-select" required>
                        <option value="">-- Select Your Role --</option>
                        <option value="admin">🛡️ Administrator</option>
                        <option value="printer">🖨️ Printer Operator</option>
                        <option value="security officer">👮 Security Officer</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginButton">
                <i class="fas fa-sign-in-alt"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="login-footer">
            <p>Need help? <a href="#">Contact IT Support</a></p>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../app/login.js"></script>
</body>

</html>