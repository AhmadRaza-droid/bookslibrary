<?php
include 'config.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, fullname, email, password FROM users WHERE fullname = ? AND is_admin = 1 LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        exit('Unable to login right now.');
    }
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if ($user && (password_verify($password, $user['password']) || hash_equals((string)$user['password'], $password))) {
        // Upgrade legacy plaintext passwords to a secure hash after a successful login.
        if (password_get_info($user['password'])['algo'] === null) {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            if ($update) {
                mysqli_stmt_bind_param($update, 'si', $new_hash, $user['id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }
        }
        session_regenerate_id(true);
        $_SESSION['admin'] = $user['fullname'];
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        echo "<script>alert('Admin login successful.'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    echo "<script>alert('Invalid username or password, or this account is not an admin.');</script>";
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Admin Login</title>

<link rel="stylesheet" href="style.css">
<style>
    /* Additional styles to fix full screen */
    .form-section {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #0b1f3a 0%, #1a3a5c 100%);
        padding: 20px;
    }
    .form-box {
        background: white;
        padding: 40px 35px;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        width: 100%;
        max-width: 420px;
    }
    .form-box h2 {
        color: #0b1f3a;
        text-align: center;
        margin-bottom: 25px;
        font-size: 28px;
    }
    .form-box input {
        width: 100%;
        padding: 14px 18px;
        margin-bottom: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        box-sizing: border-box;
    }
    .form-box input:focus {
        outline: none;
        border-color: #0b1f3a;
    }
    .form-box button {
        width: 100%;
        padding: 14px;
        background: #0b1f3a;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: bold;
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
    .form-box p a {
        color: #0b1f3a;
        text-decoration: none;
        font-weight: 500;
    }
    .form-box p a:hover {
        text-decoration: underline;
    }
    body.dark-mode .form-box {
        background: #16213e;
    }
    body.dark-mode .form-box h2 {
        color: white;
    }
    body.dark-mode .form-box input {
        background: #1a1a3a;
        color: white;
        border-color: #333;
    }
    body.dark-mode .form-box p {
        color: #aaa;
    }
    body.dark-mode .form-box p a {
        color: #ffc72c;
    }
</style>

</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>🔐 Admin Login</h2>

<form method="POST">

<input type="text"
       name="username"
       placeholder="Enter Username"
       required>

<input type="password"
       name="password"
       placeholder="Enter Password"
       required>

<button type="submit" name="login">

Login

</button>

</form>

<p>

<a href="admin_forgot_password.php">

Forgot Password?

</a>

</p>

<p>

<a href="admin_change_password.php">

Change Admin Password

</a>

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