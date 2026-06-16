<?php
session_start();
include 'config.php';

if(isset($_POST['reset'])){
    $email = $_SESSION['reset_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($new_password != $confirm_password){
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // ✅ SIMPLE PASSWORD - NO HASH
        $query = "UPDATE users SET password='$new_password' WHERE email='$email'";
        $result = mysqli_query($conn, $query);
        
        if($result){
            session_destroy();
            echo "<script>
                    alert('✅ Password reset successfully! Please login.');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
    }
}
?>