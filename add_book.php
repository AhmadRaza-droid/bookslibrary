<?php
include 'config.php';

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
if ($admin_id < 1) { header("Location: admin_login.php"); exit(); }
$admin_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? AND is_admin = 1 LIMIT 1");
if (!$admin_stmt) { http_response_code(500); exit('Unable to verify admin access.'); }
mysqli_stmt_bind_param($admin_stmt, 'i', $admin_id);
mysqli_stmt_execute($admin_stmt);
$admin_result = mysqli_stmt_get_result($admin_stmt);
$is_admin = $admin_result && mysqli_num_rows($admin_result) === 1;
mysqli_stmt_close($admin_stmt);
if (!$is_admin) { http_response_code(403); exit('Admin access required.'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
if (empty($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403); exit('Security check failed.');
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$description = trim($_POST['description'] ?? '');
$cover_image_url = trim($_POST['cover_image_url'] ?? '');
$read_link = trim($_POST['read_link'] ?? '');
$download_epub_link = trim($_POST['download_epub_link'] ?? '');

$is_safe_link = function ($value) {
    if ($value === '') return true;
    if (preg_match('/^uploads\/[A-Za-z0-9_\/.\-]+$/', $value)) return strpos($value, '..') === false;
    $parts = parse_url($value);
    return is_array($parts) && isset($parts['scheme'], $parts['host']) &&
        strtolower($parts['scheme']) === 'https' && !isset($parts['user']) && !isset($parts['pass']);
};
if ($title === '' || $author === '' || strlen($title) > 255 || strlen($author) > 255 ||
    strlen($description) > 10000 || !$is_safe_link($cover_image_url) ||
    !$is_safe_link($read_link) || !$is_safe_link($download_epub_link)) {
    http_response_code(400); exit('Invalid book details or unsafe resource link.');
}

$stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, description, cover_image_url, read_link, download_epub_link, source) VALUES (?, ?, ?, ?, ?, ?, 'Admin')");
if (!$stmt) { http_response_code(500); exit('Unable to add book.'); }
mysqli_stmt_bind_param($stmt, 'ssssss', $title, $author, $description, $cover_image_url, $read_link, $download_epub_link);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
if (!$ok) { http_response_code(500); exit('Book could not be added.'); }
header("Location: admin_dashboard.php?page=all_books&added=1");
exit();
?>