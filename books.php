<?php
session_start();
$timeout = 300; // 5 minutes

if(isset($_SESSION['LAST_ACTIVITY'])){

    if(time() - $_SESSION['LAST_ACTIVITY'] > $timeout){

        session_unset();
        session_destroy();

        echo "<script>
                alert('Session expired. Please login again.');
                window.location.href='login.php';
              </script>";
        exit();
    }
}

$_SESSION['LAST_ACTIVITY'] = time();
include 'config.php';

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $result = mysqli_query($conn,

    "SELECT * FROM books
     WHERE title LIKE '%$search%'
     OR author LIKE '%$search%'
     OR description LIKE '%$search%'
     OR category LIKE '%$search%'"

    );

}
else{

    $result = mysqli_query($conn, "SELECT * FROM books");

}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Books</title>

    <link rel="stylesheet" href="style.css?v=1000">

</head>

<body>

<nav>

    <div class="logo">
        📖 Library Management System
    </div>

    <ul>

        <li><a href="index.php">Home</a></li>

        <li>
            <a class="active" href="books.php">
                Books
            </a>
        </li>

        <li><a href="profile.php">Profile</a></li>

        <li><a href="contact.php">Contact</a></li>

<?php
if(
isset($_SESSION['email'])
&&
$_SESSION['email'] ==
"universitylibrary172@gmail.com"
){
?>

<li>
    <a href="admin_dashboard.php">
        Admin Panel
    </a>
</li>

<?php } ?>

        <li><a href="about.php">About</a></li>

    </ul>

</nav>

<section class="page-header">

    <h1>Available Books</h1>

    <p>
        Read and download free books online.
    </p>

</section>

<section class="search-section">

<form method="GET" action="books.php">

<input type="text"
       name="search"
       placeholder="Search books..."
       value="<?php echo htmlspecialchars($search); ?>">

</form>

</section>

<section class="books-container">

<?php

if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

    $readLink = $row['read_link'];
    $downloadLink = $row['download_epub_link'];

    if(is_numeric($readLink)){

        $bookId = $readLink;

        $readLink =
        "https://www.gutenberg.org/files/" .
        $bookId .
        "/" .
        $bookId .
        "-h/" .
        $bookId .
        "-h.htm";

    }

    if(is_numeric($downloadLink)){

        $downloadLink =
        "https://www.gutenberg.org/ebooks/" .
        $downloadLink .
        ".epub.noimages";

    }

?>

<div class="book-card">

<img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>"
     alt="Book Cover">

<h3>
<?php echo htmlspecialchars($row['title']); ?>
</h3>

<p>
<strong>Author:</strong>
<?php echo htmlspecialchars($row['author']); ?>
</p>

<?php
if(
isset($row['category'])
&&
$row['category'] != ""
){
?>

<p>

<strong>Category:</strong>

<?php echo htmlspecialchars($row['category']); ?>

</p>

<?php } ?>

<p>
<?php echo htmlspecialchars($row['description']); ?>
</p>

<div class="book-buttons">

<a href="<?php echo htmlspecialchars($readLink); ?>"
   target="_blank">

<button>
Read Book
</button>

</a>

<a href="<?php echo htmlspecialchars($downloadLink); ?>"
   target="_blank">

<button>
Download EPUB
</button>

</a>

</div>

</div>

<?php

}

}
else{

echo "

<h2 style='text-align:center;
width:100%;'>

No books found

</h2>

";

}

?>

</section>

</body>
</html>