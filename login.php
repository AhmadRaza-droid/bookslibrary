<?php
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendLibraryOtp($to, $otp) {
    $secret = getenv('LIBRARY_SMTP_PASSWORD');
    if (!$secret) throw new RuntimeException('SMTP is not configured.');
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('LIBRARY_SMTP_USERNAME') ?: 'universitylibrary172@gmail.com';
    $mail->Password = $secret;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($mail->Username, 'Book Library');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP';
    $mail->Body = '<h2>Password Reset OTP</h2><p>Your code is <strong>' . htmlspecialchars($otp) . '</strong>.</p><p>It expires in 10 minutes.</p>';
    $mail->send();
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $valid = false;
    if ($user) {
        $stored = (string)$user['password'];
        $valid = password_verify($password, $stored);
        if (!$valid && hash_equals($stored, $password)) {
            $valid = true;
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upgrade = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
            $upgrade->bind_param('si', $hash, $user['id']);
            $upgrade->execute();
        }
    }
    if ($user && $valid) {
        if ((int)$user['is_verified'] === 0) {
            echo "<script>alert('Please verify your email first.');window.location.href='verify_otp.php';</script>";
            exit();
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        header('Location: index.php');
        exit();
    }
    echo "<script>alert('Invalid email or password.');window.location.href='login.php';</script>";
    exit();
}

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $otp = (string)random_int(100000, 999999);
        $expiry = date('Y-m-d H:i:s', time() + 600);
        $save = $conn->prepare('UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?');
        $save->bind_param('ssi', $otp, $expiry, $user['id']);
        $save->execute();
        $_SESSION['reset_email'] = $email;
        unset($_SESSION['otp_verified']);
        try {
            sendLibraryOtp($email, $otp);
            echo "<script>alert('OTP sent to your email.');window.location.href='login.php?step=verify_otp';</script>";
            exit();
        } catch (Exception $e) {
            error_log('OTP email failed: ' . $e->getMessage());
            echo "<script>alert('Could not send OTP. Contact the administrator.');</script>";
        }
    } else {
        echo "<script>alert('If the account exists, an OTP will be sent.');</script>";
    }
}

if (isset($_POST['verify_otp'])) {
    $email = $_SESSION['reset_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND otp = ? AND otp_expiry > ? LIMIT 1');
    $stmt->bind_param('sss', $email, $otp, $now);
    $stmt->execute();
    if ($email !== '' && $stmt->get_result()->fetch_assoc()) {
        $_SESSION['otp_verified'] = true;
        header('Location: login.php?step=reset_password');
        exit();
    }
    echo "<script>alert('Invalid or expired OTP.');</script>";
}

if (isset($_POST['reset_password'])) {
    $email = $_SESSION['reset_email'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if (empty($_SESSION['otp_verified']) || $email === '') {
        echo "<script>alert('Verify your OTP first.');window.location.href='login.php?step=forgot';</script>";
        exit();
    }
    if (strlen($newPassword) < 8 || $newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords must match and be at least 8 characters.');</script>";
    } else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?');
        $stmt->bind_param('ss', $hash, $email);
        if ($stmt->execute()) {
            unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
            echo "<script>alert('Password reset successfully. Please login.');window.location.href='login.php';</script>";
            exit();
        }
        echo "<script>alert('Could not reset password.');</script>";
    }
}
$step = $_GET['step'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css?v=20261120">
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