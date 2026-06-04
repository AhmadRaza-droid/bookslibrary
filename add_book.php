<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$title = $_POST['title'];
$author = $_POST['author'];
$description = $_POST['description'];
$cover_image_url = $_POST['cover_image_url'];
$read_link = $_POST['read_link'];
$download_epub_link = $_POST['download_epub_link'];

$query = "INSERT INTO books 
(title, author, description, cover_image_url, read_link, download_epub_link, source)
VALUES
('$title', '$author', '$description', '$cover_image_url', '$read_link', '$download_epub_link', 'Admin')";

if(mysqli_query($conn, $query)){
    echo "<script>
        alert('Book Added Successfully');
        window.location.href='admin_dashboard.php';
    </script>";
}else{
    echo "Error: " . mysqli_error($conn);
}
?>