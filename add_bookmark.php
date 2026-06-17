<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    echo "<script>alert('Please login first!'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$note = isset($_POST['note']) ? mysqli_real_escape_string($conn, $_POST['note']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 0;

if($book_id == 0){
    echo "<script>alert('Invalid book!'); window.location.href='books.php';</script>";
    exit();
}

mysqli_query($conn, "INSERT INTO bookmarks (user_id, book_id, page, note) 
                     VALUES ('$user_id', '$book_id', '$page', '$note')");

echo "<script>
        alert('✅ Bookmark saved successfully!');
        window.location.href='read_book.php?id=$book_id';
      </script>";
exit();
?>