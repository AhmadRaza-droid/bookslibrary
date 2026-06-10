<?php
include 'config.php';

if(isset($_GET['id'])){

    $book_id = mysqli_real_escape_string($conn, $_GET['id']);

    $result = mysqli_query($conn,
    "SELECT * FROM books WHERE id='$book_id'");

    $book = mysqli_fetch_assoc($result);

    if($book){

        mysqli_query($conn,
        "UPDATE books
         SET downloads = downloads + 1
         WHERE id='$book_id'");

        $downloadLink = $book['download_epub_link'];

        if(is_numeric($downloadLink)){

            $downloadLink =
            "https://www.gutenberg.org/ebooks/" .
            $downloadLink .
            ".epub.noimages";

        }

        header("Location: $downloadLink");
        exit();

    }

}

header("Location: books.php");
exit();
?>