<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['submit_review'])){

    $user_id = $_SESSION['user_id'];
    $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);

    $check = mysqli_query($conn,
    "SELECT * FROM reviews
     WHERE user_id='$user_id'
     AND book_id='$book_id'");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
        "UPDATE reviews
         SET rating='$rating',
             review='$review'
         WHERE user_id='$user_id'
         AND book_id='$book_id'");

    } else {

        mysqli_query($conn,
        "INSERT INTO reviews(user_id, book_id, rating, review)
         VALUES('$user_id','$book_id','$rating','$review')");
    }
}

header("Location: books.php");
exit();
?>