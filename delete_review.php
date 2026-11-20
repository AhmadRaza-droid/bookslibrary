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

$review_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$review_id || $review_id < 1) { http_response_code(400); exit('Invalid review ID.'); }
$stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
if (!$stmt) { http_response_code(500); exit('Unable to delete review.'); }
mysqli_stmt_bind_param($stmt, 'i', $review_id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
if (!$ok) { http_response_code(500); exit('Could not delete review.'); }
header('Location: admin_dashboard.php?page=reviews'); exit();
?>