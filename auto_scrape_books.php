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
"category"=>"philosophy history education politics",
"description"=>"A famous philosophical work by Plato.",
"cover"=>"https://www.gutenberg.org/cache/epub/1497/pg1497.cover.medium.jpg",
"read"=>"1497",
"download"=>"https://www.gutenberg.org/ebooks/1497.epub3.images"
],

[
"title"=>"The Time Machine",
"author"=>"H. G. Wells",
"category"=>"science fiction technology adventure",
"description"=>"A science fiction novel about time travel.",
"cover"=>"https://www.gutenberg.org/cache/epub/35/pg35.cover.medium.jpg",
"read"=>"35",
"download"=>"https://www.gutenberg.org/ebooks/35.epub3.images"
],

[
"title"=>"Dracula",
"author"=>"Bram Stoker",
"category"=>"horror gothic novel fiction",
"description"=>"A horror novel about Dracula.",
"cover"=>"https://www.gutenberg.org/cache/epub/345/pg345.cover.medium.jpg",
"read"=>"345",
"download"=>"https://www.gutenberg.org/ebooks/345.epub3.images"
],

[
"title"=>"The Adventures of Sherlock Holmes",
"author"=>"Arthur Conan Doyle",
"category"=>"detective mystery crime novel",
"description"=>"Detective stories featuring Sherlock Holmes.",
"cover"=>"https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg",
"read"=>"1661",
"download"=>"https://www.gutenberg.org/ebooks/1661.epub3.images"
],

[
"title"=>"Pride and Prejudice",
"author"=>"Jane Austen",
"category"=>"romance novel fiction classic",
"description"=>"A classic novel about love, society, and manners.",
"cover"=>"https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg",
"read"=>"1342",
"download"=>"https://www.gutenberg.org/ebooks/1342.epub3.images"
],

[
"title"=>"Moby Dick",
"author"=>"Herman Melville",
"category"=>"adventure sea novel classic",
"description"=>"A classic adventure novel about Captain Ahab and the white whale.",
"cover"=>"https://www.gutenberg.org/cache/epub/2701/pg2701.cover.medium.jpg",
"read"=>"2701",
"download"=>"https://www.gutenberg.org/ebooks/2701.epub3.images"
],

[
"title"=>"Alice's Adventures in Wonderland",
"author"=>"Lewis Carroll",
"category"=>"children fantasy story adventure",
"description"=>"A fantasy story about Alice and Wonderland.",
"cover"=>"https://www.gutenberg.org/cache/epub/11/pg11.cover.medium.jpg",
"read"=>"11",
"download"=>"https://www.gutenberg.org/ebooks/11.epub3.images"
],

[
"title"=>"A Tale of Two Cities",
"author"=>"Charles Dickens",
"category"=>"history novel revolution classic",
"description"=>"A historical novel set in London and Paris.",
"cover"=>"https://www.gutenberg.org/cache/epub/98/pg98.cover.medium.jpg",
"read"=>"98",
"download"=>"https://www.gutenberg.org/ebooks/98.epub3.images"
],

[
"title"=>"The War of the Worlds",
"author"=>"H. G. Wells",
"category"=>"science fiction alien movie adventure",
"description"=>"A science fiction novel about alien invasion.",
"cover"=>"https://www.gutenberg.org/cache/epub/36/pg36.cover.medium.jpg",
"read"=>"36",
"download"=>"https://www.gutenberg.org/ebooks/36.epub3.images"
],

[
"title"=>"The Jungle Book",
"author"=>"Rudyard Kipling",
"category"=>"children adventure jungle story",
"description"=>"A collection of adventure stories about animals and jungle life.",
"cover"=>"https://www.gutenberg.org/cache/epub/236/pg236.cover.medium.jpg",
"read"=>"236",
"download"=>"https://www.gutenberg.org/ebooks/236.epub3.images"
],

[
"title"=>"Frankenstein",
"author"=>"Mary Shelley",
"category"=>"science fiction horror novel",
"description"=>"A gothic science fiction novel about creation and responsibility.",
"cover"=>"https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg",
"read"=>"84",
"download"=>"https://www.gutenberg.org/ebooks/84.epub3.images"
],

[
"title"=>"The Art of War",
"author"=>"Sun Tzu",
"category"=>"war strategy business history",
"description"=>"A classic book about strategy and leadership.",
"cover"=>"https://www.gutenberg.org/cache/epub/132/pg132.cover.medium.jpg",
"read"=>"132",
"download"=>"https://www.gutenberg.org/ebooks/132.epub3.images"
],

[
"title"=>"Think and Grow Rich",
"author"=>"Napoleon Hill",
"category"=>"business finance money success selfhelp",
"description"=>"A motivational book about success and personal achievement.",
"cover"=>"https://www.gutenberg.org/cache/epub/610/pg610.cover.medium.jpg",
"read"=>"610",
"download"=>"https://www.gutenberg.org/ebooks/610.epub3.images"
],

[
"title"=>"The Prophet",
"author"=>"Kahlil Gibran",
"category"=>"poetry philosophy literature",
"description"=>"A poetic and philosophical book by Kahlil Gibran.",
"cover"=>"https://www.gutenberg.org/cache/epub/58585/pg58585.cover.medium.jpg",
"read"=>"58585",
"download"=>"https://www.gutenberg.org/ebooks/58585.epub3.images"
],

[
"title"=>"Python Programming Basics",
"author"=>"Library Team",
"category"=>"programming coding computer python technology",
"description"=>"A beginner friendly programming book for learning Python concepts.",
"cover"=>"https://upload.wikimedia.org/wikipedia/commons/c/c3/Python-logo-notext.svg",
"read"=>"https://docs.python.org/3/tutorial/",
"download"=>"https://docs.python.org/3/archives/python-3.12.0-docs-pdf-a4.zip"
],

[
"title"=>"Web Development Guide",
"author"=>"Library Team",
"category"=>"programming web development html css javascript",
"description"=>"A beginner guide for learning web development basics.",
"cover"=>"https://upload.wikimedia.org/wikipedia/commons/6/61/HTML5_logo_and_wordmark.svg",
"read"=>"https://developer.mozilla.org/en-US/docs/Learn",
"download"=>"https://developer.mozilla.org/en-US/docs/Learn"
],

[
"title"=>"Computer Networking Notes",
"author"=>"Library Team",
"category"=>"networking computer technology engineering",
"description"=>"Basic notes about computer networking concepts.",
"cover"=>"https://upload.wikimedia.org/wikipedia/commons/d/d2/Internet_map_1024.jpg",
"read"=>"https://www.geeksforgeeks.org/computer-network-tutorials/",
"download"=>"https://www.geeksforgeeks.org/computer-network-tutorials/"
],

[
"title"=>"Islamic Studies Introduction",
"author"=>"Library Team",
"category"=>"islamic religion quran hadith urdu",
"description"=>"An introductory book for Islamic studies and basic concepts.",
"cover"=>"",
"read"=>"https://quran.com/",https://images.unsplash.com/photo-1542816417-0983670d7f62?q=80&w=800&auto=format&fit=crop
"download"=>"https://quran.com/"
]

];

function addBook($conn, $book){

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
         AND author='$author'"
    );

    if(mysqli_num_rows($check) > 0){
        return false;
    }

    $query = "INSERT INTO books
    (title, author, category, description, cover_image_url, read_link, download_epub_link, source)
    VALUES
    ('$title','$author','$category','$description','$cover','$read_link','$download_link','Local Import')";

    return mysqli_query($conn, $query);
}

if(isset($_POST['scrape'])){

    $keyword = strtolower(trim($_POST['keyword']));
    $quantity = (int)$_POST['quantity'];

    if($quantity <= 0){
        $quantity = 5;
    }

    $count = 0;

    $keywords = explode(" ", $keyword);

    foreach($allBooks as $book){

        if($count >= $quantity){
            break;
        }

        $searchText = strtolower(
            $book['title']." ".
            $book['author']." ".
            $book['category']." ".
            $book['description']
        );

        $matched = false;

        if(empty($keyword)){
            $matched = true;
        } else {
            foreach($keywords as $word){
                $word = trim($word);

                if($word != "" && strpos($searchText, $word) !== false){
                    $matched = true;
                    break;
                }
            }
        }

        if($matched){
            if(addBook($conn, $book)){
                $count++;
            }
        }
    }

    if($count == 0){

        foreach($allBooks as $book){

            if($count >= $quantity){
                break;
            }

            if(addBook($conn, $book)){
                $count++;
            }
        }
    }

    echo "<script>
            alert('$count books imported successfully. Duplicate books skipped.');
            window.location.href='admin_dashboard.php';
          </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auto Import Books</title>
    <link rel="stylesheet" href="style.css?v=700">
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
    <p>Search by title/category: programming, science, adventure, movie, history, islamic, urdu, business, horror, detective, novel, philosophy.</p>
</section>

<section class="form-section">

<div class="form-box">

<h2>Import Books</h2>

<form method="POST">

<input type="text"
       name="keyword"
       placeholder="Enter category/title e.g. science, programming, movie">

<input type="number"
       name="quantity"
       placeholder="How many books?"
       min="1"
       max="20"
       required>

<button type="submit" name="scrape">
Import Books
</button>

</form>

</div>

</section>

</body>
</html>