<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['change'])){

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    if($old == "admin123"){

        $_SESSION['admin_password'] = $new;

        echo "<script>
                alert('Password Changed Successfully');
                window.location.href='admin_dashboard.php';
              </script>";

    } else {

        echo "<script>
                alert('Old Password Incorrect');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<section class="form-section">

<div class="form-box">

<h2>Change Admin Password</h2>

<form method="POST">

<input type="password"
       name="old_password"
       placeholder="Old Password"
       required>

<input type="password"
       name="new_password"
       placeholder="New Password"
       required>

<button type="submit" name="change">
Change Password
</button>

</form>

</div>

</section>

</body>
</html>
