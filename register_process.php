<?php
include 'config.php';

$email = $_POST['email'];
$fullname = $_POST['fullname'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if($password != $confirm_password){
    die("<script>alert('Passwords do not match'); window.location='register.php';</script>");
}

$otp = rand(100000,999999);

// TEMP SAVE USER (unverified)
mysqli_query($conn, "
INSERT INTO users(fullname,email,password,otp,is_verified)
VALUES('$fullname','$email','$password','$otp',0)
");

// SEND OTP EMAIL
$subject = "Your OTP Code";
$message = "Your OTP is: $otp";

mail($email, $subject, $message);

// redirect
header("Location: verify_otp.php?email=$email");
exit();
?>