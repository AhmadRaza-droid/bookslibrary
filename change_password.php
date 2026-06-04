<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['change'])){

    $user_id = $_SESSION['user_id'];

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    $check = mysqli_query($conn,
    "SELECT * FROM users 
     WHERE id='$user_id' 
     AND password='$old'");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
        "UPDATE users 
         SET password='$new' 
         WHERE id='$user_id'");

        echo "<script>
                alert('Password Changed Successfully');
                window.location.href='profile.php';
              </script>";

    } else {

        echo "<script>
                alert('Old Password Incorrect');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<section class="form-section">

<div class="form-box">

<h2>Change Password</h2>

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

</div>

</section>

</body>
</html>