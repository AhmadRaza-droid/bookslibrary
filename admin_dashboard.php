<?php
include 'session_timeout.php';
include 'config.php';

// PHPMailer - APNE PATHS KE ACCORDING CHANGE KARO
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// ========== SMTP SETTINGS - APNI DETAILS DALO ==========
$smtp_host = 'smtp.gmail.com';        // Apna SMTP host
$smtp_email = 'universitylibrary172@gmail.com; // Apna email
$smtp_password = 'vmrntxjtzpvobfyr'; // Apna app password

// Delete message
if(isset($_GET['delete_message'])){
    $id = $_GET['delete_message'];
    mysqli_query($conn, "DELETE FROM messages WHERE id='$id'");
    echo "<script>alert('Message deleted successfully!'); window.location='?page=messages';</script>";
    exit();
}

// Handle reply message
if(isset($_POST['reply_submit'])){
    $message_id = $_POST['message_id'];
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    mysqli_query($conn, "UPDATE messages SET reply='$reply' WHERE id='$message_id'");
    
    // Send reply via email
    $msg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM messages WHERE id='$message_id'"));
    if($msg){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom($smtp_email, 'Admin - Book Library');
            $mail->addAddress($msg['email'], $msg['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Reply to your message';
            $mail->Body = "
                <div style='font-family: Arial; padding: 15px;'>
                    <h3>Reply from Admin</h3>
                    <p><strong>Your message:</strong> {$msg['message']}</p>
                    <p><strong>Admin Reply:</strong> $reply</p>
                    <hr>
                    <small>Book's Library Team</small>
                </div>
            ";
            $mail->send();
        } catch(Exception $e) {}
    }
    
    echo "<script>alert('Reply sent successfully!'); window.location='?page=messages';</script>";
    exit();
}

// Handle send notification
if(isset($_POST['send_notification'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message_body = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Save to database
    mysqli_query($conn, "INSERT INTO notifications (title, message, created_at) VALUES ('$title', '$message_body', NOW())");
    
    // Get all users
    $users = mysqli_query($conn, "SELECT email, fullname FROM users WHERE email != ''");
    $total_users = mysqli_num_rows($users);
    $sent_count = 0;
    
    // Send email to each user
    while($user = mysqli_fetch_assoc($users)){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom($smtp_email, 'Book Library');
            $mail->addAddress($user['email'], $user['fullname']);
            $mail->isHTML(true);
            $mail->Subject = $title;
            $mail->Body = "
                <div style='font-family: Arial; padding: 20px;'>
                    <h2 style='color: #0b1f3a;'>📢 $title</h2>
                    <p>" . nl2br($message_body) . "</p>
                    <br>
                    <hr>
                    <small style='color: #666;'>📚 Book's Library - Management & Technology University</small>
                </div>
            ";
            $mail->AltBody = strip_tags($title . "\n\n" . $message_body);
            $mail->send();
            $sent_count++;
        } catch(Exception $e) {}
    }
    
    echo "<script>alert('✅ Notification sent to $sent_count out of $total_users users via email!\\n\\nSaved to database also.'); window.location='?page=notifications';</script>";
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$books = mysqli_query($conn, "SELECT * FROM books");
$messages = mysqli_query($conn, "SELECT * FROM messages ORDER BY id DESC");
$all_users = mysqli_query($conn, "SELECT * FROM users");
$notifications = mysqli_query($conn, "SELECT * FROM notifications ORDER BY id DESC");

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f9;
        }
        .side-menu {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(180deg, #0b1f3a 0%, #1a3a5c 100%);
            color: white;
            z-index: 100;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .side-menu .logo {
            padding: 25px 20px;
            font-size: 22px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .side-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            transition: 0.2s;
            border-left: 3px solid transparent;
        }
        .side-menu a:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #ffc72c;
        }
        .side-menu a.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #ffc72c;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .top-bar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .top-bar h2 {
            color: #0b1f3a;
        }
        .dark-btn {
            background: #0b1f3a;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 220px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stat-card h2 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card h1 {
            color: #0b1f3a;
            font-size: 32px;
        }
        .table-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .table-section h2 {
            margin-bottom: 15px;
            color: #0b1f3a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #0b1f3a;
            color: white;
        }
        button, .btn {
            background: #0b1f3a;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button.red, .btn-red {
            background: #dc3545;
        }
        button.green, .btn-green {
            background: #28a745;
        }
        input, textarea, select {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .dark-mode {
            background: #1a1a2e;
            color: white;
        }
        .dark-mode .stat-card, 
        .dark-mode .table-section, 
        .dark-mode .top-bar {
            background: #16213e;
            color: white;
        }
        .dark-mode .stat-card h2,
        .dark-mode .stat-card h1 {
            color: white;
        }
        .hamburger-mobile {
            display: none;
        }
        @media (max-width: 768px) {
            .side-menu {
                left: -260px;
                transition: 0.3s;
            }
            .side-menu.open {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .hamburger-mobile {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 101;
                background: #0b1f3a;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                color: white;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="hamburger-mobile" onclick="toggleMobileMenu()">☰</div>

<div class="side-menu" id="sideMenu">
    <div class="logo">📖 Admin Panel</div>
    <a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">🏠 Dashboard</a>
    <a href="?page=website" class="<?php echo $page == 'website' ? 'active' : ''; ?>">🌐 Website</a>
    <a href="?page=all_books" class="<?php echo $page == 'all_books' ? 'active' : ''; ?>">📚 All Books</a>
    <a href="?page=all_users" class="<?php echo $page == 'all_users' ? 'active' : ''; ?>">👥 All Users</a>
    <a href="?page=messages" class="<?php echo $page == 'messages' ? 'active' : ''; ?>">💬 Messages</a>
    <a href="?page=notifications" class="<?php echo $page == 'notifications' ? 'active' : ''; ?>">🔔 Send Notification</a>
    <a href="?page=auto_import" class="<?php echo $page == 'auto_import' ? 'active' : ''; ?>">📥 Auto Import</a>
    <a href="?page=book_requests" class="<?php echo $page == 'book_requests' ? 'active' : ''; ?>">📝 Book Requests</a>
    <a href="?page=reviews" class="<?php echo $page == 'reviews' ? 'active' : ''; ?>">⭐ Reviews</a>
    <a href="admin_logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="top-bar">
        <h2><?php echo ucfirst(str_replace('_', ' ', $page)); ?></h2>
        <button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark Mode</button>
    </div>

    <!-- DASHBOARD PAGE -->
    <?php if($page == 'dashboard'): ?>
    <div class="stats-container">
        <div class="stat-card"><h2>Total Users</h2><h1><?php echo $total_users; ?></h1></div>
        <div class="stat-card"><h2>Total Books</h2><h1><?php echo $total_books; ?></h1></div>
        <div class="stat-card"><h2>Total Reviews</h2><h1><?php echo $total_reviews; ?></h1></div>
        <div class="stat-card"><h2>Total Downloads</h2><h1><?php echo $total_downloads; ?></h1></div>
    </div>

    <div class="table-section">
        <h2>Most Downloaded Books</h2>
        <table>
            <tr><th>Book Title</th><th>Downloads</th></tr>
            <?php while($row = mysqli_fetch_assoc($most_downloaded)){ ?>
            <tr><td><?php echo htmlspecialchars($row['title']); ?></td><td><?php echo $row['downloads']; ?></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="table-section">
        <h2>Most Favorited Books</h2>
        <table>
            <tr><th>Book Title</th><th>Favorites</th></tr>
            <?php while($row = mysqli_fetch_assoc($most_favorites)){ ?>
            <td><?php echo htmlspecialchars($row['title']); ?></td><td><?php echo $row['total']; ?></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="table-section">
        <h2>Add New Book</h2>
        <form action="add_book.php" method="POST">
            <input type="text" name="title" placeholder="Book Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <textarea name="description" placeholder="Description" rows="3"></textarea>
            <input type="text" name="cover_image_url" placeholder="Cover URL">
            <input type="text" name="read_link" placeholder="Read Link">
            <input type="text" name="download_epub_link" placeholder="Download Link">
            <button type="submit">Add Book</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- WEBSITE PAGE -->
    <?php if($page == 'website'): ?>
    <div class="table-section">
        <h2>🌐 Website</h2>
        <p>Website is live at: <a href="index.php" target="_blank">index.php</a></p>
        <button onclick="location.href='index.php'">Go to Website</button>
    </div>
    <?php endif; ?>

    <!-- ALL BOOKS PAGE -->
    <?php if($page == 'all_books'): ?>
    <div class="table-section">
        <h2>📚 All Books</h2>
        <table>
            <tr><th>ID</th><th>Cover</th><th>Title</th><th>Author</th><th>Action</th></tr>
            <?php while($row = mysqli_fetch_assoc($books)){ ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>" width="50"></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><a href="delete_book.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this book?')"><button class="red">Delete</button></a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ALL USERS PAGE -->
    <?php if($page == 'all_users'): ?>
    <div class="table-section">
        <h2>👥 All Users</h2>
        <table>
            <tr><th>ID</th><th>Profile</th><th>Full Name</th><th>Email</th><th>Action</th></tr>
            <?php while($user = mysqli_fetch_assoc($all_users)){ ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['profile_image'] ? '<img src="'.$user['profile_image'].'" width="40" style="border-radius:50%;">' : '👤'; ?></td>
                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Delete this user?')"><button class="red">Delete</button></a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- MESSAGES PAGE -->
    <?php if($page == 'messages'): ?>
    <div class="table-section">
        <h2>💬 User Messages</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Reply</th><th>Action</th></tr>
            <?php while($row = mysqli_fetch_assoc($messages)){ ?>
            <form method="POST" action="">
                <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                    <td width="250"><input type="text" name="reply" value="<?php echo htmlspecialchars($row['reply'] ?? ''); ?>" placeholder="Type reply..." style="width:100%"></td>
                    <td class="action-buttons">
                        <button type="submit" name="reply_submit" class="green">Reply</button>
                        <a href="?page=messages&delete_message=<?php echo $row['id']; ?>" onclick="return confirm('Delete this message?')"><button type="button" class="red">Delete</button></a>
                    </td>
                </tr>
            </form>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- NOTIFICATIONS PAGE -->
    <?php if($page == 'notifications'): ?>
    <div class="table-section">
        <h2>🔔 Send Notification</h2>
        <p style="color: #28a745; margin-bottom: 15px;">✅ Notification will be sent via EMAIL to all users AND saved to database!</p>
        <form method="POST" action="">
            <input type="text" name="title" placeholder="Notification Title" required style="width:100%; margin-bottom:10px;">
            <textarea name="message" placeholder="Write notification message here..." rows="5" required style="width:100%; margin-bottom:10px;"></textarea>
            <button type="submit" name="send_notification" class="green">📢 Send to All Users</button>
        </form>
    </div>

    <div class="table-section">
        <h2>📋 Sent Notifications History</h2>
        <table>
            <tr><th>ID</th><th>Title</th><th>Message</th><th>Sent Date</th></tr>
            <?php while($notif = mysqli_fetch_assoc($notifications)){ ?>
            <tr>
                <td><?php echo $notif['id']; ?></td>
                <td><?php echo htmlspecialchars($notif['title']); ?></td>
                <td><?php echo htmlspecialchars($notif['message']); ?></td>
                <td><?php echo $notif['created_at']; ?></td>
            </tr>
            <?php } ?>
            <?php if(mysqli_num_rows($notifications) == 0){ ?>
            <td><td colspan="4" style="text-align:center;">No notifications sent yet</td></tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- AUTO IMPORT PAGE -->
    <?php if($page == 'auto_import'): ?>
    <div class="table-section">
        <h2>📥 Auto Import Books</h2>
        <p>Click the button below to automatically import books from external source.</p>
        <a href="auto_scrape_books.php"><button class="green">🚀 Auto Import Books</button></a>
    </div>
    <?php endif; ?>

    <!-- BOOK REQUESTS PAGE -->
    <?php if($page == 'book_requests'): ?>
    <div class="table-section">
        <h2>📝 Book Requests from Users</h2>
        <table>
            <tr><th>ID</th><th>User</th><th>Email</th><th>Book Name</th><th>Category</th><th>Message</th><th>Status</th><th>Action</th></tr>
            <?php while($req = mysqli_fetch_assoc($book_requests)){ ?>
            <form method="POST" action="update_request_status.php">
                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                <tr>
                    <td><?php echo $req['id']; ?></td>
                    <td><?php echo htmlspecialchars($req['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($req['email']); ?></td>
                    <td><?php echo htmlspecialchars($req['book_name']); ?></td>
                    <td><?php echo htmlspecialchars($req['category']); ?></td>
                    <td><?php echo htmlspecialchars($req['message']); ?></td>
                    <td>
                        <select name="status">
                            <option <?php echo $req['status']=='Pending'?'selected':''; ?>>Pending</option>
                            <option <?php echo $req['status']=='Approved'?'selected':''; ?>>Approved</option>
                            <option <?php echo $req['status']=='Added'?'selected':''; ?>>Added</option>
                            <option <?php echo $req['status']=='Rejected'?'selected':''; ?>>Rejected</option>
                        </select>
                    </td>
                    <td><button type="submit" name="update_status">Update</button></td>
                </tr>
            </form>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- REVIEWS PAGE -->
    <?php if($page == 'reviews'): ?>
    <div class="table-section">
        <h2>⭐ Book Reviews</h2>
        <table>
            <tr><th>ID</th><th>User</th><th>Book</th><th>Rating</th><th>Review</th><th>Action</th></tr>
            <?php
            $reviews = mysqli_query($conn, "SELECT reviews.*, users.fullname, books.title FROM reviews JOIN users ON reviews.user_id = users.id JOIN books ON reviews.book_id = books.id ORDER BY reviews.id DESC");
            while($review = mysqli_fetch_assoc($reviews)){ ?>
            <tr>
                <td><?php echo $review['id']; ?></td>
                <td><?php echo htmlspecialchars($review['fullname']); ?></td>
                <td><?php echo htmlspecialchars($review['title']); ?></td>
                <td><?php echo $review['rating']; ?> ⭐</td>
                <td><?php echo htmlspecialchars($review['review']); ?></td>
                <td><a href="delete_review.php?id=<?php echo $review['id']; ?>"><button class="red">Delete</button></a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
function toggleMobileMenu() {
    document.getElementById('sideMenu').classList.toggle('open');
}

if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>