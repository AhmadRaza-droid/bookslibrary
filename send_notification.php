<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['send_notification'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    mysqli_query($conn,
    "INSERT INTO notifications(title,message)
     VALUES('$title','$message')");

    header("Location: admin_dashboard.php");
    exit();
}
?>