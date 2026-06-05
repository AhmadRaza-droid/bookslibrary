<?php
session_start();
include 'config.php';

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);

    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM admin
              WHERE username='$username'
              AND password='$password'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $_SESSION['admin'] = $username;

        echo "<script>

                alert('Admin Login Successful');

                window.location.href='admin_dashboard.php';

              </script>";

    } else {

        echo "<script>

                alert('Invalid Username or Password');

              </script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Admin Login</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>Admin Login</h2>

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

</body>
</html>

