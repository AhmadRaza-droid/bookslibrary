<?php
session_start();
include 'config.php';

// ========== LOGIN ==========
if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // ✅ SIMPLE PASSWORD CHECK
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Check if email is verified
        if($user['is_verified'] == 0){
            echo "<script>
                    alert('⚠️ Please verify your email first! Check your inbox for OTP.');
                    window.location.href='verify_otp.php';
                  </script>";
            exit();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];

        echo "<script>
                alert('✅ Login Successful');
                window.location.href='index.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('❌ Invalid Email or Password');
                window.location.href='login.php';
              </script>";
        exit();
    }
}

// ========== FORGOT PASSWORD - SEND OTP ==========
if(isset($_POST['send_otp'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check) > 0){
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        mysqli_query($conn, "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'");
        $_SESSION['reset_email'] = $email;
        
        // Send OTP via email
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        
        require 'phpmailer/src/Exception.php';
        require 'phpmailer/src/PHPMailer.php';
        require 'phpmailer/src/SMTP.php';
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'universitylibrary172@gmail.com';
            $mail->Password = 'zuepxvysbxrocdef';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('universitylibrary172@gmail.com', 'Book Library');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <h2>Password Reset OTP</h2>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>Valid for 10 minutes.</p>
            ";
            $mail->send();
            
            echo "<script>
                    alert('✅ OTP sent to your email!');
                    window.location.href='login.php?step=verify_otp';
                  </script>";
            exit();
        } catch(Exception $e) {
            echo "<script>alert('❌ Failed to send OTP. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('❌ Email not found!');</script>";
    }
}

// ========== VERIFY OTP ==========
if(isset($_POST['verify_otp'])){
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['reset_email'];
    
    $query = "SELECT * FROM users WHERE email='$email' AND otp='$entered_otp'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        $_SESSION['otp_verified'] = true;
        echo "<script>
                alert('✅ OTP Verified! Set your new password.');
                window.location.href='login.php?step=reset_password';
              </script>";
        exit();
    } else {
        echo "<script>alert('❌ Invalid OTP!');</script>";
    }
}

// ========== RESET PASSWORD ==========
if(isset($_POST['reset_password'])){
    if(!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true){
        echo "<script>
                alert('Session expired. Please try again.');
                window.location.href='login.php';
              </script>";
        exit();
    }
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];
    
    if($new_password != $confirm_password){
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $query = "UPDATE users SET password='$new_password', otp=NULL WHERE email='$email'";
        $result = mysqli_query($conn, $query);
        
        if($result){
            session_destroy();
            echo "<script>
                    alert('✅ Password reset successfully! Please login.');
                    window.location.href='login.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('❌ Failed to reset password. Try again.');</script>";
        }
    }
}

$step = isset($_GET['step']) ? $_GET['step'] : 'login';
?>

<!-- Rest of your login HTML remains same -->