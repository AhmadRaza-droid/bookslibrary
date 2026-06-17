<?php
// ========== ERROR REPORTING - PRODUCTION MODE ==========
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// ========== DATABASE CONNECTION ==========
$host = "sql103.byetcluster.com";
$user = "if0_42113689";
$password = "yasirijaz712";
$database = "if0_42113689_library_db";

$conn = mysqli_connect($host, $user, $password, $database);

// ========== CHECK CONNECTION ==========
if(!$conn){
    // Log error instead of showing to user
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Sorry, we are experiencing technical difficulties. Please try again later.");
}

// ========== SET CHARACTER SET TO UTF-8 ==========
if(!mysqli_set_charset($conn, "utf8mb4")){
    error_log("Failed to set charset: " . mysqli_error($conn));
}

// ========== SECURITY HEADERS ==========
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer-when-downgrade");

// ========== SESSION START ==========
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// ========== TIMEZONE ==========
date_default_timezone_set('Asia/Karachi');

// ========== SITE URL ==========
$site_url = "https://" . $_SERVER['HTTP_HOST'];
?>