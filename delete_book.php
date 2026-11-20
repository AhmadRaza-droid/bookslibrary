<?php
include 'config.php';
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit(); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid book ID.'); }
$stmt = $conn->prepare('DELETE FROM books WHERE id = ?');
$stmt->bind_param('i',$id);
if ($stmt->execute()) { header('Location: admin_dashboard.php?page=all_books'); exit(); }
error_log('Book deletion failed: '.$stmt->error);
http_response_code(500); echo 'Could not delete this book.';
?>