<?php
include 'config.php';
$books = mysqli_query($conn, "SELECT * FROM books LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="logo">📖 Library Management System</div>

    <ul>
        <li><a class="active" href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="admin_login.php">Admin</a></li>
        <li><a href="about.php">About</a></li>
    </ul>
</nav>

<section class="hero">
    <div class="hero-text">
               <h1>Welcome To <br><b>Digital Library</b></h1>
        <p>Manage books easily and explore a world of knowledge. Read. Learn. Grow.</p>

        <a href="books.php" class="btn">📖 Explore Books</a>
        <a href="login.php" class="btn-outline">👤 Login Now</a>
    </div>
</section>

<section class="features">
    <div class="card">
        <h3>📘 Huge Collection</h3>
        <p>Explore thousands of books across different categories.</p>
    </div>

    <div class="card">
        <h3>🔍 Easy Search</h3>
        <p>Find books quickly by title or author.</p>
    </div>

    <div class="card">
        <h3>📥 Easy Download</h3>
        <p>Read books online and download EPUB files.</p>
    </div>

    <div class="card">
        <h3>🛡 Secure & Reliable</h3>
        <p>Your data is safe with our secure system.</p>
    </div>
</section>

<section class="books">
    <h2>Featured Books</h2>

    <div class="book-container">

        <?php while($row = mysqli_fetch_assoc($books)){ ?>

            <div class="book-card">
                <img src="<?php echo $row['cover_image_url']; ?>" alt="Book Cover" style="width:100%; height:180px; object-fit:cover; border-radius:10px;">

                <h3><?php echo $row['title']; ?></h3>
                <p><?php echo $row['author']; ?></p>

                <a href="books.php">
                    <button>View Book</button>
                </a>
            </div>

        <?php } ?>

    </div>
</section>
 
<footer>

    <p>
        © 2026 UMT University Library. All Rights Reserved.
    </p>

    <p>
        Developed by Ahmad Raza
    </p>

</footer>
</body>
</html>