<?php
// Start secure session
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'cookie_samesite' => 'Strict',
    'use_only_cookies' => 1,
    'cookie_lifetime' => 0
]);

// Set content type header
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit();
}

// Check if user is logged in with proper role
if (!isset($_SESSION['user_id'], $_SESSION['role']) || !in_array(strtolower($_SESSION['role']), ['printer', 'admin'])) {
    sendResponse(false, 'Unauthorized: Access denied', 403);
}

// Verify CSRF token
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== ($_SESSION['csrf_token'] ?? '')) {
    sendResponse(false, 'Invalid CSRF token', 403);
}

// Check if track number is provided and valid
if (empty($_POST['track_number'])) {
    sendResponse(false, 'Track number is required', 400);
}

$trackNumber = trim($_POST['track_number']);
if (!preg_match('/^[A-Z0-9-]+$/', $trackNumber)) {
    sendResponse(false, 'Invalid track number format', 400);
}

// Include database configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Get printer ID from session
$printerId = $_SESSION['user_id'];

// Use the existing database connection from db.php
// No need to create a new connection as it's already included

try {
    // Start transaction
    $conn->begin_transaction();
    
    // First, verify the record exists and is in the correct status
    $stmt = $conn->prepare("SELECT verification_status FROM student WHERE Track_Number = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $trackNumber);
    if (!$stmt->execute()) {
        throw new Exception('Database query failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Record not found');
    }
    
    $row = $result->fetch_assoc();
    if ($row['verification_status'] !== 'verified') {
        throw new Exception('Record is not in verified status');
    }
    
    $stmt->close();
    
    // Update the record
    $updateStmt = $conn->prepare("
        UPDATE student 
        SET verification_status = 'printed', 
            printed_at = NOW(), 
            printed_by = ? 
        WHERE Track_Number = ?
    ");
    
    if (!$updateStmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $updateStmt->bind_param('is', $printerId, $trackNumber);
    if (!$updateStmt->execute()) {
        throw new Exception('Update failed: ' . $updateStmt->error);
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Log the action
    error_log("ID Card marked as printed - Track #$trackNumber by User #$printerId");
    
    // Send success response
    sendResponse(true, 'ID Card successfully marked as printed');
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    
    // Log the error
    error_log('Error in mark_printed.php: ' . $e->getMessage());
    
    // Send error response
    $message = Config::get('APP_DEBUG', false) 
        ? $e->getMessage() 
        : 'An error occurred while processing your request';
    
    sendResponse(false, $message, 500);
} finally {
    // Close statements if they exist
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($updateStmt) && $updateStmt) $updateStmt->close();
    
    // Don't close the connection as it's shared
}
        echo json_encode(['success' => false, 'message' => 'No verified record found or already printed']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>
