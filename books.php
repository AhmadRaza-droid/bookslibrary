<?php
session_start();
include 'config.php';

$result = mysqli_query($conn, "SELECT * FROM books");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
    <div class="logo">📖 Library Management System</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a class="active" href="books.php">Books</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="admin_login.php">Admin</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Available Books</h1>
    <p>Read and download free classic books.</p>
</section>

<section class="books-container">

<?php
while ($row = mysqli_fetch_assoc($result)) {

    // Book ID extract karna
    $bookId = basename($row['read_link']);

    // Direct online read link banana
    $directReadLink = "https://www.gutenberg.org/files/$bookId/{$bookId}-h/{$bookId}-h.htm";
?>

    <div class="book-card">

        <img src="<?php echo $row['cover_image_url']; ?>" alt="Book Cover">

        <h3><?php echo $row['title']; ?></h3>

        <p><strong>Author:</strong> <?php echo $row['author']; ?></p>

        <p><?php echo $row['description']; ?></p>

        <div class="book-buttons">

            <!-- Read Book -->
            <a href="<?php echo $directReadLink; ?>" target="_blank">
                <button>Read Book</button>
            </a>

            <!-- Download EPUB -->
            <a href="<?php echo $row['download_epub_link']; ?>" target="_blank">
                <button>Download EPUB</button>
            </a>

        </div>

    </div>

<?php
}
?>

</section>

</body>
</html>