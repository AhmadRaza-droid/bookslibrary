<?php
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address.');</script>";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
        if ($user) {
            $otp = (string) random_int(100000, 999999);
            $expiry = date('Y-m-d H:i:s', time() + 600);
            $update = mysqli_prepare($conn, "UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, 'ssi', $otp, $expiry, $user['id']);
            $saved = mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
            if ($saved) {
                $_SESSION['reset_email'] = $email;
                unset($_SESSION['otp_verified'], $_SESSION['otp_verified_at']);
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'universitylibrary172@gmail.com';
                    $mail->Password = getenv('LIBRARY_SMTP_PASSWORD') ?: '';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->setFrom('universitylibrary172@gmail.com', 'Book Library');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset OTP';
                    $mail->Body = "<p>Your Book Library password reset code is:</p><h1>{$otp}</h1><p>This code expires in 10 minutes.</p>";
                    $mail->send();
                    echo "<script>alert('OTP sent to your email.'); window.location.href='verify_otp.php';</script>";
                    exit();
                } catch (Exception $e) {
                    error_log('Password reset email failed: ' . $e->getMessage());
                    echo "<script>alert('Unable to send OTP. Please try again later.');</script>";
                }
            }
        } else {
            echo "<script>alert('Email not found.');</script>";
        }
    }
}

if (isset($_POST['reset_password'])) {
    $verified = !empty($_SESSION['otp_verified']) && !empty($_SESSION['otp_verified_at'])
        && (time() - (int) $_SESSION['otp_verified_at']) <= 600
        && !empty($_SESSION['reset_email']);
    if (!$verified) {
        unset($_SESSION['otp_verified'], $_SESSION['otp_verified_at']);
        echo "<script>alert('Verification expired. Please request a new OTP.'); window.location.href='forgot_password.php';</script>";
        exit();
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (strlen($new_password) < 8 || $new_password !== $confirm_password) {
        echo "<script>alert('Password must be at least 8 characters and both entries must match.');</script>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ? AND otp IS NOT NULL AND otp_expiry > NOW()");
        mysqli_stmt_bind_param($stmt, 'ss', $hashed_password, $email);
        $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1;
        mysqli_stmt_close($stmt);
        if ($ok) {
            unset($_SESSION['reset_email'], $_SESSION['otp_verified'], $_SESSION['otp_verified_at']);
            session_regenerate_id(true);
            echo "<script>alert('Password reset successfully. Please login.'); window.location.href='login.php';</script>";
            exit();
        }
        unset($_SESSION['otp_verified'], $_SESSION['otp_verified_at']);
        echo "<script>alert('OTP expired or password reset failed. Please request a new OTP.'); window.location.href='forgot_password.php';</script>";
        exit();
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