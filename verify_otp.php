```php
<?php
session_start();
include 'config.php';

if(isset($_POST['verify'])){

    $otp = $_POST['otp'];

    $email = $_SESSION['reset_email'];

    $check = mysqli_query($conn,
    "SELECT * FROM password_otp
     WHERE email='$email'
     AND otp='$otp'");

    if(mysqli_num_rows($check) > 0){

        echo "<script>
                alert('OTP Verified');
                window.location.href='reset_password.php';
              </script>";

    } else {

        echo "<script>
                alert('Invalid OTP');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>Verify OTP</h2>

<form method="POST">

<input type="text"
       name="otp"
       placeholder="Enter OTP"
       required>

<button type="submit" name="verify">
Verify OTP
</button>

</form>

</div>

</section>

</body>
</html>
```
