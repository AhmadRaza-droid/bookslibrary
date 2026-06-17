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

// ========== GET DOWNLOAD HISTORY ==========
$downloads = mysqli_query($conn,
"SELECT * FROM downloads 
 WHERE user_id='$user_id' 
 ORDER BY download_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css?v=11000">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            overflow-x: hidden;
        }
        
        .form-section {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-height: 85vh;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .profile-box::-webkit-scrollbar {
            width: 8px;
        }
        
        .profile-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .profile-box::-webkit-scrollbar-thumb {
            background: #0b1f3a;
            border-radius: 10px;
        }
        
        .clear-all-btn {
            background: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            width: 100%;
        }
        
        .clear-all-btn:hover {
            background: #c82333;
            transform: scale(1.02);
        }
        
        .warning-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: -15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .profile-heading {
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0b1f3a;
            color: #0b1f3a;
        }
        
        .profile-book-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #0b1f3a;
        }
        
        .empty-text {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        button, .btn {
            background: #0b1f3a;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .remove-btn {
            background: #dc3545;
        }
        
        .dark-mode {
            background: #1a1a2e;
        }
        
        .dark-mode .profile-box {
            background: #16213e;
            color: white;
        }
        
        .dark-mode .profile-book-card {
            background: #1a1a3a;
        }
        
        .dark-mode .profile-heading {
            border-bottom-color: #ffc72c;
        }
        
        .download-badge {
            background: #28a745;
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .profile-box {
                max-height: 85vh;
            }
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
    <button type="submit" name="upload_image">Upload Profile Picture</button>
</form>

<p><b>Name:</b> <?php echo htmlspecialchars($user['fullname']); ?></p>
<p><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></p>
<p><b>Password:</b> ********</p>

<br>
<a href="change_password.php"><button>Change Password</button></a>
<br><br>
<a href="logout.php"><button>Logout</button></a>

<!-- ========== CLEAR ALL ACTIVITY BUTTON - NOTIFICATIONS KE BILKUL UPAR ========== -->
<form action="clear_all_history.php" method="POST" onsubmit="return confirm('⚠️ WARNING: This will delete ALL your:\n\n• Notifications\n• Reading History\n• Reading Progress\n• Bookmarks\n• Favorite Books\n• Book Requests\n• Messages\n\nThis action CANNOT be undone!\n\nAre you sure you want to continue?');">
    <button type="submit" class="clear-all-btn">
        🗑️ Clear All My Activity
    </button>
</form>
<p class="warning-text">⚠️ This will delete notifications, history, progress, bookmarks, favorites, requests & messages</p>
<!-- ============================================================================= -->

<!-- 🔔 NOTIFICATIONS SECTION -->
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

<!-- 📚 Request a Book -->
<h2 class="profile-heading">📚 Request a Book</h2>

<form action="request_book.php" method="POST">
    <input type="text" name="book_name" placeholder="Enter book name" required>
    <input type="text" name="category" placeholder="Category e.g. Programming, Science">
    <textarea name="message" placeholder="Write details..."></textarea>
    <button type="submit" name="request_book">Send Request</button>
</form>

<!-- ❤️ Favorite Books -->
<h2 class="profile-heading">❤️ My Favorite Books</h2>

<?php if(mysqli_num_rows($favorites) > 0){ ?>
    <?php while($book = mysqli_fetch_assoc($favorites)){ ?>
        <div class="profile-book-card">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <a href="books.php"><button>View Book</button></a>
            <a href="remove_favorite.php?book_id=<?php echo $book['id']; ?>"><button class="remove-btn">Remove Favorite</button></a>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty-text">No favorite books yet 😢</p>
<?php } ?>

<!-- 🕒 Recently Viewed -->
<h2 class="profile-heading">🕒 Recently Viewed Books</h2>

<?php if(mysqli_num_rows($recentlyViewed) > 0){ ?>
    <?php while($book = mysqli_fetch_assoc($recentlyViewed)){ ?>
        <div class="profile-book-card">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <a href="read_book.php?id=<?php echo $book['id']; ?>"><button>Continue Reading</button></a>
            <a href="remove_history.php?book_id=<?php echo $book['id']; ?>"><button class="remove-btn">Remove History</button></a>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty-text">No recently viewed books yet.</p>
<?php } ?>

<!-- 📖 Reading Progress -->
<h2 class="profile-heading">📖 Reading Progress</h2>

<?php if(mysqli_num_rows($readingProgress) > 0){ ?>
    <?php while($progress = mysqli_fetch_assoc($readingProgress)){ ?>
        <div class="profile-book-card">
            <h3><?php echo htmlspecialchars($progress['title']); ?></h3>
            <p><strong>Progress:</strong> <?php echo (int)$progress['progress']; ?>%</p>
            <div style="width:100%;background:#e0e0e0;height:8px;border-radius:10px;margin-top:8px;">
                <div style="width:<?php echo (int)$progress['progress']; ?>%;background:#28a745;height:8px;border-radius:10px;"></div>
            </div>
            <a href="read_book.php?id=<?php echo $progress['book_id']; ?>"><button>Continue Book</button></a>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty-text">No reading progress yet.</p>
<?php } ?>

<!-- 🔖 Bookmarks -->
<h2 class="profile-heading">🔖 My Bookmarks</h2>

<?php if(mysqli_num_rows($bookmarks) > 0){ ?>
    <?php while($bm = mysqli_fetch_assoc($bookmarks)){ ?>
        <div class="profile-book-card">
            <h3><?php echo htmlspecialchars($bm['title']); ?></h3>
            <p><?php echo htmlspecialchars($bm['note']); ?></p>
            <a href="read_book.php?id=<?php echo $bm['book_id']; ?>"><button>Open Book</button></a>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty-text">No bookmarks yet.</p>
<?php } ?>

<!-- 📥 My Downloads -->
<h2 class="profile-heading">📥 My Downloads</h2>

<?php if(mysqli_num_rows($downloads) > 0){ 
    $download_count = mysqli_num_rows($downloads);
?>
    <p style="margin-bottom:15px;color:#28a745;font-weight:bold;">📚 You have downloaded <?php echo $download_count; ?> books</p>
    
    <?php while($dl = mysqli_fetch_assoc($downloads)){ ?>
        <div class="profile-book-card">
            <h3>📖 <?php echo htmlspecialchars($dl['book_title']); ?></h3>
            <p><strong>Downloaded:</strong> <?php echo date('d M Y, h:i A', strtotime($dl['download_date'])); ?></p>
            <span class="download-badge">✅ Downloaded</span>
            <a href="read_book.php?id=<?php echo $dl['book_id']; ?>">
                <button>📖 Read Now</button>
            </a>
        </div>
    <?php } ?>

<?php } else { ?>
    <p class="empty-text">📚 No downloads yet. Start exploring books!</p>
<?php } ?>

<!-- 📚 Book Requests History -->
<h2 class="profile-heading">📚 My Book Requests</h2>

<?php if(mysqli_num_rows($myRequests) > 0){ ?>
    <?php while($req = mysqli_fetch_assoc($myRequests)){ ?>
        <div class="profile-book-card">
            <h3><?php echo htmlspecialchars($req['book_name']); ?></h3>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($req['category']); ?></p>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($req['message']); ?></p>
            <p><strong>Status:</strong> <span style="color:green;font-weight:bold;"><?php echo htmlspecialchars($req['status']); ?></span></p>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty-text">No book requests yet.</p>
<?php } ?>

<!-- 📩 Messages -->
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