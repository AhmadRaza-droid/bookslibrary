<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['scrape'])){

    $keyword = mysqli_real_escape_string($conn, $_POST['keyword']);
    $quantity = (int) $_POST['quantity'];

    if($quantity <= 0){
        $quantity = 5;
    }

    $url = "https://gutendex.com/books/?search=" . urlencode($keyword);

    $response = @file_get_contents($url);

    if($response === false){
        echo "<script>
                alert('Auto scrape failed. InfinityFree API request block kar raha hai.');
                window.location.href='auto_scrape_books.php';
              </script>";
        exit();
    }

    $data = json_decode($response, true);

    if(!isset($data['results'])){
        echo "<script>
                alert('No books found');
                window.location.href='auto_scrape_books.php';
              </script>";
        exit();
    }

    $count = 0;

    foreach($data['results'] as $book){

        if($count >= $quantity){
            break;
        }

        $title = mysqli_real_escape_string($conn, $book['title']);

        $author = "Unknown";
        if(!empty($book['authors'])){
            $author = mysqli_real_escape_string($conn, $book['authors'][0]['name']);
        }

        $description = "Free public domain book from Project Gutenberg.";

        $cover = "";
        if(isset($book['formats']['image/jpeg'])){
            $cover = mysqli_real_escape_string($conn, $book['formats']['image/jpeg']);
        }

        $read_link = "";
        if(isset($book['formats']['text/html'])){
            $read_link = mysqli_real_escape_string($conn, $book['formats']['text/html']);
        }

        $download_link = "";
        if(isset($book['formats']['application/epub+zip'])){
            $download_link = mysqli_real_escape_string($conn, $book['formats']['application/epub+zip']);
        }

        $category = mysqli_real_escape_string($conn, $keyword);

        $check = mysqli_query($conn,
            "SELECT * FROM books 
             WHERE title='$title' 
             AND author='$author'"
        );

        if(mysqli_num_rows($check) == 0){

            $query = "INSERT INTO books
            (title, author, category, description, cover_image_url, read_link, download_epub_link, source)
            VALUES
            ('$title', '$author', '$category', '$description', '$cover', '$read_link', '$download_link', 'Gutendex')";

            if(mysqli_query($conn, $query)){
                $count++;
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
    <title>Auto Scrape Books</title>
    <link rel="stylesheet" href="style.css?v=400">
</head>
<body>

<nav>
    <div class="logo">📖 Auto Scrape Books</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Auto Scrape Books</h1>
    <p>Enter book name/category and quantity to import automatically.</p>
</section>

<section class="form-section">

    <div class="form-box">

        <h2>Scrape Books</h2>

        <form method="POST">

            <input type="text"
                   name="keyword"
                   placeholder="Enter book name or category e.g. science, history, novel"
                   required>

            <input type="number"
                   name="quantity"
                   placeholder="How many books?"
                   min="1"
                   max="50"
                   required>

            <button type="submit" name="scrape">
                Auto Import Books
            </button>

        </form>

    </div>

</section>

</body>
</html>