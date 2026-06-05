<?php

include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['message'])){

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO messages(name,email,message)
              VALUES('$name','$email','$message')";

    mysqli_query($conn, $query);

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

        $mail->addAddress('universitylibrary172@gmail.com');

        $mail->isHTML(true);

        $mail->Subject = 'New Contact Message';

        $mail->Body = "
            <h2>New User Message</h2>
            <p><b>Name:</b> $name</p>
            <p><b>Email:</b> $email</p>
            <p><b>Message:</b><br>$message</p>
        ";

        $mail->send();

        echo "<script>
                alert('Message Sent Successfully');
                window.location.href='contact.php';
              </script>";

    } catch(Exception $e){

        echo "Mailer Error: " . $mail->ErrorInfo;
    }

} else {

    echo "<script>
            alert('Invalid Request');
            window.location.href='contact.php';
          </script>";
}

?>