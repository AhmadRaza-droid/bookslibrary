<?php
session_start();
include 'config.php';

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ✅ Check in users table where is_admin = 1
    $query = "SELECT * FROM users 
              WHERE fullname='$username' 
              AND is_admin=1";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        // ✅ Check password (plain text or hashed)
        if($password == $user['password'] || password_verify($password, $user['password'])){

            $_SESSION['admin'] = $username;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];

            echo "<script>
                    alert('Admin Login Successful');
                    window.location.href='admin_dashboard.php';
                  </script>";

        } else {

            echo "<script>
                    alert('Invalid Password');
                  </script>";
        }

    } else {

        echo "<script>
                alert('Invalid Username or Not an Admin');
              </script>";
    }
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