<?php
// ========== SESSION START ==========
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// ========== SESSION TIMEOUT (30 MINUTES) ==========
$timeout = 1800; // 30 minutes (production)

// For testing, you can use 600 (10 minutes)
// $timeout = 600; // 10 minutes

// ========== CHECK SESSION ACTIVITY ==========
if(isset($_SESSION['LAST_ACTIVITY'])){
    if(time() - $_SESSION['LAST_ACTIVITY'] > $timeout){
        // Clear session
        session_unset();
        session_destroy();
        
        // Redirect to login with timeout message
        header("Location: login.php?timeout=1");
        exit();
    }
}

// ========== UPDATE LAST ACTIVITY ==========
$_SESSION['LAST_ACTIVITY'] = time();

// ========== REGENERATE SESSION ID (Security) ==========
if(!isset($_SESSION['CREATED'])){
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// ========== SESSION REGENERATE EVERY 30 MINUTES ==========
if(isset($_SESSION['CREATED']) && (time() - $_SESSION['CREATED'] > 1800)){
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// ========== SESSION SECURITY CHECK ==========
// Check if user is logged in
if(isset($_SESSION['user_id'])){
    // Check IP address matches
    if(!isset($_SESSION['IP_ADDRESS'])){
        $_SESSION['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
    } else if($_SESSION['IP_ADDRESS'] != $_SERVER['REMOTE_ADDR']){
        // IP changed - possible session hijacking
        session_unset();
        session_destroy();
        header("Location: login.php?security=1");
        exit();
    }
    
    // Check User Agent matches
    if(!isset($_SESSION['USER_AGENT'])){
        $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    } else if($_SESSION['USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']){
        // User Agent changed - possible session hijacking
        session_unset();
        session_destroy();
        header("Location: login.php?security=1");
        exit();
    }
}

// ========== ADMIN SESSION CHECK ==========
if(isset($_SESSION['admin'])){
    // Check IP address matches
    if(!isset($_SESSION['ADMIN_IP'])){
        $_SESSION['ADMIN_IP'] = $_SERVER['REMOTE_ADDR'];
    } else if($_SESSION['ADMIN_IP'] != $_SERVER['REMOTE_ADDR']){
        session_unset();
        session_destroy();
        header("Location: admin_login.php?security=1");
        exit();
    }
}
?>