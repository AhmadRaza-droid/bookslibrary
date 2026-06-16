<?php
session_start();
include 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Check if user came from registration OR forgot password
if(!isset($_SESSION['temp_email']) && !isset($_SESSION['reset_email'])){
    echo "<script>
            alert('Session expired. Please try again.');
            window.location.href='login.php';
          </script>";
    exit();
}

// Get email from session
if(isset($_SESSION['temp_email'])){
    $email = $_SESSION['temp_email'];
    $type = 'registration';
} else if(isset($_SESSION['reset_email'])){
    $email = $_SESSION['reset_email'];
    $type = 'reset';
}

// ========== RESEND OTP ==========
if(isset($_POST['resend_otp'])){
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Get user data
    $user_query = mysqli_query($conn, "SELECT fullname FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($user_query);
    $fullname = $user['fullname'] ?? 'User';
    
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
        
        if($type == 'registration'){
            $mail->Subject = 'Verify Your Email - New OTP';
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>New OTP for Registration</h2>
                    <p>Hello <strong>$fullname</strong>,</p>
                    <p>Your new OTP code is:</p>
                    <h1 style='color: #28a745; font-size: 40px;'>$otp</h1>
                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                    <hr>
                    <small>📚 Book's Library Team</small>
                </div>
            ";
        } else {
            $mail->Subject = 'New OTP for Password Reset';
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>New OTP for Password Reset</h2>
                    <p>Hello <strong>$fullname</strong>,</p>
                    <p>Your new OTP code is:</p>
                    <h1 style='color: #28a745; font-size: 40px;'>$otp</h1>
                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                    <hr>
                    <small>📚 Book's Library Team</small>
                </div>
            ";
        }
        
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

// ========== VERIFY OTP ==========
if(isset($_POST['verify'])){
    $entered_otp = trim($_POST['otp']);
    
    if($type == 'registration'){
        // Registration OTP verification
        $query = "SELECT * FROM users WHERE email='$email' AND otp='$entered_otp' AND is_verified=0";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0){
            mysqli_query($conn, "UPDATE users SET is_verified=1, otp=NULL, otp_expiry=NULL WHERE email='$email'");
            unset($_SESSION['temp_email']);
            echo "<script>
                    alert('✅ Email verified successfully! Please login.');
                    window.location.href='login.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('❌ Invalid OTP! Please try again.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        }
    } else if($type == 'reset'){
        // Forgot Password OTP verification
        $query = "SELECT * FROM users WHERE email='$email' AND otp='$entered_otp'";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0){
            $_SESSION['otp_verified'] = true;
            unset($_SESSION['reset_email']);
            echo "<script>
                    alert('✅ OTP Verified! Please reset your password.');
                    window.location.href='reset_password.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('❌ Invalid OTP! Please try again.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify OTP</title>
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
            text-align: center;
        }
        .form-box h2 {
            color: #0b1f3a;
            margin-bottom: 10px;
        }
        .form-box p {
            color: #666;
            margin-bottom: 20px;
        }
        .form-box input {
            width: 80%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 8px;
        }
        .form-box input:focus {
            outline: none;
            border-color: #0b1f3a;
        }
        .form-box button {
            width: 80%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin: 5px 0;
        }
        .form-box .verify-btn {
            background: #28a745;
            color: white;
        }
        .form-box .verify-btn:hover {
            background: #218838;
        }
        .form-box .resend-btn {
            background: #0b1f3a;
            color: white;
        }
        .form-box .resend-btn:hover {
            background: #1a3a5c;
        }
        .form-box a {
            color: #0b1f3a;
            text-decoration: none;
        }
        .form-box a:hover {
            text-decoration: underline;
        }
        .email-display {
            background: #f4f6f9;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .back-link {
            margin-top: 20px;
            display: block;
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
        .dark-mode .email-display {
            background: #1a1a3a;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">📖 Book's Library</div>
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

        <h2>🔐 Verify OTP</h2>
        
        <p>Enter the 6-digit OTP sent to</p>
        <div class="email-display">
            <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>

        <!-- ===== VERIFY OTP FORM ===== -->
        <form method="POST">
            <input type="text" 
                   name="otp" 
                   placeholder="Enter OTP" 
                   maxlength="6" 
                   required>
            <br>
            <button type="submit" name="verify" class="verify-btn">
                ✅ Verify OTP
            </button>
        </form>

        <!-- ===== RESEND OTP FORM - ADDED HERE ===== -->
        <form method="POST">
            <button type="submit" name="resend_otp" class="resend-btn">
                📩 Resend OTP
            </button>
        </form>
        <!-- ========================================= -->

        <a href="<?php echo ($type == 'registration') ? 'register.php' : 'forgot_password.php'; ?>" class="back-link">
            ← Back
        </a>

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
    }
    else{
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>