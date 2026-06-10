<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$allBooks = [

[
"title"=>"The Republic",
"author"=>"Plato",
"category"=>"philosophy history education",
"description"=>"A famous philosophical work by Plato.",
"cover"=>"https://www.gutenberg.org/cache/epub/1497/pg1497.cover.medium.jpg",
"read"=>"1497",
"download"=>"https://www.gutenberg.org/ebooks/1497.epub3.images"
],

[
"title"=>"The Time Machine",
"author"=>"H. G. Wells",
"category"=>"science fiction",
"description"=>"A science fiction novel about time travel.",
"cover"=>"https://www.gutenberg.org/cache/epub/35/pg35.cover.medium.jpg",
"read"=>"35",
"download"=>"https://www.gutenberg.org/ebooks/35.epub3.images"
],

[
"title"=>"Dracula",
"author"=>"Bram Stoker",
"category"=>"horror gothic",
"description"=>"A horror novel about Dracula.",
"cover"=>"https://www.gutenberg.org/cache/epub/345/pg345.cover.medium.jpg",
"read"=>"345",
"download"=>"https://www.gutenberg.org/ebooks/345.epub3.images"
],

[
"title"=>"Sherlock Holmes",
"author"=>"Arthur Conan Doyle",
"category"=>"detective mystery",
"description"=>"Detective stories featuring Sherlock Holmes.",
"cover"=>"https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg",
"read"=>"1661",
"download"=>"https://www.gutenberg.org/ebooks/1661.epub3.images"
]

];

if(isset($_POST['scrape'])){

    $keyword = strtolower(trim($_POST['keyword']));
    $quantity = (int)$_POST['quantity'];

    $count = 0;

    foreach($allBooks as $book){

        if($count >= $quantity){
            break;
        }

        $search =
        strtolower(
            $book['title']." ".
            $book['author']." ".
            $book['category']
        );

        // MATCH CHECK
        if(
            strpos($search, $keyword) !== false
            ||
            empty($keyword)
        ){

            $title = mysqli_real_escape_string($conn, $book['title']);
            $author = mysqli_real_escape_string($conn, $book['author']);
            $category = mysqli_real_escape_string($conn, $book['category']);
            $description = mysqli_real_escape_string($conn, $book['description']);
            $cover = mysqli_real_escape_string($conn, $book['cover']);
            $read_link = mysqli_real_escape_string($conn, $book['read']);
            $download_link = mysqli_real_escape_string($conn, $book['download']);

            // DUPLICATE CHECK
            $check = mysqli_query($conn,
            "SELECT * FROM books
             WHERE title='$title'
             AND author='$author'");

            if(mysqli_num_rows($check) == 0){

                mysqli_query($conn,

                "INSERT INTO books
                (title, author, category, description,
                cover_image_url, read_link,
                download_epub_link, source)

                VALUES

                ('$title','$author','$category',
                '$description','$cover',
                '$read_link','$download_link',
                'Local Import')"

                );

                $count++;
            }
        }
    }

    // IF NO MATCH FOUND
    if($count == 0){

        foreach($allBooks as $book){

            if($count >= $quantity){
                break;
            }

            $title = mysqli_real_escape_string($conn, $book['title']);
            $author = mysqli_real_escape_string($conn, $book['author']);
            $category = mysqli_real_escape_string($conn, $book['category']);
            $description = mysqli_real_escape_string($conn, $book['description']);
            $cover = mysqli_real_escape_string($conn, $book['cover']);
            $read_link = mysqli_real_escape_string($conn, $book['read']);
            $download_link = mysqli_real_escape_string($conn, $book['download']);

            $check = mysqli_query($conn,
            "SELECT * FROM books
             WHERE title='$title'
             AND author='$author'");

            if(mysqli_num_rows($check) == 0){

                mysqli_query($conn,

                "INSERT INTO books
                (title, author, category, description,
                cover_image_url, read_link,
                download_epub_link, source)

                VALUES

                ('$title','$author','$category',
                '$description','$cover',
                '$read_link','$download_link',
                'Local Import')"

                );

                $count++;
            }
        }
    }

    echo "<script>
    alert('$count books imported successfully');
    window.location.href='admin_dashboard.php';
    </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auto Import Books</title>
    <link rel="stylesheet" href="style.css?v=600">
</head>

<body>

<nav>
    <div class="logo">📖 Auto Import Books</div>

    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Auto Import Books</h1>
    <p>Search books by category or title.</p>
</section>

<section class="form-section">

<div class="form-box">

<h2>Import Books</h2>

<form method="POST">

<input type="text"
       name="keyword"
       placeholder="science, horror, detective">

<input type="number"
       name="quantity"
       placeholder="How many books?"
       required>

<button type="submit" name="scrape">
Import Books
</button>

</form>

</div>

</section>

</body>
</html>