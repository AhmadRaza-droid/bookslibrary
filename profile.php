<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM users WHERE id='$user_id'");

$user = mysqli_fetch_assoc($result);

$user_email = $user['email'];

$favorites = mysqli_query($conn,
"SELECT books.*
 FROM favorites
 JOIN books ON favorites.book_id = books.id
 WHERE favorites.user_id='$user_id'");

$recentlyViewed = mysqli_query($conn,
"SELECT books.*
 FROM recently_viewed
 JOIN books ON recently_viewed.book_id = books.id
 WHERE recently_viewed.user_id='$user_id'
 ORDER BY recently_viewed.viewed_at DESC
 LIMIT 5");

$myMessages = mysqli_query($conn,
"SELECT * FROM messages
 WHERE email='$user_email'
 ORDER BY id DESC");

$myRequests = mysqli_query($conn,
"SELECT *
 FROM book_requests
 WHERE user_id='$user_id'
 ORDER BY id DESC");

$notifications = mysqli_query($conn,
"SELECT * FROM notifications
 ORDER BY id DESC
 LIMIT 5");

$readingProgress = mysqli_query($conn,
"SELECT reading_progress.*, books.title
 FROM reading_progress
 JOIN books ON reading_progress.book_id = books.id
 WHERE reading_progress.user_id='$user_id'
 ORDER BY reading_progress.updated_at DESC");

$bookmarks = mysqli_query($conn,
"SELECT bookmarks.*, books.title
 FROM bookmarks
 JOIN books ON bookmarks.book_id = books.id
 WHERE bookmarks.user_id='$user_id'
 ORDER BY bookmarks.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css?v=11000">
    <style>
        .clear-history-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .clear-history-btn:hover {
            background: #c82333;
        }
    </style>
</head>

<body>

<nav>
    <div class="logo">📖 Library Management System</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a class="active" href="profile.php">Profile</a></li>

        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
            <li><a href="admin_dashboard.php">Admin Panel</a></li>
        <?php } ?>

        <li><a href="logout.php">Logout</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="form-section">

<div class="form-box profile-box">

<h2>My Profile</h2>

<?php if(isset($user['profile_image']) && $user['profile_image'] != ""){ ?>

    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>"
         alt="Profile Picture"
         style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:15px;border:3px solid #061b33;">

<?php } else { ?>

    <div style="font-size:75px;margin:10px;">👤</div>

<?php } ?>

<form action="upload_profile_image.php" method="POST" enctype="multipart/form-data" style="margin-bottom:20px;">

    <input type="file" name="profile_image" accept="image/*" required>

    <button type="submit" name="upload_image">
        Upload Profile Picture
    </button>

</form>

<p><b>Name:</b> <?php echo htmlspecialchars($user['fullname']); ?></p>
<p><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></p>
<p><b>Password:</b> ********</p>

<br>

<a href="change_password.php">
    <button>Change Password</button>
</a>

<br><br>

<a href="logout.php">
    <button>Logout</button>
</a>

<h2 class="profile-heading">🔔 Notifications</h2>

<?php if(mysqli_num_rows($notifications) > 0){ ?>

<?php while($note = mysqli_fetch_assoc($notifications)){ ?>

<div class="profile-book-card">
    <h3><?php echo htmlspecialchars($note['title']); ?></h3>
    <p><?php echo htmlspecialchars($note['message']); ?></p>
</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No notifications yet.</p>

<?php } ?>

<h2 class="profile-heading">📚 Request a Book</h2>

<form action="request_book.php" method="POST">

    <input type="text" name="book_name" placeholder="Enter book name" required>

    <input type="text" name="category" placeholder="Category e.g. Programming, Science">

    <textarea name="message" placeholder="Write details..."></textarea>

    <button type="submit" name="request_book">
        Send Request
    </button>

</form>

<h2 class="profile-heading">❤️ My Favorite Books</h2>

<?php if(mysqli_num_rows($favorites) > 0){ ?>

<?php while($book = mysqli_fetch_assoc($favorites)){ ?>

<div class="profile-book-card">

    <h3><?php echo htmlspecialchars($book['title']); ?></h3>

    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>

    <?php if(isset($book['category']) && $book['category'] != ""){ ?>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
    <?php } ?>

    <a href="books.php">
        <button>View Book</button>
    </a>

    <a href="remove_favorite.php?book_id=<?php echo $book['id']; ?>">
        <button class="remove-btn">Remove Favorite</button>
    </a>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No favorite books yet 😢</p>

<?php } ?>

<h2 class="profile-heading">🕒 Recently Viewed Books</h2>

<?php if(mysqli_num_rows($recentlyViewed) > 0){ ?>

<form action="clear_history.php" method="POST" onsubmit="return confirm('Are you sure you want to clear ALL your reading history? This action cannot be undone.');">
    <button type="submit" name="clear_history" class="clear-history-btn">
        🗑️ Clear All History
    </button>
</form>

<?php while($book = mysqli_fetch_assoc($recentlyViewed)){ ?>

<div class="profile-book-card">

    <h3><?php echo htmlspecialchars($book['title']); ?></h3>

    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>

    <a href="read_book.php?id=<?php echo $book['id']; ?>">
        <button>Continue Reading</button>
    </a>

    <a href="remove_history.php?book_id=<?php echo $book['id']; ?>">
        <button class="remove-btn">Remove History</button>
    </a>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No recently viewed books yet.</p>

<?php } ?>

<h2 class="profile-heading">📖 Reading Progress</h2>

<?php if(mysqli_num_rows($readingProgress) > 0){ ?>

<?php while($progress = mysqli_fetch_assoc($readingProgress)){ ?>

<div class="profile-book-card">

    <h3><?php echo htmlspecialchars($progress['title']); ?></h3>

    <p><strong>Progress:</strong> <?php echo (int)$progress['progress']; ?>%</p>

    <a href="read_book.php?id=<?php echo $progress['book_id']; ?>">
        <button>Continue Book</button>
    </a>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No reading progress yet.</p>

<?php } ?>

<h2 class="profile-heading">🔖 My Bookmarks</h2>

<?php if(mysqli_num_rows($bookmarks) > 0){ ?>

<?php while($bm = mysqli_fetch_assoc($bookmarks)){ ?>

<div class="profile-book-card">

    <h3><?php echo htmlspecialchars($bm['title']); ?></h3>

    <p><?php echo htmlspecialchars($bm['note']); ?></p>

    <a href="read_book.php?id=<?php echo $bm['book_id']; ?>">
        <button>Open Book</button>
    </a>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No bookmarks yet.</p>

<?php } ?>

<h2 class="profile-heading">📚 My Book Requests</h2>

<?php if(mysqli_num_rows($myRequests) > 0){ ?>

<?php while($req = mysqli_fetch_assoc($myRequests)){ ?>

<div class="profile-book-card">

    <h3><?php echo htmlspecialchars($req['book_name']); ?></h3>

    <p><strong>Category:</strong> <?php echo htmlspecialchars($req['category']); ?></p>

    <p><strong>Message:</strong> <?php echo htmlspecialchars($req['message']); ?></p>

    <p>
        <strong>Status:</strong>
        <span style="color:green;font-weight:bold;">
            <?php echo htmlspecialchars($req['status']); ?>
        </span>
    </p>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No book requests yet.</p>

<?php } ?>

<h2 class="profile-heading">📩 My Messages & Admin Replies</h2>

<?php if(mysqli_num_rows($myMessages) > 0){ ?>

<?php while($msg = mysqli_fetch_assoc($myMessages)){ ?>

<div class="profile-book-card">

    <p><strong>Your Message:</strong></p>

    <p><?php echo htmlspecialchars($msg['message']); ?></p>

    <p><strong>Admin Reply:</strong></p>

    <?php if(isset($msg['reply']) && $msg['reply'] != ""){ ?>

        <p><?php echo htmlspecialchars($msg['reply']); ?></p>

    <?php } else { ?>

        <p>No reply yet.</p>

    <?php } ?>

</div>

<?php } ?>

<?php } else { ?>

<p class="empty-text">No messages sent yet.</p>

<?php } ?>

</div>

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