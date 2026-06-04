<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if(isset($_POST['send'])){

    $email = $_POST['email'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){

        $otp = rand(100000,999999);

        mysqli_query($conn, "INSERT INTO password_otp(email, otp) VALUES('$email','$otp')");

        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);

        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = 'universitylibrary172@gmail.com';
            $mail->Password = 'vmrntxjtzpvobfyr';

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('universitylibrary172@gmail.com', 'Library Management System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "Your OTP is: <b>$otp</b>";

            $mail->send();

            echo "<script>
                    alert('OTP sent to your email');
                    window.location.href='verify_otp.php';
                  </script>";

        } catch(Exception $e){
            echo "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {
        echo "<script>alert('Email Not Found');</script>";
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
    <input type="email" name="email" placeholder="Enter Email" required>
    <button type="submit" name="send">Send OTP</button>
</form>

</div>
</section>

</body>
</html>