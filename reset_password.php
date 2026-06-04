```php
<?php
session_start();
include 'config.php';

if(isset($_POST['reset'])){

    $new_password = $_POST['new_password'];

    $email = $_SESSION['reset_email'];

    mysqli_query($conn,
    "UPDATE users
     SET password='$new_password'
     WHERE email='$email'");

    echo "<script>
            alert('Password Reset Successful');
            window.location.href='login.php';
          </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>Reset Password</h2>

<form method="POST">

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
```
