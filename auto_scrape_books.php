<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$allBooks = [
    ["title"=>"Pride and Prejudice","author"=>"Jane Austen","category"=>"novel fiction classic","description"=>"A classic novel about love, manners and society.","cover"=>"https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg","read"=>"1342","download"=>"https://www.gutenberg.org/ebooks/1342.epub3.images"],
    ["title"=>"Moby Dick","author"=>"Herman Melville","category"=>"adventure novel classic","description"=>"A classic adventure novel about Captain Ahab and the white whale.","cover"=>"https://www.gutenberg.org/cache/epub/2701/pg2701.cover.medium.jpg","read"=>"2701","download"=>"https://www.gutenberg.org/ebooks/2701.epub3.images"],
    ["title"=>"The Adventures of Sherlock Holmes","author"=>"Arthur Conan Doyle","category"=>"detective mystery novel","description"=>"Detective stories featuring Sherlock Holmes.","cover"=>"https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg","read"=>"1661","download"=>"https://www.gutenberg.org/ebooks/1661.epub3.images"],
    ["title"=>"Frankenstein","author"=>"Mary Shelley","category"=>"science fiction horror classic","description"=>"A gothic novel about science, creation and responsibility.","cover"=>"https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg","read"=>"84","download"=>"https://www.gutenberg.org/ebooks/84.epub3.images"],
    ["title"=>"Alice's Adventures in Wonderland","author"=>"Lewis Carroll","category"=>"fantasy children story","description"=>"A fantasy story about Alice and Wonderland.","cover"=>"https://www.gutenberg.org/cache/epub/11/pg11.cover.medium.jpg","read"=>"11","download"=>"https://www.gutenberg.org/ebooks/11.epub3.images"],
    ["title"=>"The Time Machine","author"=>"H. G. Wells","category"=>"science fiction adventure","description"=>"A science fiction novel about time travel.","cover"=>"https://www.gutenberg.org/cache/epub/35/pg35.cover.medium.jpg","read"=>"35","download"=>"https://www.gutenberg.org/ebooks/35.epub3.images"],
    ["title"=>"Dracula","author"=>"Bram Stoker","category"=>"horror gothic novel","description"=>"A famous gothic horror novel about Count Dracula.","cover"=>"https://www.gutenberg.org/cache/epub/345/pg345.cover.medium.jpg","read"=>"345","download"=>"https://www.gutenberg.org/ebooks/345.epub3.images"],
    ["title"=>"A Tale of Two Cities","author"=>"Charles Dickens","category"=>"history novel classic","description"=>"A historical novel set in London and Paris.","cover"=>"https://www.gutenberg.org/cache/epub/98/pg98.cover.medium.jpg","read"=>"98","download"=>"https://www.gutenberg.org/ebooks/98.epub3.images"],
    ["title"=>"The Jungle Book","author"=>"Rudyard Kipling","category"=>"children adventure story","description"=>"A collection of stories about animals and adventure.","cover"=>"https://www.gutenberg.org/cache/epub/236/pg236.cover.medium.jpg","read"=>"236","download"=>"https://www.gutenberg.org/ebooks/236.epub3.images"],
    ["title"=>"The Republic","author"=>"Plato","category"=>"philosophy history education","description"=>"A famous philosophical work by Plato.","cover"=>"https://www.gutenberg.org/cache/epub/1497/pg1497.cover.medium.jpg","read"=>"1497","download"=>"https://www.gutenberg.org/ebooks/1497.epub3.images"]
];

if(isset($_POST['scrape'])){

    $keyword = strtolower(trim($_POST['keyword']));
    $quantity = (int) $_POST['quantity'];
    $count = 0;

    foreach($allBooks as $book){

        if($count >= $quantity){
            break;
        }

        $searchText = strtolower($book['title']." ".$book['author']." ".$book['category']);

        if(strpos($searchText, $keyword) !== false){

            $title = mysqli_real_escape_string($conn, $book['title']);
            $author = mysqli_real_escape_string($conn, $book['author']);
            $category = mysqli_real_escape_string($conn, $book['category']);
            $description = mysqli_real_escape_string($conn, $book['description']);
            $cover = mysqli_real_escape_string($conn, $book['cover']);
            $read_link = mysqli_real_escape_string($conn, $book['read']);
            $download_link = mysqli_real_escape_string($conn, $book['download']);

            $check = mysqli_query($conn, "SELECT * FROM books WHERE title='$title' AND author='$author'");

            if(mysqli_num_rows($check) == 0){

                $query = "INSERT INTO books
                (title, author, category, description, cover_image_url, read_link, download_epub_link, source)
                VALUES
                ('$title', '$author', '$category', '$description', '$cover', '$read_link', '$download_link', 'Local Import')";

                if(mysqli_query($conn, $query)){
                    $count++;
                }
            }
        }
    }

    echo "<script>
            alert('$count new books imported successfully');
            window.location.href='admin_dashboard.php';
          </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auto Import Books</title>
    <link rel="stylesheet" href="style.css?v=500">
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
    <p>Enter book name/category and quantity.</p>
</section>

<section class="form-section">
    <div class="form-box">
        <h2>Import Books</h2>

        <form method="POST">
            <input type="text" name="keyword" placeholder="Book name/category e.g. novel, science, history" required>

            <input type="number" name="quantity" placeholder="How many books?" min="1" max="10" required>

            <button type="submit" name="scrape">Import Books</button>
        </form>
    </div>
</section>

</body>
</html>