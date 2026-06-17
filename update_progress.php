<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status' => 'error', 'message' => 'Please login']);
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$progress = isset($_POST['progress']) ? (int)$_POST['progress'] : 0;

if($book_id == 0){
    echo json_encode(['status' => 'error', 'message' => 'Invalid book']);
    exit();
}

// Update or insert progress
$query = "INSERT INTO reading_progress (user_id, book_id, progress) 
          VALUES ('$user_id', '$book_id', '$progress')
          ON DUPLICATE KEY UPDATE progress = '$progress'";
mysqli_query($conn, $query);

// Save to reading history
mysqli_query($conn, "INSERT INTO reading_history (user_id, book_id, action) 
                     VALUES ('$user_id', '$book_id', 'progress_updated')");

echo json_encode(['status' => 'success', 'message' => 'Progress saved']);
?>