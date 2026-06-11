<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = (int)$_GET['book_id'];

mysqli_query($conn,
"DELETE FROM recently_viewed
 WHERE user_id='$user_id'
 AND book_id='$book_id'");

header("Location: profile.php");
exit();
?>