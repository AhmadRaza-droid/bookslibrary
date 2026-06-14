<?php

include 'config.php';

$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if($password != $confirm_password){
    echo "<script>
            alert('Passwords do not match');
            window.location.href='register.php';
          </script>";
    exit();
}

$check = mysqli_query($conn,
"SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

    echo "<script>
            alert('Email already registered');
            window.location.href='register.php';
          </script>";
    exit();
}

$query = "INSERT INTO users(fullname,email,password)
          VALUES('$fullname','$email','$password')";

$result = mysqli_query($conn,$query);

if($result){

    echo "<script>
            alert('Registration Successful');
            window.location.href='login.php';
          </script>";

}else{

    echo "<script>
            alert('Registration Failed');
            window.location.href='register.php';
          </script>";
}
?>