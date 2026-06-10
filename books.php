<?php
session_start();
include 'config.php';

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $result = mysqli_query($conn,
        "SELECT * FROM books
         WHERE title LIKE '%$search%'
         OR author LIKE '%$search%'
         OR description LIKE '%$search%'"
    );
} else {
    $result = mysqli_query($conn, "SELECT * FROM books");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books</title>
    <link rel="stylesheet" href="style.css?v=20">
</head>

<body>

<nav>
    <div class="logo">📖 Library Management System</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a class="active" href="books.php">Books</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
    <li><a href="admin_dashboard.php">Admin Panel</a></li>
<?php } ?>
        <li><a href="about.php">About</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Available Books</h1>
    <p>Read and download free classic books.</p>
</section>

<section class="search-section">
    <form method="GET" action="books.php">
        <input type="text"
               name="search"
               placeholder="Search books by title, author or description..."
               value="<?php echo htmlspecialchars($search); ?>">
    </form>
</section>

<section class="books-container">

<?php
if(mysqli_num_rows($result) > 0){

while ($row = mysqli_fetch_assoc($result)) {

    $bookId = basename($row['read_link']);
   if(is_numeric($bookId)){

    $directReadLink =
    "https://www.gutenberg.org/ebooks/$bookId";

}
else{

    $directReadLink = $row['read_link'];

}
?>

    <div class="book-card">

        <img src="<?php echo $row['cover_image_url']; ?>" alt="Book Cover">

        <h3><?php echo $row['title']; ?></h3>

        <p><strong>Author:</strong> <?php echo $row['author']; ?></p>

        <p><?php echo $row['description']; ?></p>

        <div class="book-buttons">

            <a href="<?php echo $directReadLink; ?>" target="_blank">
                <button>Read Book</button>
            </a>

            <a href="<?php echo $row['download_epub_link']; ?>" target="_blank">
                <button>Download EPUB</button>
            </a>

        </div>

    </div>

<?php
}

} else {
    echo "<h2 style='text-align:center; width:100%;'>No books found</h2>";
}
?>

</section>

</body>
</html>