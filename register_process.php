<?php
session_start();
include 'config.php';

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

    // ✅ SIMPLE PASSWORD - NO HASH
    $query = "INSERT INTO users(fullname, email, password, is_verified) 
              VALUES('$fullname', '$email', '$password', 1)";  // is_verified = 1 by default

    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>
                alert('✅ Registration Successful! Please login.');
                window.location.href='login.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('❌ Registration Failed'); window.location.href='register.php';</script>";
        exit();
    }
}
?>