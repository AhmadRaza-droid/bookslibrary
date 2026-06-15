<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if(isset($_POST['send'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $check = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        $otp = rand(100000,999999);

        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);

        try{

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = 'universitylibrary172@gmail.com';
            $mail->Password = 'zuepxvysbxrocdef';

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(
                'universitylibrary172@gmail.com',
                'Library Management System'
            );

            $mail->addAddress($email);

            $mail->isHTML(true);

            $mail->Subject = 'Password Reset OTP';

            $mail->Body = "
            <h2>Password Reset OTP</h2>
            <p>Your OTP is:</p>
            <h1>$otp</h1>
            ";

            $mail->send();

            header("Location: verify_otp.php");
            exit();

        } catch(Exception $e){

            echo "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {

        echo "<script>
                alert('Email Not Found');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section class="form-section">

<div class="form-box">

<h2>Forgot Password</h2>

<form method="POST">

<input type="email"
       name="email"
       placeholder="Enter Email"
       required>

<button type="submit" name="send">
Send OTP
</button>

</form>

</div>

</section>

</body>
</html>