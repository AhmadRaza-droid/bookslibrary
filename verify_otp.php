<?php
session_start();

if(!isset($_SESSION['otp'])){
    echo "<script>
            alert('OTP session expired. Please try again.');
            window.location.href='login.php';
          </script>";
    exit();
}

if(isset($_POST['verify'])){

    $entered_otp = trim($_POST['otp']);

    if($entered_otp == $_SESSION['otp']){

        $_SESSION['otp_verified'] = true;

        unset($_SESSION['otp']); // cleanup

        echo "<script>
                alert('OTP Verified Successfully');
                window.location.href='reset_password.php';
              </script>";
        exit();

    } else {

        echo "<script>
                alert('Invalid OTP');
                window.location.href='verify_otp.php';
              </script>";
        exit();
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

<p>Enter the OTP sent to your email</p>

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