<?php
session_start();
include 'config.php';

// Debug - check session
if(!isset($_SESSION['temp_email'])){
    // Try to get email from database if session missing
    if(isset($_SESSION['user_id'])){
        $uid = $_SESSION['user_id'];
        $q = mysqli_query($conn, "SELECT email FROM users WHERE id='$uid'");
        if($row = mysqli_fetch_assoc($q)){
            $_SESSION['temp_email'] = $row['email'];
        }
    }
    
    // If still no session, redirect to register
    if(!isset($_SESSION['temp_email'])){
        echo "<script>
                alert('Session expired. Please register again.');
                window.location.href='register.php';
              </script>";
        exit();
    }
}

$email = $_SESSION['temp_email'];

if(isset($_POST['verify'])){
    $entered_otp = trim($_POST['otp']);
    
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
        <h2>🔐 Verify Your Email</h2>
        <p>Enter the 6-digit OTP sent to</p>
        <div class="email-display">
            <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
            <br>
            <button type="submit" name="verify">✅ Verify Email</button>
        </form>
        <p style="margin-top: 20px;">
            <a href="register.php">← Back to Register</a>
        </p>
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