<?php

session_start();

include 'config.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");

    exit();

}

$user_id = $_SESSION['user_id'];

$book_id = $_GET['book_id'];

$check = mysqli_query($conn,

"SELECT * FROM favorites
WHERE user_id='$user_id'
AND book_id='$book_id'"

);

if(mysqli_num_rows($check) == 0){

    mysqli_query($conn,

    "INSERT INTO favorites(user_id, book_id)
    VALUES('$user_id','$book_id')"

    );

}

header("Location: books.php");

?>