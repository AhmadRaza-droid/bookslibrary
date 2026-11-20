<?php
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if (!isset($_SESSION['temp_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['temp_email'];
if (isset($_POST['resend'])) {
    $last_sent = (int) ($_SESSION['resend_otp_last'] ?? 0);
    if (time() - $last_sent < 60) {
        echo "<script>alert('Please wait 60 seconds before requesting another OTP.'); window.location.href='verify_otp.php';</script>";
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT fullname FROM users WHERE email = ? AND is_verified = 0 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if (!$user) {
        unset($_SESSION['temp_email']);
        header("Location: register.php");
        exit();
    }

    $fullname = $user['fullname'];
    $otp = (string) random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', time() + 600);
    $update = mysqli_prepare($conn, "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ? AND is_verified = 0");
    mysqli_stmt_bind_param($update, 'sss', $otp, $expiry, $email);
    $saved = mysqli_stmt_execute($update);
    mysqli_stmt_close($update);

    if (!$saved) {
        echo "<script>alert('Could not generate a new OTP. Please try again.'); window.location.href='verify_otp.php';</script>";
        exit();
    }

    $_SESSION['resend_otp_last'] = time();
    $mail = new PHPMailer(true);
    try {
        $smtp = library_smtp_settings();
        if ($smtp['username'] === '' || $smtp['password'] === '') {
            throw new Exception('SMTP credentials are not configured on the server.');
        }
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'];
        $mail->Port = $smtp['port'];
        $mail->setFrom($smtp['username'], 'Book Library');
        $mail->addAddress($email, $fullname);
        $mail->isHTML(true);
        $mail->Subject = 'New OTP Code - Book Library';
        $safe_name = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
        $mail->Body = "<p>Hello <strong>{$safe_name}</strong>,</p><p>Your new verification code is:</p><h1>{$otp}</h1><p>This code is valid for 10 minutes.</p>";
        $mail->send();
        echo "<script>alert('New OTP sent to your email.'); window.location.href='verify_otp.php';</script>";
        exit();
    } catch (Exception $e) {
        error_log('OTP resend failed: ' . $e->getMessage());
        echo "<script>alert('Failed to send OTP. Please try again later.'); window.location.href='verify_otp.php';</script>";
        exit();
    }
}
?>