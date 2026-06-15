<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'universitylibrary172@gmail.com';
    $mail->Password = 'zuepxvysbxrcdef';  // Naya app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->setFrom('universitylibrary172@gmail.com', 'Test');
    $mail->addAddress('universitylibrary172@gmail.com');  // Apne email pe test
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from InfinityFree!';
    
    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo '❌ Email failed: ' . $mail->ErrorInfo;
}
?>