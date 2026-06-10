<?php

session_start();

include 'config.php';

if(!isset($_SESSION['admin'])){

    header("Location: admin_login.php");

    exit();

}

if(isset($_GET['id'])){

    $id = mysqli_real_escape_string($conn, $_GET['id']);

    mysqli_query($conn,

    "DELETE FROM reviews
     WHERE id='$id'");

}

header("Location: admin_dashboard.php");

exit();

?>