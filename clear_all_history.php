<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Delete ALL user activity
mysqli_query($conn, "DELETE FROM recently_viewed WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM reading_progress WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM favorites WHERE user_id='$user_id'");

echo "<script>alert('✅ All activity cleared successfully!'); window.location='profile.php';</script>";
?>