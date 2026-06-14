<?php
include 'config.php';
session_start();

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
    
    // Simple mail (InfinityFree pe kaam karta hai)
    $to = $email;
    $subject = "Your OTP Code";
    $message = "Hello $fullname,\n\nYour OTP code is: $otp\n\nEnter this code to verify your account.\n\nThank you!";
    $headers = "From: no-reply@" . $_SERVER['HTTP_HOST'];
    
    if(mail($to, $subject, $message, $headers)){
        header("Location: verify_otp.php?email=" . urlencode($email));
    } else {
        // Agar mail fail ho to screen pe OTP dikhao
        echo "<script>alert('Demo Mode - Your OTP is: $otp'); window.location='verify_otp.php?email=$email&demo_otp=$otp';</script>";
    }
    exit();
    
} else {
    if(mysqli_errno($conn) == 1062){
        echo "<script>alert('Email already exists!'); window.location='register.php';</script>";
    } else {
        echo "<script>alert('Registration failed!'); window.location='register.php';</script>";
    }
}
?>