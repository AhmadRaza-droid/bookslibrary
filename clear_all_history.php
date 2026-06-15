<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];

// Delete ALL user activity
mysqli_query($conn, "DELETE FROM recently_viewed WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM reading_progress WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM favorites WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM notifications");  // All notifications
mysqli_query($conn, "DELETE FROM book_requests WHERE user_id='$user_id'");
mysqli_query($conn, "DELETE FROM messages WHERE email='$user_email'");

echo "<script>alert('✅ All activity cleared successfully!\\n\\nDeleted:\\n• Notifications\\n• Reading History\\n• Reading Progress\\n• Bookmarks\\n• Favorites\\n• Book Requests\\n• Messages'); window.location='profile.php';</script>";
?>