<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['upload'])){

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $pdfName = time() . "_" . basename($_FILES['pdf']['name']);
    $coverName = time() . "_" . basename($_FILES['cover']['name']);

    $pdfPath = "uploads/pdfs/" . $pdfName;
    $coverPath = "uploads/covers/" . $coverName;

    move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath);
    move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath);

    $query = "INSERT INTO books
    (title, author, category, description, cover_image_url, read_link, download_epub_link, pdf_file, cover_file, source)
    VALUES
    ('$title', '$author', '$category', '$description', '$coverPath', '$pdfPath', '$pdfPath', '$pdfPath', '$coverPath', 'PDF Upload')";

    mysqli_query($conn, $query);

    echo "<script>
            alert('PDF Book Uploaded Successfully');
            window.location.href='admin_dashboard.php';
          </script>";
}
?>