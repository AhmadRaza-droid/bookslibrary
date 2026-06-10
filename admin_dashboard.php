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

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$books = mysqli_query($conn, "SELECT * FROM books");
$messages = mysqli_query($conn, "SELECT * FROM messages");
$all_users = mysqli_query($conn, "SELECT * FROM users");

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$total_books = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM books"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=250">
</head>

<body>

<nav>
    <div class="logo">📖 Admin Panel</div>

    <ul>
        <li><a href="index.php">Website</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Manage books, users and messages.</p>
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

    <div style="background:#ffc72c; color:#061b33; padding:20px; border-radius:10px; width:250px;">
        <h2>Auto Import</h2>

        <a href="auto_scrape_books.php">
            <button style="margin-top:10px; padding:12px 18px; border:none; border-radius:8px; background:#061b33; color:white; cursor:pointer;">
                Auto Import Books
            </button>
        </a>
    </div>

</div>

<!-- ADD BOOK -->

<section class="table-section">

    <h2>Add New Book</h2>

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

<!-- ALL BOOKS -->

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
                <img src="<?php echo $row['cover_image_url']; ?>" width="60">
            </td>

            <td><?php echo $row['title']; ?></td>

            <td><?php echo $row['author']; ?></td>

            <td>
                <a href="<?php echo $row['read_link']; ?>" target="_blank">
                    Read
                </a>
            </td>

            <td>
                <a href="<?php echo $row['download_epub_link']; ?>" target="_blank">
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

<!-- USER MESSAGES -->

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

            <td><?php echo $row['name']; ?></td>

            <td><?php echo $row['email']; ?></td>

            <td><?php echo $row['message']; ?></td>

        </tr>

        <?php } ?>

    </table>

</section>

<!-- USERS -->

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

            <td><?php echo $user['fullname']; ?></td>

            <td><?php echo $user['email']; ?></td>

        </tr>

        <?php } ?>

    </table>

</section>

</body>
</html>