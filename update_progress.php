<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

function progress_response($status, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') progress_response('error', 'Method not allowed', 405);
if (!isset($_SESSION['user_id'])) progress_response('error', 'Please login', 401);

$user_id = (int) $_SESSION['user_id'];
$book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
$progress = filter_input(INPUT_POST, 'progress', FILTER_VALIDATE_INT);

if (!$book_id || $book_id < 1) progress_response('error', 'Invalid book', 400);
if ($progress === false || $progress === null || $progress < 0 || $progress > 100) {
    progress_response('error', 'Progress must be between 0 and 100', 400);
}

$book_stmt = mysqli_prepare($conn, "SELECT id FROM books WHERE id = ? LIMIT 1");
if (!$book_stmt) progress_response('error', 'Unable to save progress', 500);
mysqli_stmt_bind_param($book_stmt, 'i', $book_id);
mysqli_stmt_execute($book_stmt);
$book_result = mysqli_stmt_get_result($book_stmt);
$book_exists = $book_result && mysqli_num_rows($book_result) === 1;
mysqli_stmt_close($book_stmt);
if (!$book_exists) progress_response('error', 'Book not found', 404);

$stmt = mysqli_prepare($conn, "INSERT INTO reading_progress (user_id, book_id, progress) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE progress = VALUES(progress)");
if (!$stmt) progress_response('error', 'Unable to save progress', 500);
mysqli_stmt_bind_param($stmt, 'iii', $user_id, $book_id, $progress);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    progress_response('error', 'Unable to save progress', 500);
}
mysqli_stmt_close($stmt);

$history_stmt = mysqli_prepare($conn, "INSERT INTO reading_history (user_id, book_id, action) VALUES (?, ?, 'progress_updated')");
if ($history_stmt) {
    mysqli_stmt_bind_param($history_stmt, 'ii', $user_id, $book_id);
    mysqli_stmt_execute($history_stmt);
    mysqli_stmt_close($history_stmt);
}
progress_response('success', 'Progress saved');
?>