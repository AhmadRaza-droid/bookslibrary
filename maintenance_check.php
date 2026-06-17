<?php
// maintenance_check.php
include 'config.php';

// Get maintenance mode setting
$result = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key='maintenance_mode'");
$row = mysqli_fetch_assoc($result);
$maintenance_mode = $row['setting_value'] ?? '0';

// Check if user is admin
$is_admin = false;
if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){
    $is_admin = true;
}

// If maintenance mode is ON and user is not admin
if($maintenance_mode == '1' && !$is_admin){
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Maintenance Mode</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .maintenance-box {
                background: white;
                padding: 50px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
            }
            .maintenance-box .icon { font-size: 80px; margin-bottom: 20px; }
            .maintenance-box h1 { color: #0b1f3a; margin-bottom: 10px; }
            .maintenance-box p { color: #666; line-height: 1.8; }
            .maintenance-box .status {
                background: #ffc72c;
                color: #0b1f3a;
                padding: 8px 20px;
                border-radius: 20px;
                display: inline-block;
                font-weight: bold;
                margin-top: 10px;
            }
            .dark-mode .maintenance-box {
                background: #16213e;
                color: white;
            }
            .dark-mode .maintenance-box h1 { color: white; }
            .dark-mode .maintenance-box p { color: #aaa; }
        </style>
    </head>
    <body>
        <div class='maintenance-box'>
            <div class='icon'>🔧</div>
            <h1>Under Maintenance</h1>
            <p>We're currently working on improving our library system.</p>
            <p>Please check back later. We apologize for the inconvenience.</p>
            <div class='status'>🟡 Maintenance Mode Active</div>
        </div>
    </body>
    </html>";
    exit();
}
?>