<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['request_book'])){

    $user_id = $_SESSION['user_id'];
    $book_name = mysqli_real_escape_string($conn, $_POST['book_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    mysqli_query($conn,
    "INSERT INTO book_requests(user_id, book_name, category, message)
     VALUES('$user_id','$book_name','$category','$message')");

    echo "<script>
        alert('Book request sent successfully');
        window.location.href='profile.php';
    </script>";
}
?>