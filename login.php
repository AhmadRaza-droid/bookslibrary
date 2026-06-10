<?php
session_start();
include 'config.php';

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users 
              WHERE email='$email' 
              AND password='$password'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];

        echo "<script>
                alert('Login Successful');
                window.location.href='index.php';
              </script>";

    } else {

        echo "<script>
                alert('Invalid Email or Password');
                window.location.href='login.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css?v=100">
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
    </ul>
</nav>

<section class="form-section">

    <div class="form-box">

        <h2>Login</h2>

        <form method="POST">

            <input type="email"
                   name="email"
                   placeholder="Enter Email"
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
            <a href="forgot_password.php">Forgot Password?</a>
        </p>

        <p>
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

    </div>

</section>

</body>
</html>