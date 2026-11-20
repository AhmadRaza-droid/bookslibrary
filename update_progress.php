<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

function progress_response($status, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') progress_response('error', 'Method not allowed', 405);
if (empty($_SESSION['user_id'])) progress_response('error', 'Please login', 401);
if (empty($_SESSION['progress_csrf']) || !hash_equals($_SESSION['progress_csrf'], (string)($_POST['csrf_token'] ?? ''))) progress_response('error', 'Security check failed. Refresh the reader page.', 403);

$user_id = (int)$_SESSION['user_id'];
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

$check_stmt = mysqli_prepare($conn, "SELECT progress FROM reading_progress WHERE user_id = ? AND book_id = ? LIMIT 1");
if (!$check_stmt) progress_response('error', 'Reading progress storage is unavailable', 500);
mysqli_stmt_bind_param($check_stmt, 'ii', $user_id, $book_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$existing = $check_result ? mysqli_fetch_assoc($check_result) : null;
mysqli_stmt_close($check_stmt);

if ($existing) {
    $stmt = mysqli_prepare($conn, "UPDATE reading_progress SET progress = ? WHERE user_id = ? AND book_id = ?");
    if (!$stmt) progress_response('error', 'Unable to update progress', 500);
    mysqli_stmt_bind_param($stmt, 'iii', $progress, $user_id, $book_id);
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO reading_progress (user_id, book_id, progress) VALUES (?, ?, ?)");
    if (!$stmt) progress_response('error', 'Unable to save progress', 500);
    mysqli_stmt_bind_param($stmt, 'iii', $user_id, $book_id, $progress);
}
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
if (!$ok) progress_response('error', 'Unable to save progress', 500);
progress_response('success', 'Progress saved');
?>