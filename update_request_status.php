<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['update_status'])){

    $request_id = (int)$_POST['request_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn,
    "UPDATE book_requests
     SET status='$status'
     WHERE id='$request_id'");
}

header("Location: admin_dashboard.php");
exit();
?>