<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// PHPMailer - EXACT SAME AS FORGOT PASSWORD
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ========== SMTP SETTINGS - EXACT COPY FROM FORGOT PASSWORD ==========
$smtp_host = 'smtp.gmail.com';
$smtp_email = 'universitylibrary172@gmail.com';
$smtp_password = getenv('LIBRARY_SMTP_PASSWORD') ?: '';

// All destructive and role-changing actions require POST and a CSRF token.
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
$admin_csrf = $_SESSION['admin_csrf'];

// Validate every form submitted to this dashboard.
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !hash_equals($admin_csrf, (string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('Security check failed. Refresh the page and try again.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    if (!hash_equals($admin_csrf, (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Security check failed. Refresh and try again.');
    }
    $action = (string)$_POST['admin_action'];
    $id = filter_var($_POST['record_id'] ?? null, FILTER_VALIDATE_INT);
    $queries = [
        'delete_book' => 'DELETE FROM books WHERE id = ?',
        'delete_category' => 'DELETE FROM categories WHERE id = ?',
        'delete_message' => 'DELETE FROM messages WHERE id = ?',
        'delete_user' => 'DELETE FROM users WHERE id = ? AND id <> ?',
        'make_admin' => 'UPDATE users SET is_admin = 1 WHERE id = ?',
        'remove_admin' => 'UPDATE users SET is_admin = 0 WHERE id = ? AND id <> ?'
    ];
    if (!$id || !isset($queries[$action])) {
        http_response_code(400);
        exit('Invalid action.');
    }
    $stmt = mysqli_prepare($conn, $queries[$action]);
    if (!$stmt) {
        http_response_code(500);
        exit('Could not process this action.');
    }
    if ($action === 'delete_user' || $action === 'remove_admin') {
        $currentAdminId = (int)($_SESSION['admin_id'] ?? 0);
        mysqli_stmt_bind_param($stmt, 'ii', $id, $currentAdminId);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $id);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $redirect = [
        'delete_book'=>'all_books','delete_category'=>'categories','delete_message'=>'messages',
        'delete_user'=>'all_users','make_admin'=>'all_users','remove_admin'=>'all_users'
    ][$action];
    header('Location: ?page=' . $redirect . '&updated=1');
    exit();
}

// Add a book only with validated, safe link schemes.
if (isset($_POST['add_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $cover_image_url = trim($_POST['cover_image_url'] ?? '');
    $read_link = trim($_POST['read_link'] ?? '');
    $download_epub_link = trim($_POST['download_epub_link'] ?? '');

    $safe_link = function ($value) {
        if ($value === '') return true;
        if (preg_match('/^(uploads\\/[A-Za-z0-9_\\/.-]+)$/', $value)) return strpos($value, '..') === false;
        $parts = parse_url($value);
        return is_array($parts) && isset($parts['scheme'], $parts['host'])
            && strtolower($parts['scheme']) === 'https'
            && !isset($parts['user']) && !isset($parts['pass']);
    };

    if ($title === '' || $author === '' || strlen($title) > 255 || strlen($author) > 255 ||
        strlen($description) > 10000 || strlen($category) > 120 ||
        !$safe_link($cover_image_url) || !$safe_link($read_link) || !$safe_link($download_epub_link)) {
        $admin_error = 'Enter valid book details and use HTTPS or a safe uploads/ path for links.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, description, category, cover_image_url, read_link, download_epub_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssssss', $title, $author, $description, $category, $cover_image_url, $read_link, $download_epub_link);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) { header('Location: ?page=all_books&added=1'); exit(); }
        }
        $admin_error = 'Book could not be added. Please check the details.';
    }
}

// Add category with prepared SQL.
if (isset($_POST['add_category'])) {
    $category = trim($_POST['category'] ?? '');
    if ($category !== '') {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $category);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: ?page=categories&added=1');
    exit();
}

// Update book details.
if (isset($_POST['update_book'])) {
    $id = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $cover_image_url = trim($_POST['cover_image_url'] ?? '');
    $read_link = trim($_POST['read_link'] ?? '');
    $download_epub_link = trim($_POST['download_epub_link'] ?? '');
    if ($id && $title !== '' && $author !== '') {
        $stmt = mysqli_prepare($conn, "UPDATE books SET title=?, author=?, description=?, category=?, cover_image_url=?, read_link=?, download_epub_link=? WHERE id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $author, $description, $category, $cover_image_url, $read_link, $download_epub_link, $id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) { header('Location: ?page=all_books&updated=1'); exit(); }
        }
    }
    $admin_error = 'Book could not be updated. Check the title and author.';
}

// Handle reply message
// Handle reply message
if(isset($_POST['reply_submit'])){
    $message_id = $_POST['message_id'];
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    mysqli_query($conn, "UPDATE messages SET reply='$reply' WHERE id='$message_id'");
    
    $msg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM messages WHERE id='$message_id'"));
    if($msg){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = 'tls';
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
    
    mysqli_query($conn, "INSERT INTO notifications (title, message, created_at) VALUES ('$title', '$message_body', NOW())");
    
    $users = mysqli_query($conn, "SELECT email, fullname FROM users WHERE email != ''");
    $total_users = mysqli_num_rows($users);
    $sent_count = 0;
    
    while($user = mysqli_fetch_assoc($users)){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = 'tls';
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
                    <small style='color: #666;'>📚 Book's Library Team</small>
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

$books = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC");
$messages = mysqli_query($conn, "SELECT * FROM messages ORDER BY id DESC");
$all_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$notifications = mysqli_query($conn, "SELECT * FROM notifications ORDER BY id DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$total_books = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM books"));
$total_reviews = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM reviews"));
$total_categories = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM categories"));

$total_downloads_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(download_count) AS total FROM books"));
$total_downloads = $total_downloads_data['total'] ?? 0;

$most_downloaded = mysqli_query($conn, "SELECT title, download_count FROM books ORDER BY download_count DESC LIMIT 5");
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
    <link rel="stylesheet" href="style.css?v=20261120">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; }
        .side-menu {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background: linear-gradient(180deg, #0b1f3a 0%, #1a3a5c 100%);
            color: white; z-index: 100; overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .side-menu .logo { padding: 25px 20px; font-size: 22px; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .side-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 20px; color: white; text-decoration: none; border-left: 3px solid transparent; }
        .side-menu a:hover { background: rgba(255,255,255,0.1); border-left-color: #ffc72c; }
        .side-menu a.active { background: rgba(255,255,255,0.15); border-left-color: #ffc72c; }
        .main-content { margin-left: 260px; padding: 20px; }
        .top-bar { background: white; padding: 15px 25px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .top-bar h2 { color: #0b1f3a; }
        .dark-btn { background: #0b1f3a; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .stats-container { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; width: 220px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card h2 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card h1 { color: #0b1f3a; font-size: 32px; }
        .table-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; overflow-x: auto; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-section h2 { margin-bottom: 15px; color: #0b1f3a; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #0b1f3a; color: white; }
        button, .btn { background: #0b1f3a; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        button.red, .btn-red { background: #dc3545; }
        button.green, .btn-green { background: #28a745; }
        input, textarea, select { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .dark-mode { background: #1a1a2e; color: white; }
        .dark-mode .stat-card, .dark-mode .table-section, .dark-mode .top-bar { background: #16213e; color: white; }
        .dark-mode .stat-card h2, .dark-mode .stat-card h1 { color: white; }
        .hamburger-mobile { display: none; }
        @media (max-width: 768px) {
            .side-menu { left: -260px; transition: 0.3s; }
            .side-menu.open { left: 0; }
            .main-content { margin-left: 0; }
            .hamburger-mobile { display: block; position: fixed; top: 15px; left: 15px; z-index: 101; background: #0b1f3a; padding: 10px; border-radius: 5px; cursor: pointer; color: white; font-size: 24px; }
        }
        .badge-admin { background: #ffc72c; color: #0b1f3a; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }

        body { background:#f3f7f2; color:#23352b; }
        .side-menu { background:linear-gradient(180deg,#203f31,#315b45); }
        .side-menu a { margin:3px 10px; border-radius:10px; border-left:0; }
        .side-menu a.active,.side-menu a:hover { background:rgba(255,255,255,.15); border-left:0; }
        .main-content { padding:26px; }
        .top-bar,.stat-card,.table-section { border:1px solid rgba(55,91,67,.10); border-radius:18px; box-shadow:0 10px 30px rgba(35,60,43,.07); }
        .top-bar { position:sticky; top:12px; z-index:20; backdrop-filter:blur(14px); }
        .top-bar h2,.table-section h2 { color:#294b37; }
        .stats-container { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:16px; }
        .stat-card { width:auto; min-width:0; background:linear-gradient(145deg,#fff,#f5faf4); }
        .stat-card h1 { color:#315b45; font-size:clamp(26px,3vw,36px); }
        .table-section { padding:22px; }
        .table-section table { border-collapse:separate; border-spacing:0; min-width:650px; border-radius:12px; overflow:hidden; }
        .table-section th { background:#315b45; color:#fff; }
        .table-section td,.table-section th { border:0; border-bottom:1px solid #e7eee7; padding:12px 14px; }
        .table-section tr:hover td { background:#f7fbf6; }
        button,.btn { border-radius:10px; padding:9px 14px; font-weight:600; background:#315b45; }
        button.green,.btn-green { background:#4d8962; } button.red,.btn-red { background:#c44f50; }
        input,textarea,select { border:1px solid #dce7dc; border-radius:10px; padding:12px 14px; max-width:100%; }
        .admin-table-search { width:min(100%,440px); margin:0 0 16px; background:#fbfdfb; }
        .admin-book-form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .admin-book-form label { display:flex; flex-direction:column; gap:6px; color:#49624f; font-weight:600; }
        .admin-book-form label:nth-child(4),.admin-book-form button { grid-column:1/-1; }
        .admin-book-form input,.admin-book-form textarea { width:100%; margin:0; }
        .admin-alert { background:#fff1e9; border:1px solid #f2c8ae; color:#8d4929; border-radius:12px; padding:12px 16px; margin-bottom:16px; }
        .action-buttons form { display:inline-flex; margin:0; }
        .dark-mode { background:#14231b; color:#e8f0e8; }
        .dark-mode .top-bar,.dark-mode .stat-card,.dark-mode .table-section { background:#20352a; border-color:#345440; color:#e8f0e8; }
        .dark-mode .top-bar h2,.dark-mode .table-section h2,.dark-mode .stat-card h1,.dark-mode .stat-card h2,.dark-mode .table-section td { color:#e8f0e8; }
        .dark-mode .table-section td { border-color:#345440; } .dark-mode .table-section tr:hover td { background:#294535; }
        .dark-mode input,.dark-mode textarea,.dark-mode select { background:#172a20; color:#f3f7f3; border-color:#45634e; }
        @media(max-width:768px) {
            .main-content { padding:76px 12px 18px; }
            .top-bar { padding:14px; } .top-bar h2 { margin-left:32px; font-size:18px; }
            .table-section { padding:14px; } .stats-container { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
            .stat-card { padding:15px; } .admin-book-form { grid-template-columns:1fr; }
            .admin-book-form label:nth-child(4),.admin-book-form button { grid-column:auto; }
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
    <a href="?page=add_book" class="<?php echo $page == 'add_book' ? 'active' : ''; ?>">➕ Add Book</a>
    <a href="?page=categories" class="<?php echo $page == 'categories' ? 'active' : ''; ?>">📂 Categories</a>
    <a href="?page=all_users" class="<?php echo $page == 'all_users' ? 'active' : ''; ?>">👥 All Users</a>
    <a href="?page=messages" class="<?php echo $page == 'messages' ? 'active' : ''; ?>">💬 Messages</a>
    <a href="?page=notifications" class="<?php echo $page == 'notifications' ? 'active' : ''; ?>">🔔 Notifications</a>
    <a href="?page=auto_import" class="<?php echo $page == 'auto_import' ? 'active' : ''; ?>">📥 Auto Import</a>
    <a href="?page=book_requests" class="<?php echo $page == 'book_requests' ? 'active' : ''; ?>">📝 Book Requests</a>
    <a href="?page=reviews" class="<?php echo $page == 'reviews' ? 'active' : ''; ?>">⭐ Reviews</a>
    <a href="admin_analytics.php">📊 Analytics</a>
    <a href="admin_settings.php">⚙️ Settings</a>
<a href="admin_backup.php">💾 Backup</a>
<a href="performance.php">🚀 Performance</a>
    <a href="admin_logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <?php if (!empty($admin_error)): ?><div class="admin-alert"><?php echo htmlspecialchars($admin_error); ?></div><?php endif; ?>
    <div class="top-bar">
        <h2><?php echo ucfirst(str_replace('_', ' ', $page)); ?></h2>
        <button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark Mode</button>
    </div>

    <!-- ========== DASHBOARD PAGE ========== -->
    <?php if($page == 'dashboard'): ?>
    <div class="stats-container">
        <div class="stat-card"><h2>Total Users</h2><h1><?php echo $total_users; ?></h1></div>
        <div class="stat-card"><h2>Total Books</h2><h1><?php echo $total_books; ?></h1></div>
        <div class="stat-card"><h2>Total Categories</h2><h1><?php echo $total_categories; ?></h1></div>
        <div class="stat-card"><h2>Total Reviews</h2><h1><?php echo $total_reviews; ?></h1></div>
        <div class="stat-card"><h2>Total Downloads</h2><h1><?php echo $total_downloads; ?></h1></div>
    </div>

    <div class="table-section">
        <h2>Most Downloaded Books</h2>
        <table>
            <tr><th>Book Title</th><th>Downloads</th></tr>
            <?php while($row = mysqli_fetch_assoc($most_downloaded)){ ?>
            <tr><td><?php echo htmlspecialchars($row['title']); ?></td><td><?php echo $row['download_count']; ?></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="table-section">
        <h2>Most Favorited Books</h2>
        <table>
            <tr><th>Book Title</th><th>Favorites</th></tr>
            <?php while($row = mysqli_fetch_assoc($most_favorites)){ ?>
            <tr><td><?php echo htmlspecialchars($row['title']); ?></td><td><?php echo $row['total']; ?></td></tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== ADD BOOK PAGE ========== -->
    <?php if($page == 'add_book'): ?>
    <div class="table-section">
        <h2>➕ Add New Book</h2>
        <form method="POST" action=""><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" name="title" placeholder="Book Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <select name="category">
                <option value="">Select Category</option>
                <?php 
                $cats = mysqli_query($conn, "SELECT * FROM categories");
                while($cat = mysqli_fetch_assoc($cats)){ ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php } ?>
            </select>
            <textarea name="description" placeholder="Description" rows="4"></textarea>
            <input type="text" name="cover_image_url" placeholder="Cover Image URL">
            <input type="text" name="read_link" placeholder="Read Link">
            <input type="text" name="download_epub_link" placeholder="Download EPUB Link">
            <button type="submit" name="add_book" class="green">Add Book</button>
        </form>

        <hr style="margin:28px 0;border:0;border-top:1px solid #dce7dc;">
        <h2>📄 Upload PDF Book</h2>
        <p style="margin:8px 0 16px;color:#667;">Upload a PDF and cover image. The PDF will be available in the online reader and download page.</p>
        <form method="POST" action="upload_book.php" enctype="multipart/form-data" class="admin-book-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <label>Book Title<input type="text" name="title" maxlength="255" required></label>
            <label>Author<input type="text" name="author" maxlength="255" required></label>
            <label>Description<textarea name="description" rows="4" maxlength="10000"></textarea></label>
            <label>PDF file<input type="file" name="pdf" accept="application/pdf,.pdf" required></label>
            <label>Cover image (JPG, PNG, WEBP)<input type="file" name="cover" accept="image/jpeg,image/png,image/webp" required></label>
            <button type="submit" class="green">Upload PDF Book</button>
        </form>
        <p style="margin-top:12px;color:#667;font-size:13px;">Limits: PDF up to 20 MB; cover up to 5 MB.</p>
    </div>
    <?php endif; ?>


    <?php if($page == 'edit_book'):
        $editId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $editBook = null;
        if ($editId) {
            $editStmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id=?");
            if ($editStmt) {
                mysqli_stmt_bind_param($editStmt, 'i', $editId);
                mysqli_stmt_execute($editStmt);
                $editResult = mysqli_stmt_get_result($editStmt);
                $editBook = mysqli_fetch_assoc($editResult);
                mysqli_stmt_close($editStmt);
            }
        }
    ?>
    <div class="table-section">
        <h2>✏️ Edit Book</h2>
        <?php if (!$editBook): ?>
            <p>Book not found. <a href="?page=all_books">Return to books</a>.</p>
        <?php else: ?>
        <form method="POST" class="admin-book-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="book_id" value="<?php echo (int)$editBook['id']; ?>">
            <label>Title<input name="title" required value="<?php echo htmlspecialchars($editBook['title']); ?>"></label>
            <label>Author<input name="author" required value="<?php echo htmlspecialchars($editBook['author']); ?>"></label>
            <label>Category<input name="category" value="<?php echo htmlspecialchars($editBook['category'] ?? ''); ?>"></label>
            <label>Description<textarea name="description" rows="4"><?php echo htmlspecialchars($editBook['description'] ?? ''); ?></textarea></label>
            <label>Cover image URL<input type="url" name="cover_image_url" value="<?php echo htmlspecialchars($editBook['cover_image_url'] ?? ''); ?>"></label>
            <label>Read link<input type="url" name="read_link" value="<?php echo htmlspecialchars($editBook['read_link'] ?? ''); ?>"></label>
            <label>Download EPUB link<input type="url" name="download_epub_link" value="<?php echo htmlspecialchars($editBook['download_epub_link'] ?? ''); ?>"></label>
            <button type="submit" name="update_book" class="green">Save Changes</button>
            <a class="btn" href="?page=all_books">Cancel</a>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ========== CATEGORIES PAGE ========== -->
    <?php if($page == 'categories'): ?>
    <div class="table-section">
        <h2>📂 Manage Categories</h2>
        <form method="POST" action="" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" name="category" placeholder="Category Name" required style="flex:1;min-width:200px;">
            <button type="submit" name="add_category" class="green">Add Category</button>
        </form>
        <table>
            <tr><th>ID</th><th>Category Name</th><th>Action</th></tr>
            <?php while($cat = mysqli_fetch_assoc($categories)){ ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><form method="POST" onsubmit="return confirm('Delete this category?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="delete_category"><input type="hidden" name="record_id" value="<?php echo (int)$cat['id']; ?>"><button class="red" type="submit">Delete</button></form></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== WEBSITE PAGE ========== -->
    <?php if($page == 'website'): ?>
    <div class="table-section">
        <h2>🌐 Website</h2>
        <p>Website is live at: <a href="index.php" target="_blank">index.php</a></p>
        <button onclick="location.href='index.php'">Go to Website</button>
    </div>
    <?php endif; ?>

    <!-- ========== ALL BOOKS PAGE ========== -->
    <?php if($page == 'all_books'): ?>
    <div class="table-section">
        <h2>📚 All Books (<?php echo mysqli_num_rows($books); ?>)</h2>
        <input type="search" class="admin-table-search" data-table="booksTable" placeholder="Search books by title, author or category...">
        <table id="booksTable">
            <tr><th>ID</th><th>Cover</th><th>Title</th><th>Author</th><th>Category</th><th>Downloads</th><th>Action</th></tr>
            <?php while($row = mysqli_fetch_assoc($books)){ ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>" width="50" style="border-radius:5px;"></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></td>
                <td><?php echo $row['download_count'] ?? 0; ?></td>
                <td class="action-buttons"><a class="btn" href="?page=edit_book&id=<?php echo (int)$row['id']; ?>">Edit</a><form method="POST" onsubmit="return confirm('Delete this book permanently?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="delete_book"><input type="hidden" name="record_id" value="<?php echo (int)$row['id']; ?>"><button class="red" type="submit">Delete</button></form></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== ALL USERS PAGE ========== -->
    <?php if($page == 'all_users'): ?>
    <div class="table-section">
        <h2>👥 All Users (<?php echo mysqli_num_rows($all_users); ?>)</h2>
        <input type="search" class="admin-table-search" data-table="usersTable" placeholder="Search users by name or email...">
        <table id="usersTable">
            <tr><th>ID</th><th>Profile</th><th>Full Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
            <?php while($user = mysqli_fetch_assoc($all_users)){ ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['profile_image'] ? '<img src="'.$user['profile_image'].'" width="40" style="border-radius:50%;">' : '👤'; ?></td>
                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <?php if(isset($user['is_admin']) && $user['is_admin'] == 1){ ?>
                        <span class="badge-admin">👑 Admin</span>
                    <?php } else { ?>
                        User
                    <?php } ?>
                </td>
                <td class="action-buttons">
                    <?php if(isset($user['is_admin']) && $user['is_admin'] == 1){ ?>
                        <form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="remove_admin"><input type="hidden" name="record_id" value="<?php echo (int)$user['id']; ?>"><button type="submit" style="background:#ffc72c;color:#0b1f3a;">Remove Admin</button></form>
                    <?php } else { ?>
                        <form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="make_admin"><input type="hidden" name="record_id" value="<?php echo (int)$user['id']; ?>"><button class="green" type="submit">Make Admin</button></form>
                    <?php } ?>
                    <form method="POST" onsubmit="return confirm('Delete this user?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="delete_user"><input type="hidden" name="record_id" value="<?php echo (int)$user['id']; ?>"><button class="red" type="submit">Delete</button></form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== MESSAGES PAGE ========== -->
    <?php if($page == 'messages'): ?>
    <div class="table-section">
        <h2>💬 User Messages</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Reply</th><th>Action</th></tr>
            <?php while($row = mysqli_fetch_assoc($messages)){ ?>
            <form method="POST" action=""><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                    <td width="250"><input type="text" name="reply" value="<?php echo htmlspecialchars($row['reply'] ?? ''); ?>" placeholder="Type reply..." style="width:100%"></td>
                    <td class="action-buttons">
                        <button type="submit" name="reply_submit" class="green">Reply</button>
                        <form method="POST" onsubmit="return confirm('Delete this message?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="admin_action" value="delete_message"><input type="hidden" name="record_id" value="<?php echo (int)$row['id']; ?>"><button type="submit" class="red">Delete</button></form>
                    </td>
                </tr>
            </form>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== NOTIFICATIONS PAGE ========== -->
    <?php if($page == 'notifications'): ?>
    <div class="table-section">
        <h2>🔔 Send Notification</h2>
        <p style="color: #28a745; margin-bottom: 15px;">✅ Notification sent via EMAIL + saved to database!</p>
        <form method="POST" action=""><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" name="title" placeholder="Notification Title" required style="width:100%; margin-bottom:10px;">
            <textarea name="message" placeholder="Write notification..." rows="5" required style="width:100%; margin-bottom:10px;"></textarea>
            <button type="submit" name="send_notification" class="green">📢 Send to All Users</button>
        </form>
    </div>
    <div class="table-section">
        <h2>📋 Sent Notifications</h2>
        <table>
            <tr><th>ID</th><th>Title</th><th>Message</th><th>Date</th></tr>
            <?php while($notif = mysqli_fetch_assoc($notifications)){ ?>
            <tr><td><?php echo $notif['id']; ?></td><td><?php echo htmlspecialchars($notif['title']); ?></td><td><?php echo htmlspecialchars($notif['message']); ?></td><td><?php echo $notif['created_at']; ?></td></tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- ========== AUTO IMPORT PAGE ========== -->
    <?php if($page == 'auto_import'): ?>
    <div class="table-section">
        <h2>📥 Auto Import Books</h2>
        <a href="auto_scrape_books.php"><button class="green">🚀 Auto Import Books</button></a>
    </div>
    <?php endif; ?>

    <!-- ========== BOOK REQUESTS PAGE ========== -->
    <?php if($page == 'book_requests'): ?>
    <div class="table-section">
        <h2>📝 Book Requests</h2>
        <table>
            <tr><th>ID</th><th>User</th><th>Email</th><th>Book</th><th>Category</th><th>Status</th><th>Action</th></tr>
            <?php while($req = mysqli_fetch_assoc($book_requests)){ ?>
            <form method="POST" action="update_request_status.php"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                <tr>
                    <td><?php echo $req['id']; ?></td>
                    <td><?php echo htmlspecialchars($req['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($req['email']); ?></td>
                    <td><?php echo htmlspecialchars($req['book_name']); ?></td>
                    <td><?php echo htmlspecialchars($req['category']); ?></td>
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

    <!-- ========== REVIEWS PAGE ========== -->
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
                <td><form method="POST" action="delete_review.php" onsubmit="return confirm('Delete this review?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo (int)$review['id']; ?>">
                    <button type="submit" class="red">Delete</button>
                </form></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
document.querySelectorAll(".admin-table-search").forEach(function(search){
    search.addEventListener("input", function(){
        const table = document.getElementById(search.dataset.table);
        if (!table) return;
        const query = search.value.toLowerCase();
        Array.from(table.querySelectorAll("tr")).slice(1).forEach(function(row){
            row.hidden = !row.textContent.toLowerCase().includes(query);
        });
    });
});
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