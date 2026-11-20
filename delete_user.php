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

$user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$user_id || $user_id < 1 || $user_id === $admin_id) { http_response_code(400); exit('Invalid user ID.'); }
mysqli_begin_transaction($conn);
try {
    foreach (['favorites','recently_viewed','reviews','bookmarks','reading_progress','reading_history','notifications','book_requests'] as $table) {
        $sql = "DELETE FROM `" . $table . "` WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) throw new Exception('Prepare failed for ' . $table);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); throw new Exception('Delete failed for ' . $table); }
        mysqli_stmt_close($stmt);
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND id <> ?");
    if (!$stmt) throw new Exception('User delete prepare failed');
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $admin_id);
    if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); throw new Exception('User delete failed'); }
    mysqli_stmt_close($stmt);
    mysqli_commit($conn);
    header("Location: admin_dashboard.php?page=all_users"); exit();
} catch (Throwable $e) {
    mysqli_rollback($conn); error_log('Admin user deletion failed: '.$e->getMessage());
    http_response_code(500); exit('Unable to delete user and related records.');
}
?>