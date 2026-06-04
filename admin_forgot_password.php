 id="5vjlp7"
<?php

if(isset($_POST['reset'])){

    $username = $_POST['username'];
    $new = $_POST['new_password'];

    if($username == "admin"){

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
    <link rel="stylesheet" href="style.css">
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

