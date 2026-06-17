<?php

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$timeout = 600; // 10 minute testing

if(isset($_SESSION['LAST_ACTIVITY'])){

    if(time() - $_SESSION['LAST_ACTIVITY'] > $timeout){

        session_unset();
        session_destroy();

        header("Location: login.php?timeout=1");
        exit();
    }
}

$_SESSION['LAST_ACTIVITY'] = time();

?>