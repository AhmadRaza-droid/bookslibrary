<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_GET['id'])){
    header("Location: books.php");
    exit();
}

$id = (int)$_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM books WHERE id='$id'");
$book = mysqli_fetch_assoc($result);

if(!$book){
    header("Location: books.php");
    exit();
}

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    mysqli_query($conn,
    "INSERT INTO recently_viewed(user_id, book_id)
     VALUES('$user_id','$id')");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="style.css?v=9000">
</head>

<body>

<nav>
    <div class="logo">📖 Reading Book</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="profile.php">Profile</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1><?php echo htmlspecialchars($book['title']); ?></h1>
    <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
</section>

<div style="padding:20px;">

    <iframe src="<?php echo htmlspecialchars($book['read_link']); ?>"
            style="width:100%; height:850px; border:2px solid #061b33; border-radius:12px;">
    </iframe>

</div>

</body>
</html>