<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$books = mysqli_query($conn, "SELECT * FROM books");
$messages = mysqli_query($conn, "SELECT * FROM messages");
$all_users = mysqli_query($conn, "SELECT * FROM users");

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$total_books = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM books"));
$total_reviews = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM reviews"));

$total_downloads_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(downloads) AS total FROM books"));
$total_downloads = $total_downloads_data['total'] ?? 0;

$most_downloaded = mysqli_query($conn, "SELECT title, downloads FROM books ORDER BY downloads DESC LIMIT 5");

$most_favorites = mysqli_query($conn,
"SELECT books.title, COUNT(favorites.id) AS total
 FROM favorites
 JOIN books ON favorites.book_id = books.id
 GROUP BY books.id
 ORDER BY total DESC
 LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=6000">
</head>

<body>

<nav>
    <div class="logo">📖 Admin Panel</div>

    <ul>
        <li><a href="index.php">Website</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Manage books, users, reviews and analytics.</p>
</section>

<div style="display:flex; gap:20px; margin:20px; flex-wrap:wrap;">

    <div style="background:#0b1f3a; color:white; padding:20px; border-radius:10px; width:250px;">
        <h2>Total Users</h2>
        <h1><?php echo $total_users; ?></h1>
    </div>

    <div style="background:#0b1f3a; color:white; padding:20px; border-radius:10px; width:250px;">
        <h2>Total Books</h2>
        <h1><?php echo $total_books; ?></h1>
    </div>

    <div style="background:#0b1f3a; color:white; padding:20px; border-radius:10px; width:250px;">
        <h2>Total Reviews</h2>
        <h1><?php echo $total_reviews; ?></h1>
    </div>

    <div style="background:#0b1f3a; color:white; padding:20px; border-radius:10px; width:250px;">
        <h2>Total Downloads</h2>
        <h1><?php echo $total_downloads; ?></h1>
    </div>

    <div style="background:#ffc72c; color:#061b33; padding:20px; border-radius:10px; width:250px;">
        <h2>Auto Import</h2>

        <a href="auto_scrape_books.php">
            <button style="margin-top:10px; padding:12px 18px; border:none; border-radius:8px; background:#061b33; color:white; cursor:pointer;">
                Auto Import Books
            </button>
        </a>
    </div>

</div>

<section class="table-section">
<h2>Most Downloaded Books</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Book</th>
    <th>Downloads</th>
</tr>

<?php while($row = mysqli_fetch_assoc($most_downloaded)){ ?>
<tr>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo (int)$row['downloads']; ?></td>
</tr>
<?php } ?>

</table>
</section>

<section class="table-section">
<h2>Most Favorited Books</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Book</th>
    <th>Favorites</th>
</tr>

<?php while($row = mysqli_fetch_assoc($most_favorites)){ ?>
<tr>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo (int)$row['total']; ?></td>
</tr>
<?php } ?>

</table>
</section>

<section class="table-section">

    <h2>Add New Book by Link</h2>

    <form action="add_book.php" method="POST">

        <input type="text" name="title" placeholder="Book Title" required>

        <input type="text" name="author" placeholder="Author Name" required>

        <textarea name="description" placeholder="Book Description" required></textarea>

        <input type="text" name="cover_image_url" placeholder="Cover Image URL" required>

        <input type="text" name="read_link" placeholder="Read Link" required>

        <input type="text" name="download_epub_link" placeholder="Download EPUB Link" required>

        <button type="submit">Add Book</button>

    </form>

</section>

<section class="table-section">

    <h2>All Books</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>Read</th>
            <th>Download</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($books)){ ?>

        <tr>

            <td><?php echo $row['id']; ?></td>

            <td>
                <img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>" width="60">
            </td>

            <td><?php echo htmlspecialchars($row['title']); ?></td>

            <td><?php echo htmlspecialchars($row['author']); ?></td>

            <td>
                <a href="<?php echo htmlspecialchars($row['read_link']); ?>" target="_blank">
                    Read
                </a>
            </td>

            <td>
                <a href="<?php echo htmlspecialchars($row['download_epub_link']); ?>" target="_blank">
                    EPUB
                </a>
            </td>

            <td>
                <a href="delete_book.php?id=<?php echo $row['id']; ?>">
                    <button>Delete</button>
                </a>
            </td>

        </tr>

        <?php } ?>

    </table>

</section>

<section class="table-section">

    <h2>User Messages</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($messages)){ ?>

        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['message']); ?></td>
        </tr>

        <?php } ?>

    </table>

</section>

<section class="table-section">

<h2>Book Reviews</h2>

<table border="1" cellpadding="10">

<tr>
    <th>ID</th>
    <th>User</th>
    <th>Book</th>
    <th>Rating</th>
    <th>Review</th>
    <th>Action</th>
</tr>

<?php
$reviews = mysqli_query($conn,
"SELECT reviews.*, users.fullname, books.title
 FROM reviews
 JOIN users ON reviews.user_id = users.id
 JOIN books ON reviews.book_id = books.id
 ORDER BY reviews.id DESC");

while($review = mysqli_fetch_assoc($reviews)){
?>

<tr>
<td><?php echo $review['id']; ?></td>
<td><?php echo htmlspecialchars($review['fullname']); ?></td>
<td><?php echo htmlspecialchars($review['title']); ?></td>
<td><?php echo $review['rating']; ?> ⭐</td>
<td><?php echo htmlspecialchars($review['review']); ?></td>
<td>
<a href="delete_review.php?id=<?php echo $review['id']; ?>">
<button style="background:red; color:white;">Delete</button>
</a>
</td>
</tr>

<?php } ?>

</table>

</section>

<section class="table-section">

    <h2>Registered Users</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
        </tr>

        <?php while($user = mysqli_fetch_assoc($all_users)){ ?>

        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>

        <?php } ?>

    </table>

</section>

<script>
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
    }
    else{
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>