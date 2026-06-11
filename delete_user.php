<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_GET['id'])){

    $id = (int)$_GET['id'];

    mysqli_query($conn, "DELETE FROM favorites WHERE user_id='$id'");
    mysqli_query($conn, "DELETE FROM recently_viewed WHERE user_id='$id'");
    mysqli_query($conn, "DELETE FROM reviews WHERE user_id='$id'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
}

header("Location: admin_dashboard.php");
exit();
?>