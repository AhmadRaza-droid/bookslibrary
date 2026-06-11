<?php
session_start();

include '../../config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['upload_image'])){

    $imageName = time() . "_" . basename($_FILES['profile_image']['name']);

    $uploadPath = $imageName;

    $imagePathForDB = "upload/profile/" . $imageName;

    move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath);

    mysqli_query($conn,
    "UPDATE users SET profile_image='$imagePathForDB' WHERE id='$user_id'");

    header("Location: ../../profile.php");
    exit();
}
?>