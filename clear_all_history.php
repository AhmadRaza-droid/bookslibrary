<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403); exit('Invalid request token. Refresh your profile and try again.');
}
$user_id = (int) $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? '';
$queries = [
    "DELETE FROM recently_viewed WHERE user_id = ?",
    "DELETE FROM reading_progress WHERE user_id = ?",
    "DELETE FROM bookmarks WHERE user_id = ?",
    "DELETE FROM favorites WHERE user_id = ?",
    "DELETE FROM book_requests WHERE user_id = ?"
];
mysqli_begin_transaction($conn);
try {
    foreach ($queries as $sql) {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) throw new Exception('Prepare failed');
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); throw new Exception('Delete failed'); }
        mysqli_stmt_close($stmt);
    }
    if ($user_email !== '') {
        $stmt = mysqli_prepare($conn, "DELETE FROM messages WHERE email = ?");
        if (!$stmt) throw new Exception('Message prepare failed');
        mysqli_stmt_bind_param($stmt, 's', $user_email);
        if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); throw new Exception('Message delete failed'); }
        mysqli_stmt_close($stmt);
    }
    mysqli_commit($conn);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "<script>alert('Your personal activity has been cleared. Site announcements were kept.'); window.location='profile.php';</script>";
} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log('Activity cleanup failed: ' . $e->getMessage());
    http_response_code(500);
    echo 'Unable to clear activity. Please try again.';
}
?>