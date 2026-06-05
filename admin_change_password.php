<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['change'])){

    $username = $_SESSION['admin'];
    $old = mysqli_real_escape_string($conn, $_POST['old_password']);
    $new = mysqli_real_escape_string($conn, $_POST['new_password']);

    $check = mysqli_query($conn,
        "SELECT * FROM admin 
         WHERE username='$username' 
         AND password='$old'");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
            "UPDATE admin 
             SET password='$new' 
             WHERE username='$username'");

        echo "<script>
                alert('Password Changed Successfully');
                window.location.href='admin_dashboard.php';
              </script>";

    } else {

        echo "<script>
                alert('Old Password Incorrect');
                window.location.href='admin_change_password.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<section class="form-section">
<div class="form-box">

<h2>Change Admin Password</h2>

<form method="POST">

<input type="password"
       name="old_password"
       placeholder="Old Password"
       required>

<input type="password"
       name="new_password"
       placeholder="New Password"
       required>

<button type="submit" name="change">
Change Password
</button>

</form>

<p>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</p>

</div>
</section>

</body>
</html>