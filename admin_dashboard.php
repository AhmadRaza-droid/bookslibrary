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

$book_requests = mysqli_query($conn,
"SELECT book_requests.*, users.fullname, users.email
 FROM book_requests
 JOIN users ON book_requests.user_id = users.id
 ORDER BY book_requests.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=8000">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* ========== 3 DOTS HAMBURGER MENU STYLES ========== */
        .hamburger {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 45px;
            height: 45px;
            background: #0b1f3a;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .hamburger span {
            width: 22px;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: 0.3s;
        }
        .menu-panel {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100%;
            background: linear-gradient(180deg, #0b1f3a 0%, #1a3a5c 100%);
            color: white;
            z-index: 1000;
            transition: 0.3s;
            padding-top: 70px;
            box-shadow: -5px 0 20px rgba(0,0,0,0.3);
            overflow-y: auto;
        }
        .menu-panel.open {
            right: 0;
        }
        .menu-panel a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            font-size: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: 0.2s;
        }
        .menu-panel a:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 35px;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            background: none;
            border: none;
            color: white;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        .overlay.show {
            display: block;
        }
    </style>
</head>

<body>

<!-- ========== 3 DOTS HAMBURGER BUTTON ========== -->
<div class="hamburger" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
</div>

<!-- ========== MENU PANEL (REMOVED Academic Advisor, Peer Tutoring, Student Undertaking) ========== -->
<div class="menu-panel" id="menuPanel">
    <button class="close-btn" onclick="toggleMenu()">✕</button>
    
    <a href="admin_dashboard.php">
        🏠 Dashboard
    </a>
    <a href="index.php">
        🌐 Website
    </a>
    <a href="all_books.php">
        📚 All Books
    </a>
    <a href="all_users.php">
        👥 All Users
    </a>
    <a href="all_messages.php">
        💬 Messages
    </a>
    <a href="book_requests.php">
        📝 Book Requests
    </a>
    <a href="reviews.php">
        ⭐ Reviews
    </a>
    <a href="admin_logout.php">
        🚪 Logout
    </a>
</div>

<!-- ========== OVERLAY ========== -->
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

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
    <p>Manage books, users, reviews, messages, requests and analytics.</p>
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
                <a href="<?php echo htmlspecialchars($row['read_link']); ?>" target="_blank">Read</a>
            </td>
            <td>
                <a href="<?php echo htmlspecialchars($row['download_epub_link']); ?>" target="_blank">EPUB</a>
            </td>
            <td>
                <a href="delete_book.php?id=<?php echo $row['id']; ?>">
                    <button style="background:red; color:white;">Delete</button>
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
            <th>Reply</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($messages)){ ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['message']); ?></td>
            <td>
                <?php
                if(isset($row['reply']) && $row['reply'] != ""){
                    echo htmlspecialchars($row['reply']);
                } else {
                    echo "No reply yet";
                }
                ?>
            </td>
            <td>
                <form action="reply_message.php" method="POST">
                    <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                    <textarea name="reply" placeholder="Write reply..." required></textarea>
                    <button type="submit" name="reply_submit">Reply</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</section>

<section class="table-section">
<h2>🔔 Send Notification</h2>

<form action="send_notification.php" method="POST">
    <input type="text"
           name="title"
           placeholder="Notification Title"
           required>
    <textarea
        name="message"
        placeholder="Write notification..."
        required>
    </textarea>
    <button type="submit"
            name="send_notification">
        Send Notification
    </button>
</form>
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
<h2>📚 Book Requests</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Email</th>
        <th>Book Name</th>
        <th>Category</th>
        <th>Message</th>
        <th>Status</th>
    </tr>

<?php while($req = mysqli_fetch_assoc($book_requests)){ ?>
    <tr>
        <td><?php echo $req['id']; ?></td>
        <td><?php echo htmlspecialchars($req['fullname']); ?></td>
        <td><?php echo htmlspecialchars($req['email']); ?></td>
        <td><?php echo htmlspecialchars($req['book_name']); ?></td>
        <td><?php echo htmlspecialchars($req['category']); ?></td>
        <td><?php echo htmlspecialchars($req['message']); ?></td>
        <td>
            <form action="update_request_status.php" method="POST">
                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                <select name="status">
                    <option value="Pending" <?php if($req['status']=="Pending") echo "selected"; ?>>Pending</option>
                    <option value="Approved" <?php if($req['status']=="Approved") echo "selected"; ?>>Approved</option>
                    <option value="Added" <?php if($req['status']=="Added") echo "selected"; ?>>Added</option>
                    <option value="Rejected" <?php if($req['status']=="Rejected") echo "selected"; ?>>Rejected</option>
                </select>
                <button type="submit" name="update_status">Update</button>
            </form>
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
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>

        <?php while($user = mysqli_fetch_assoc($all_users)){ ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td>
                <?php if(isset($user['profile_image']) && $user['profile_image'] != ""){ ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">
                <?php } else { ?>
                    👤
                <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td>
                <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                   onclick="return confirm('Are you sure you want to delete this user?');">
                    <button style="background:red; color:white;">Delete</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</section>

<script>
function toggleMenu() {
    const menu = document.getElementById('menuPanel');
    const overlay = document.getElementById('overlay');
    menu.classList.toggle('open');
    overlay.classList.toggle('show');
}

// Close menu on escape key
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        document.getElementById('menuPanel').classList.remove('open');
        document.getElementById('overlay').classList.remove('show');
    }
});

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