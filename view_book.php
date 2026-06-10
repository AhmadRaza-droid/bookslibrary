<?php
session_start();
include 'config.php';

if(!isset($_GET['id'])){
    header("Location: books.php");
    exit();
}

$book_id = mysqli_real_escape_string($conn, $_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM books WHERE id='$book_id'");
$book = mysqli_fetch_assoc($result);

if(!$book){
    header("Location: books.php");
    exit();
}

if(isset($_SESSION['user_id'])){

    $user_id = $_SESSION['user_id'];

    mysqli_query($conn,
        "DELETE FROM recently_viewed
         WHERE user_id='$user_id'
         AND book_id='$book_id'"
    );

    mysqli_query($conn,
        "INSERT INTO recently_viewed(user_id, book_id)
         VALUES('$user_id','$book_id')"
    );
}

$readLink = $book['read_link'];

if(is_numeric($readLink)){
    $bookId = $readLink;
    $readLink = "https://www.gutenberg.org/files/$bookId/$bookId-h/$bookId-h.htm";
}

header("Location: $readLink");
exit();
?>