<?php
session_start();

if(isset($_POST['verify'])){

    $entered_otp = $_POST['otp'];

    if(isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']){

        $_SESSION['otp_verified'] = true;

        echo "<script>
                alert('OTP Verified Successfully');
                window.location.href='reset_password.php';
              </script>";

    } else {

        echo "<script>
                alert('Invalid OTP');
                window.location.href='verify_otp.php';
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