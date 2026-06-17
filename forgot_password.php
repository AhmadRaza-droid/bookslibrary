<?php
session_start();
include 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ========== STEP 1: SEND OTP ==========
if(isset($_POST['send_otp'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check) > 0){
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        mysqli_query($conn, "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'");
        
        $_SESSION['reset_email'] = $email;
        
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
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>Password Reset OTP</h2>
                    <p>Your OTP code is:</p>
                    <h1 style='color: #28a745; font-size: 40px;'>$otp</h1>
                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                    <hr>
                    <small>📚 Book's Library Team</small>
                </div>
            ";
            $mail->send();
            
            echo "<script>
                    alert('✅ OTP sent to your email!');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        } catch(Exception $e) {
            echo "<script>alert('❌ Failed to send OTP. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('❌ Email not found!');</script>";
    }
}

// ========== STEP 2: RESET PASSWORD ==========
if(isset($_POST['reset_password'])){
    if(!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true){
        echo "<script>
                alert('Session expired. Please try again.');
                window.location.href='forgot_password.php';
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

$step = isset($_GET['step']) ? $_GET['step'] : 'send';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
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
            margin-bottom: 10px;
        }
        .form-box p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
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
        .form-box .back-link {
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
        .dark-mode .form-box p {
            color: #aaa;
        }
        .dark-mode .form-box input {
            background: #1a1a3a;
            color: white;
            border-color: #333;
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
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="form-section">
    <div class="form-box">

        <?php if($step == 'send'): ?>
            <!-- STEP 1: Send OTP -->
            <h2>🔐 Forgot Password</h2>
            <p>Enter your email to receive OTP</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter Email" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>

        <?php elseif($step == 'reset'): ?>
            <!-- STEP 2: Reset Password -->
            <h2>🔑 Reset Password</h2>
            <p>Enter your new password</p>
            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
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