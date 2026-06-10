<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['import'])){

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $cover = mysqli_real_escape_string($conn, $_POST['cover_image_url']);
    $read_link = mysqli_real_escape_string($conn, $_POST['read_link']);
    $download_link = mysqli_real_escape_string($conn, $_POST['download_epub_link']);

    $check = mysqli_query($conn,
        "SELECT * FROM books 
         WHERE title='$title' 
         AND author='$author'"
    );

    if(mysqli_num_rows($check) > 0){

        echo "<script>
                alert('This book already exists');
                window.location.href='auto_scrape_books.php';
              </script>";
        exit();
    }

    $query = "INSERT INTO books
    (title, author, category, description, cover_image_url, read_link, download_epub_link, source)
    VALUES
    ('$title', '$author', '$category', '$description', '$cover', '$read_link', '$download_link', 'Admin Import')";

    if(mysqli_query($conn, $query)){
        echo "<script>
                alert('Book imported successfully');
                window.location.href='admin_dashboard.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Import Book</title>
    <link rel="stylesheet" href="style.css?v=300">
</head>
<body>

<nav>
    <div class="logo">📖 Import Book</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Import Custom Book</h1>
    <p>Add a new book manually without duplicate entries.</p>
</section>

<section class="table-section">

    <h2>Book Details</h2>

    <form method="POST">

        <input type="text" name="title" placeholder="Book Title" required>

        <input type="text" name="author" placeholder="Author Name" required>

        <input type="text" name="category" placeholder="Category e.g Fiction, Science, Programming" required>

        <textarea name="description" placeholder="Book Description" required></textarea>

        <input type="text" name="cover_image_url" placeholder="Cover Image URL" required>

        <input type="text" name="read_link" placeholder="Read Book ID or Link" required>

        <input type="text" name="download_epub_link" placeholder="Download EPUB Link" required>

        <button type="submit" name="import">Import Book</button>

    </form>

</section>

</body>
</html>