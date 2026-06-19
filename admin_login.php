<?php
session_start();
include 'config.php';

// Check if already logged in
if(isset($_SESSION['admin'])){
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Check in database
    $query = "SELECT * FROM users WHERE fullname='$username' AND is_admin=1";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Check password (plain text or hashed)
        if($password == $user['password'] || password_verify($password, $user['password'])){
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['fullname'];
            $_SESSION['admin_email'] = $user['email'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'Username not found or not an admin!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0b1f3a 0%, #1a3a5c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        
        .login-container .icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        
        .login-container h1 {
            color: #0b1f3a;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .login-container .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
        }
        
        .login-container input {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: 0.3s ease;
        }
        
        .login-container input:focus {
            outline: none;
            border-color: #0b1f3a;
            box-shadow: 0 0 0 4px rgba(11, 31, 58, 0.1);
        }
        
        .login-container button {
            width: 100%;
            padding: 14px;
            background: #0b1f3a;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }
        
        .login-container button:hover {
            background: #1a3a5c;
            transform: translateY(-2px);
        }
        
        .login-container .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .login-container .links {
            margin-top: 20px;
        }
        
        .login-container .links a {
            color: #0b1f3a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .login-container .links a:hover {
            text-decoration: underline;
        }
        
        .login-container .links .separator {
            color: #ccc;
            margin: 0 10px;
        }
        
        /* Dark Mode */
        body.dark-mode .login-container {
            background: #16213e;
        }
        
        body.dark-mode .login-container h1 {
            color: white;
        }
        
        body.dark-mode .login-container .subtitle {
            color: #aaa;
        }
        
        body.dark-mode .login-container input {
            background: #1a1a3a;
            color: white;
            border-color: #333;
        }
        
        body.dark-mode .login-container input:focus {
            border-color: #ffc72c;
        }
        
        body.dark-mode .login-container .links a {
            color: #ffc72c;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            .login-container h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="icon">🔐</div>
    <h1>Admin Login</h1>
    <p class="subtitle">Enter your admin username and password</p>
    
    <?php if($error): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    
    <div class="links">
        <a href="forgot_password.php">Forgot Password?</a>
        <span class="separator">|</span>
        <a href="index.php">← Back to Website</a>
    </div>
</div>

<script>
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}
</script>

</body>
</html>