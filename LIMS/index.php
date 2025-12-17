<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarTU LIMS - Lost ID Management System</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Consolidated Styles -->
    <link rel="stylesheet" href="styles/main-consolidated.css">
</head>
<body class="index-body">
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <img src="assets/Dartulogo.jpg" alt="DarTU Logo">
                <span>DarTU LIMS</span>
            </div>
            <nav class="nav-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#process">Process</a>
                <a href="#contact">Contact</a>
                <a href="front/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <h1>Lost ID Management System</h1>
            <p>Efficient and secure management of lost student ID cards at Dar es Salaam Tumaini University. Report, track, and replace your ID cards with ease.</p>
            <div class="hero-buttons">
                <a href="front/student_lost.html" class="btn btn-primary">
                    <i class="fas fa-exclamation-triangle"></i>
                    Report Lost ID
                </a>
                <a href="front/track_status.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i>
                    Track Status
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>System Features</h2>
                <p>Our comprehensive lost ID management system provides all the tools you need for efficient ID card replacement</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Easy Reporting</h3>
                    <p>Quick and simple online form to report your lost ID card with all necessary details and documentation upload.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Real-time Tracking</h3>
                    <p>Track your replacement request status in real-time with our advanced tracking system using your unique tracking number.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Process</h3>
                    <p>Multi-level verification and security measures ensure your personal information and ID replacement process is completely secure.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="process" id="process">
        <div class="process-container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Follow these simple steps to get your replacement ID card</p>
            </div>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h4>Report Lost ID</h4>
                    <p>Fill out the online form with your details and upload required documents including loss report and payment receipt.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h4>Verification</h4>
                    <p>Our security team verifies your information and documents to ensure authenticity and prevent fraud.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h4>Processing</h4>
                    <p>Once verified, your replacement ID card is prepared and printed by our authorized printing team.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h4>Collection</h4>
                    <p>Receive notification when your new ID is ready for collection from the university ID office.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <p><i class="fas fa-phone"></i> +255 123 456 789</p>
                    <p><i class="fas fa-envelope"></i> support@dartu.ac.tz</p>
                    <p><i class="fas fa-map-marker-alt"></i> Dar es Salaam, Tanzania</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <p><a href="front/student_lost.html">Report Lost ID</a></p>
                    <p><a href="front/track_status.php">Track Status</a></p>
                    <p><a href="front/login.php">Staff Login</a></p>
                </div>
                <div class="footer-section">
                    <h4>Office Hours</h4>
                    <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
                    <p>Saturday: 9:00 AM - 1:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Dar es Salaam Tumaini University. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
