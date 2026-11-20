<?php
include 'config.php';

if (!isset($_SESSION['temp_email']) && !isset($_SESSION['reset_email'])) {
    echo "<script>alert('Session expired. Please try again.'); window.location.href='login.php';</script>";
    exit();
}

$type = isset($_SESSION['temp_email']) ? 'registration' : 'reset';
$email = $type === 'registration' ? $_SESSION['temp_email'] : $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $entered_otp = trim($_POST['otp'] ?? '');
    if (!preg_match('/^[0-9]{6}$/', $entered_otp)) {
        echo "<script>alert('Please enter a valid 6-digit OTP.'); window.location.href='verify_otp.php';</script>";
        exit();
    }

    if ($type === 'registration') {
        $sql = "SELECT id FROM users WHERE email = ? AND otp = ? AND otp_expiry > NOW() AND is_verified = 0 LIMIT 1";
    } else {
        $sql = "SELECT id FROM users WHERE email = ? AND otp = ? AND otp_expiry > NOW() LIMIT 1";
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { http_response_code(500); exit('Unable to verify OTP.'); }
    mysqli_stmt_bind_param($stmt, 'ss', $email, $entered_otp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $matched = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if ($matched) {
        if ($type === 'registration') {
            $update = mysqli_prepare($conn, "UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE id = ? AND otp = ? AND otp_expiry > NOW()");
            mysqli_stmt_bind_param($update, 'is', $matched['id'], $entered_otp);
            $ok = mysqli_stmt_execute($update) && mysqli_stmt_affected_rows($update) === 1;
            mysqli_stmt_close($update);
            if ($ok) {
                unset($_SESSION['temp_email']);
                echo "<script>alert('Email verified successfully! Please login.'); window.location.href='login.php';</script>";
                exit();
            }
        } else {
            $_SESSION['otp_verified'] = true;
            $_SESSION['otp_verified_at'] = time();
            echo "<script>alert('OTP verified! Please reset your password.'); window.location.href='forgot_password.php?step=reset';</script>";
            exit();
        }
    }

    echo "<script>alert('Invalid or expired OTP. Please request a new OTP.'); window.location.href='verify_otp.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify OTP - Book's Library</title>
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
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .form-box button:hover {
            background: #218838;
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
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
            <br>
            <button type="submit" name="verify">✅ Verify OTP</button>
        </form>
        <div class="back-link">
            <a href="<?php echo ($type == 'registration') ? 'register.php' : 'forgot_password.php'; ?>">
                ← Back
            </a>
        </div>
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