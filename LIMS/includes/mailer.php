<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // if installed via Composer

function sendEmail($toEmail, $studentName) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';
        $mail->Password   = 'your_app_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'ID Management System');
        $mail->addAddress($toEmail, $studentName);

        $mail->isHTML(true);
        $mail->Subject = 'Your New Student ID is Ready';
        $mail->Body    = "Hello <b>$studentName</b>,<br><br>
                         Your new student ID card has been printed and is ready for collection.<br><br>
                         Regards,<br>ID Management Team";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
    }
}
