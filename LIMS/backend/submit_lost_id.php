<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'lims');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Sanitize inputs
    $fullName = trim($_POST['fullName']);
    $studentId = trim($_POST['studentId']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
   
    

    // Uploads
    $uploadDir = '../uploads/lost_id/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $lossReport = '';
    $paymentReceipt = '';
    if (!empty($_FILES['attachments']['name'][0])) {
        $fileCount = count($_FILES['attachments']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = basename($_FILES['attachments']['name'][$i]);
            $targetFile = $uploadDir . uniqid() . '_' . $fileName;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if ($fileType !== 'pdf') continue;
            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $targetFile)) {
                if ($i == 0) $lossReport = $targetFile;
                if ($i == 1) $paymentReceipt = $targetFile;
            }
        }
    }

    // Generate unique Track Number
    $dateCode = date('Ymd'); // e.g., 20250825
    $query = $conn->query("SELECT COUNT(*) AS count FROM student WHERE Track_Number LIKE 'TRK-$dateCode%'");
    $data = $query->fetch_assoc();
    $count = $data['count'] + 1;
    $trackNumber = 'TRK-' . $dateCode . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO student (
        Full_Name, Student_ID_No, Email_Address, Phone_Number, Upload_Loss_Report, Upload_Payment_Receipt, Track_Number
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param('sssssss',
        $fullName, $studentId, $email, $phone,
        $lossReport, $paymentReceipt, $trackNumber
    );

    if ($stmt->execute()) {
        header('Location: ../front/lost_id_success.php?track=' . urlencode($trackNumber));
        exit();
    } else {
        echo '<script>alert("Error submitting report: ' . $stmt->error . '");window.history.back();</script>';
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: ../front/student_lost.html');
    exit();
}
