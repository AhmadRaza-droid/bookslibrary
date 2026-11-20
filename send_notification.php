<?php
include 'session_timeout.php';
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
if (!$is_admin) { unset($_SESSION['admin'], $_SESSION['admin_id']); header("Location: admin_login.php"); exit(); }
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
if (!hash_equals($_SESSION['admin_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403); exit('Security check failed. Refresh the admin page and try again.');
}

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
if ($title === '' || $message === '' || strlen($title) > 180 || strlen($message) > 5000) { http_response_code(400); exit('Enter a title and message within the allowed length.'); }
$stmt = mysqli_prepare($conn, "INSERT INTO notifications (title, message) VALUES (?, ?)");
if (!$stmt) { http_response_code(500); exit('Unable to send notification.'); }
mysqli_stmt_bind_param($stmt, 'ss', $title, $message);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
if (!$ok) { http_response_code(500); exit('Notification could not be sent.'); }
header('Location: admin_dashboard.php?page=notifications'); exit();
?>