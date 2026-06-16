<?php
session_start();
include 'config.php';

if(!isset($_SESSION['temp_email'])){
    header("Location: register.php");
    exit();
}

$email = $_SESSION['temp_email'];

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if(isset($_POST['resend'])){
    // Get user data
    $user_query = mysqli_query($conn, "SELECT fullname FROM users WHERE email='$email' AND is_verified=0");
    $user = mysqli_fetch_assoc($user_query);
    
    if($user){
        $fullname = $user['fullname'];
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update OTP in database
        mysqli_query($conn, "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'");
        
        // Send OTP via email
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'universitylibrary172@gmail.com';
            $mail->Password = 'zuepxvysbxrocdef';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('universitylibrary172@gmail.com', 'Book Library');
            $mail->addAddress($email, $fullname);
            $mail->isHTML(true);
            $mail->Subject = 'New OTP Code - Book Library';
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>Your New OTP</h2>
                    <p>Hello <strong>$fullname</strong>,</p>
                    <p>Your new OTP code is:</p>
                    <h1 style='color: #28a745; font-size: 40px;'>$otp</h1>
                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                    <hr>
                    <small>📚 Book's Library Team</small>
                </div>
            ";
            $mail->send();
            
            echo "<script>
                    alert('✅ New OTP sent to your email!');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
            
        } catch(Exception $e) {
            echo "<script>
                    alert('❌ Failed to send OTP. Please try again.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        }
    }
}
?>