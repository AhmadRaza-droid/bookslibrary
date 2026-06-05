<?php
include 'config.php';

if(isset($_POST['reset'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

    // CHECK ADMIN EXISTS
    $check = mysqli_query($conn,
        "SELECT * FROM admin
         WHERE username='$username'");

    if(mysqli_num_rows($check) > 0){

        // UPDATE PASSWORD
        mysqli_query($conn,
            "UPDATE admin
             SET password='$new_password'
             WHERE username='$username'");

        echo "<script>
                alert('Admin Password Reset Successful');
                window.location.href='admin_login.php';
              </script>";

    } else {

        echo "<script>
                alert('Admin Username Incorrect');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Forgot Password</title>
    <link rel='stylesheet' href='style.css'>
</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>Admin Forgot Password</h2>

<form method="POST">

<input type="text"
       name="username"
       placeholder="Enter Admin Username"
       required>

<input type="password"
       name="new_password"
       placeholder="Enter New Password"
       required>

<button type="submit" name="reset">
Reset Password
</button>

</form>

</div>

</section>

</body>
</html>