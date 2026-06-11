<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['upload_image'])){

    $imageName = time() . "_" . basename($_FILES['profile_image']['name']);
    $imagePath = "uploads/profile/" . $imageName;

    move_uploaded_file($_FILES['profile_image']['tmp_name'], $imagePath);

    mysqli_query($conn,
    "UPDATE users SET profile_image='$imagePath' WHERE id='$user_id'");

    header("Location: profile.php");
    exit();
}
?>