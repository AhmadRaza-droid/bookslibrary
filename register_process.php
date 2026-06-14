<?php
include 'config.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email = mysqli_real_escape_string($conn, $_POST['email']);
$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if($password != $confirm_password){
    die("<script>alert('Passwords do not match'); window.location='register.php';</script>");
}

// Check if email already exists
$check_query = "SELECT email FROM users WHERE email = '$email'";
$check_result = mysqli_query($conn, $check_query);

if(mysqli_num_rows($check_result) > 0){
    die("<script>alert('Email already registered! Please login.'); window.location='login.php';</script>");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$otp = rand(100000, 999999);

$insert_query = "INSERT INTO users(fullname, email, password, otp, is_verified) 
                 VALUES('$fullname', '$email', '$hashed_password', '$otp', 0)";

if(mysqli_query($conn, $insert_query)){
    
    // Send OTP via SMTP
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to your SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Your email
        $mail->Password = 'your-app-password'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('your-email@gmail.com', 'Website Name');
        $mail->addAddress($email, $fullname);
        
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "Hello $fullname,<br><br>Your OTP is: <b>$otp</b><br><br>Thank you for registering!";
        
        $mail->send();
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit();
        
    } catch(Exception $e) {
        echo "OTP sent failed! But your OTP is: $otp <br>";
        echo "Error: " . $mail->ErrorInfo;
    }
    
} else {
    echo "<script>alert('Registration failed! Try again.'); window.location='register.php';</script>";
}
?>