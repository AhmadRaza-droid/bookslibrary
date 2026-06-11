<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['reply_submit'])){

    $message_id = (int)$_POST['message_id'];
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);

    mysqli_query($conn,
    "UPDATE messages SET reply='$reply' WHERE id='$message_id'");

    echo "<script>
            alert('Reply saved successfully');
            window.location.href='admin_dashboard.php';
          </script>";
}
?>