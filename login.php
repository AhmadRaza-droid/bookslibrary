<?php
session_start();
include 'config.php';

// ========== PHPMailer USE STATEMENTS - TOP PAR ==========
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ========== LOGIN ==========
if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        if($user['is_verified'] == 0){
            echo "<script>
                    alert('⚠️ Please verify your email first! Check your inbox for OTP.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];

        echo "<script>
                alert('✅ Login Successful');
                window.location.href='index.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('❌ Invalid Email or Password');
                window.location.href='login.php';
              </script>";
        exit();
    }
}

// ========== SEND OTP FOR FORGOT PASSWORD ==========
if(isset($_POST['send_otp'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check) > 0){
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        mysqli_query($conn, "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'");
        $_SESSION['reset_email'] = $email;
        
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
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <h2>Password Reset OTP</h2>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>Valid for 10 minutes.</p>
            ";
            $mail->send();
            
            echo "<script>
                    alert('✅ OTP sent to your email!');
                    window.location.href='login.php?step=verify_otp';
                  </script>";
            exit();
        } catch(Exception $e) {
            echo "<script>alert('❌ Failed to send OTP. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('❌ Email not found!');</script>";
    }
}

// ========== VERIFY OTP ==========
if(isset($_POST['verify_otp'])){
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['reset_email'];
    
    $query = "SELECT * FROM users WHERE email='$email' AND otp='$entered_otp'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        $_SESSION['otp_verified'] = true;
        echo "<script>
                alert('✅ OTP Verified! Set your new password.');
                window.location.href='login.php?step=reset_password';
              </script>";
        exit();
    } else {
        echo "<script>alert('❌ Invalid OTP!');</script>";
    }
}

// ========== RESET PASSWORD ==========
if(isset($_POST['reset_password'])){
    if(!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true){
        echo "<script>
                alert('Session expired. Please try again.');
                window.location.href='login.php';
              </script>";
        exit();
    }
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];
    
    if($new_password != $confirm_password){
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $query = "UPDATE users SET password='$new_password', otp=NULL WHERE email='$email'";
        $result = mysqli_query($conn, $query);
        
        if($result){
            session_destroy();
            echo "<script>
                    alert('✅ Password reset successfully! Please login.');
                    window.location.href='login.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('❌ Failed to reset password. Try again.');</script>";
        }
    }
}

$step = isset($_GET['step']) ? $_GET['step'] : 'login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css?v=100">
    <style>
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }
        .form-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        .form-box h2 {
            text-align: center;
            color: #0b1f3a;
            margin-bottom: 25px;
        }
        .form-box input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
            box-sizing: border-box;
        }
        .form-box input:focus {
            outline: none;
            border-color: #0b1f3a;
        }
        .form-box button {
            width: 100%;
            padding: 12px;
            background: #0b1f3a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .form-box button:hover {
            background: #1a3a5c;
        }
        .form-box p {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }
        .form-box a {
            color: #0b1f3a;
            text-decoration: none;
            font-weight: bold;
        }
        .form-box a:hover {
            text-decoration: underline;
        }
        .dark-mode .form-box {
            background: #16213e;
            color: white;
        }
        .dark-mode .form-box h2 {
            color: white;
        }
        .dark-mode .form-box input {
            background: #1a1a3a;
            color: white;
            border-color: #333;
        }
        .dark-mode .form-box p {
            color: #aaa;
        }
        .dark-mode .form-box a {
            color: #ffc72c;
        }
    </style>
</head>

<body>

<nav>
    <div class="logo">📖 Library Management System</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a class="active" href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="profile.php">Profile</a></li>
        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
            <li><a href="admin_dashboard.php">Admin Panel</a></li>
        <?php } ?>
        <li><a href="about.php">About</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="form-section">
    <div class="form-box">

        <!-- ========== STEP 1: LOGIN ========== -->
        <?php if($step == 'login'): ?>
            <h2>🔐 Login</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            <p><a href="login.php?step=forgot">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Register here</a></p>

        <!-- ========== STEP 2: FORGOT PASSWORD (SEND OTP) ========== -->
        <?php elseif($step == 'forgot'): ?>
            <h2>🔑 Forgot Password</h2>
            <p style="text-align:center;color:#666;margin-bottom:20px;">Enter your email to receive OTP</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter Email" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
            <p><a href="login.php">← Back to Login</a></p>

        <!-- ========== STEP 3: VERIFY OTP ========== -->
        <?php elseif($step == 'verify_otp'): ?>
            <h2>🔑 Verify OTP</h2>
            <p style="text-align:center;color:#666;margin-bottom:20px;">Enter the OTP sent to your email</p>
            <form method="POST">
                <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
                <button type="submit" name="verify_otp">Verify OTP</button>
            </form>
            <p><a href="login.php?step=forgot">← Resend OTP</a></p>

        <!-- ========== STEP 4: RESET PASSWORD ========== -->
        <?php elseif($step == 'reset_password'): ?>
            <h2>🔑 Reset Password</h2>
            <p style="text-align:center;color:#666;margin-bottom:20px;">Enter your new password</p>
            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
            <p><a href="login.php">← Back to Login</a></p>
        <?php endif; ?>

    </div>
</section>

<script>
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}
function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>