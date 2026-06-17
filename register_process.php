<?php
session_start();
include 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if(isset($_POST['register'])){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='register.php';</script>";
        exit();
    }

    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if(mysqli_num_rows($check_result) > 0){
        echo "<script>alert('Email already registered! Please login.'); window.location.href='login.php';</script>";
        exit();
    }

    // ✅ Generate OTP
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // ✅ Insert user with OTP (is_verified = 0)
    $query = "INSERT INTO users(fullname, email, password, otp, otp_expiry, is_verified) 
              VALUES('$fullname', '$email', '$password', '$otp', '$otp_expiry', 0)";

    $result = mysqli_query($conn, $query);

    if ($result) {
        // ✅ Set session for OTP verification
        $_SESSION['temp_email'] = $email;
        
        // ✅ Send OTP via email
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
            $mail->Subject = 'Verify Your Email - OTP Code';
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>Email Verification</h2>
                    <p>Hello <strong>$fullname</strong>,</p>
                    <p>Your OTP code is:</p>
                    <h1 style='color: #28a745; font-size: 40px;'>$otp</h1>
                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                    <hr>
                    <small>📚 Book's Library Team</small>
                </div>
            ";
            $mail->send();
            
            echo "<script>
                    alert('✅ Registration Successful! OTP sent to your email. Please verify.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
            
        } catch(Exception $e) {
            echo "<script>
                    alert('⚠️ Registration Successful but OTP email failed! Please contact admin.');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
        
    } else {
        echo "<script>alert('❌ Registration Failed'); window.location.href='register.php';</script>";
        exit();
    }
}
?>