<?php
require_once '../includes/db.php';

$statusMsg = '';
$resultRow = null;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $track = trim($_POST['track_number']);
    $stmt = $conn->prepare("SELECT * FROM student WHERE Track_Number = ?");
    $stmt->bind_param('s', $track);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $resultRow = $result->fetch_assoc();
        $statusMsg = 'Request found!';
    } else {
        $statusMsg = 'No request found for this tracking number.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarTU LIMS - Track Your ID Request</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .university-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .header-title p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 3rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .track-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .track-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .track-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .track-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .track-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .track-header p {
            color: #6c757d;
            font-size: 1rem;
        }

        /* Form Styles */
        .track-form {
            margin-bottom: 2rem;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1rem;
        }

        .track-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .track-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        /* Status Display */
        .status-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            border-left: 4px solid #667eea;
            animation: fadeInUp 0.5s ease;
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .status-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .status-icon.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .status-icon.warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .status-icon.info {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        .status-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .status-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-badge.printed {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .status-badge.verified {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        /* Error Messages */
        .error-message {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Footer */
        .footer {
            background: rgba(44, 62, 80, 0.9);
            color: white;
            text-align: center;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem 1rem;
            }

            .nav-links {
                order: -1;
                flex-wrap: wrap;
                justify-content: center;
            }

            .main-content {
                padding: 2rem 1rem;
            }

            .track-container {
                padding: 2rem 1.5rem;
            }

            .status-details {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .track-container {
            animation: fadeInUp 0.6s ease;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <div class="university-logo">
                <i class="fas fa-university"></i>
            </div>
            <div class="header-title">
                <h1>DarTU LIMS</h1>
                <p>Lost ID Management System</p>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i>
                Home
            </a>
            <a href="student_lost.html" class="nav-link">
                <i class="fas fa-plus"></i>
                Report Lost ID
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="track-container">
            <div class="track-header">
                <div class="track-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2>Track Your ID Request</h2>
                <p>Enter your tracking number to check the current status of your ID replacement request</p>
            </div>

            <form class="track-form" method="POST">
                <div class="input-wrapper">
                    <i class="fas fa-hashtag input-icon"></i>
                    <input type="text" id="track_number" name="track_number" class="form-control" 
                           placeholder="Enter Tracking Number (e.g., TRK-20250101-0001)" 
                           value="<?php echo isset($_POST['track_number']) ? htmlspecialchars($_POST['track_number']) : ''; ?>" required>
                </div>
                <button type="submit" class="track-btn">
                    <i class="fas fa-search"></i>
                    Check Status
                </button>
            </form>

            <?php if ($errorMsg): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>

            <?php if ($resultRow): ?>
                <div class="status-card">
                    <div class="status-header">
                        <?php 
                        $status = $resultRow['verification_status'] ?? 'pending';
                        $iconClass = '';
                        $statusText = '';
                        $badgeClass = '';
                        
                        if ($status === 'printed') {
                            $iconClass = 'info';
                            $statusText = 'ID Ready for Collection';
                            $badgeClass = 'printed';
                        } elseif ($status === 'verified') {
                            $iconClass = 'success';
                            $statusText = 'Being Processed for Printing';
                            $badgeClass = 'verified';
                        } else {
                            $iconClass = 'warning';
                            $statusText = 'Under Admin Review';
                            $badgeClass = 'pending';
                        }
                        ?>
                        <div class="status-icon <?php echo $iconClass; ?>">
                            <?php if ($status === 'printed'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php elseif ($status === 'verified'): ?>
                                <i class="fas fa-cog fa-spin"></i>
                            <?php else: ?>
                                <i class="fas fa-clock"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="status-title">Request Found!</div>
                            <div class="status-badge <?php echo $badgeClass; ?>">
                                <?php if ($status === 'printed'): ?>
                                    <i class="fas fa-print"></i> <?php echo $statusText; ?>
                                <?php elseif ($status === 'verified'): ?>
                                    <i class="fas fa-check"></i> <?php echo $statusText; ?>
                                <?php else: ?>
                                    <i class="fas fa-hourglass-half"></i> <?php echo $statusText; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-details">
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($resultRow['Full_Name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Student ID</div>
                            <div class="detail-value"><?php echo htmlspecialchars($resultRow['Student_ID_No']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tracking Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($resultRow['Track_Number']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Request Date</div>
                            <div class="detail-value">
                                <?php 
                                $date = $resultRow['created_at'] ?? $resultRow['submission_date'] ?? 'N/A';
                                if ($date !== 'N/A') {
                                    echo date('M d, Y', strtotime($date));
                                } else {
                                    echo $date;
                                }
                                ?>
                            </div>
                        </div>
                        <?php if ($status === 'printed' && isset($resultRow['printed_date'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Printed Date</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($resultRow['printed_date'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <div class="detail-label">Contact</div>
                            <div class="detail-value"><?php echo htmlspecialchars($resultRow['Phone_Number'] ?? $resultRow['phone'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <small>
            <i class="fas fa-copyright"></i> 2025 Dar es Salaam Tumaini University. All rights reserved.
        </small>
    </footer>

    <script>
        // Form submission loading state
        document.querySelector('.track-form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.track-btn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;
            
            // Re-enable if no response after 10 seconds
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 10000);
        });

        // Input formatting
        document.querySelector('#track_number').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            if (value.length > 0 && !value.startsWith('TRK-')) {
                if (value.startsWith('TRK')) {
                    value = 'TRK-' + value.substring(3);
                } else {
                    value = 'TRK-' + value;
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
