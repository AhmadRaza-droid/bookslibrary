<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'];

$query = "DELETE FROM books WHERE id='$id'";

if(mysqli_query($conn, $query)){
    echo "<script>
        alert('Book Deleted Successfully');
        window.location.href='admin_dashboard.php';
    </script>";
}else{
    echo "Error: " . mysqli_error($conn);
}
?>