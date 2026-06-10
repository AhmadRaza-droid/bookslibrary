<?php
include 'session_timeout.php';
include 'config.php';

$search = "";
$category = "";

$where = "WHERE 1";

if(isset($_GET['search']) && $_GET['search'] != ""){

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $where .= " AND (
        title LIKE '%$search%'
        OR author LIKE '%$search%'
        OR description LIKE '%$search%'
        OR category LIKE '%$search%'
    )";
}

if(isset($_GET['category']) && $_GET['category'] != ""){

    $category = mysqli_real_escape_string($conn, $_GET['category']);

    $where .= " AND category LIKE '%$category%'";
}

$result = mysqli_query($conn, "SELECT * FROM books $where");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Books</title>

    <link rel="stylesheet" href="style.css?v=1200">

</head>

<body>

<nav>

    <div class="logo">
        📖 Library Management System
    </div>

    <ul>

        <li>
            <a href="index.php">
                Home
            </a>
        </li>

        <li>
            <a class="active" href="books.php">
                Books
            </a>
        </li>

        <li>
            <a href="profile.php">
                Profile
            </a>
        </li>

        <li>
            <a href="contact.php">
                Contact
            </a>
        </li>

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

        <li>
            <a href="about.php">
                About
            </a>
        </li>

    </ul>

</nav>

<section class="page-header">

    <h1>
        Available Books
    </h1>

    <p>
        Read and download free books online.
    </p>

</section>

<section class="search-section">

<form method="GET" action="books.php">

<input type="text"
       name="search"
       placeholder="Search books by title, author or description..."
       value="<?php echo htmlspecialchars($search); ?>">

<select name="category"
style="width:100%;
padding:14px;
margin-top:12px;
border-radius:10px;
border:2px solid #ddd;">

<option value="">
All Categories
</option>

<option value="programming"
<?php if($category=="programming") echo "selected"; ?>>
Programming
</option>

<option value="science"
<?php if($category=="science") echo "selected"; ?>>
Science
</option>

<option value="adventure"
<?php if($category=="adventure") echo "selected"; ?>>
Adventure
</option>

<option value="movie"
<?php if($category=="movie") echo "selected"; ?>>
Movie
</option>

<option value="history"
<?php if($category=="history") echo "selected"; ?>>
History
</option>

<option value="islamic"
<?php if($category=="islamic") echo "selected"; ?>>
Islamic
</option>

<option value="urdu"
<?php if($category=="urdu") echo "selected"; ?>>
Urdu
</option>

<option value="business"
<?php if($category=="business") echo "selected"; ?>>
Business
</option>

<option value="horror"
<?php if($category=="horror") echo "selected"; ?>>
Horror
</option>

<option value="detective"
<?php if($category=="detective") echo "selected"; ?>>
Detective
</option>

<option value="novel"
<?php if($category=="novel") echo "selected"; ?>>
Novel
</option>

<option value="philosophy"
<?php if($category=="philosophy") echo "selected"; ?>>
Philosophy
</option>

</select>

<button type="submit"
style="margin-top:12px;
padding:12px 20px;
border:none;
border-radius:8px;
background:#061b33;
color:white;
cursor:pointer;">

Filter Books

</button>

</form>

</section>

<section class="books-container">

<?php

if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

    $readLink = $row['read_link'];

    $downloadLink = $row['download_epub_link'];

    // DIRECT ONLINE READING FIX

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

    // DIRECT EPUB DOWNLOAD FIX

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

<strong>
Author:
</strong>

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

<strong>
Category:
</strong>

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
<?php if(isset($_SESSION['user_id'])){ ?>

<a href="favorite_book.php?book_id=<?php echo $row['id']; ?>">

<button style="background:red;">
❤️ Favorite
</button>

</a>

<?php } ?>

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