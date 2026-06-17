<?php
session_start();
include 'config.php';
include 'session_timeout.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ========== RATE LIMITING ==========
$ip = $_SERVER['REMOTE_ADDR'];
$limit_file = sys_get_temp_dir() . '/register_limit_' . md5($ip);
$now = time();

if(file_exists($limit_file)){
    $data = json_decode(file_get_contents($limit_file), true);
    if($data['time'] + 3600 > $now && $data['count'] >= 10){
        echo "<script>alert('⛔ Too many registration attempts! Try again after 1 hour.'); window.location.href='register.php';</script>";
        exit();
    }
}

if(isset($_POST['register'])){
    // ✅ FIX 1: Sanitize inputs
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // ✅ FIX 2: Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('❌ Invalid email format!'); window.location.href='register.php';</script>";
        exit();
    }
    
    // ✅ FIX 3: Validate password strength
    if(strlen($password) < 8){
        echo "<script>alert('❌ Password must be at least 8 characters long!'); window.location.href='register.php';</script>";
        exit();
    }
    if(!preg_match("/[A-Z]/", $password)){
        echo "<script>alert('❌ Password must contain at least 1 uppercase letter!'); window.location.href='register.php';</script>";
        exit();
    }
    if(!preg_match("/[a-z]/", $password)){
        echo "<script>alert('❌ Password must contain at least 1 lowercase letter!'); window.location.href='register.php';</script>";
        exit();
    }
    if(!preg_match("/[0-9]/", $password)){
        echo "<script>alert('❌ Password must contain at least 1 number!'); window.location.href='register.php';</script>";
        exit();
    }

    if ($password != $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='register.php';</script>";
        exit();
    }

    // ✅ FIX 4: Check if email exists (using prepared statement for security)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if(mysqli_num_rows($check_result) > 0){
        echo "<script>alert('Email already registered! Please login.'); window.location.href='login.php';</script>";
        exit();
    }

    // ✅ FIX 5: HASH PASSWORD (not plain text)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Generate OTP
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // ✅ FIX 6: Insert using prepared statement
    $stmt = $conn->prepare("INSERT INTO users(fullname, email, password, otp, otp_expiry, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $otp, $otp_expiry);
    $result = $stmt->execute();

    if ($result) {
        // Update rate limit
        if(file_exists($limit_file)){
            $data = json_decode(file_get_contents($limit_file), true);
            if($data['time'] + 3600 > $now){
                $data['count']++;
            } else {
                $data['count'] = 1;
                $data['time'] = $now;
            }
        } else {
            $data = ['count' => 1, 'time' => $now];
        }
        file_put_contents($limit_file, json_encode($data));
        
        // Set session for OTP verification
        $_SESSION['temp_email'] = $email;
        
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
        echo "<script>alert('❌ Registration Failed: " . mysqli_error($conn) . "'); window.location.href='register.php';</script>";
        exit();
    }
}
?>