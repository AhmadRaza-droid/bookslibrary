<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));

// Create Backup
if(isset($_GET['action']) && $_GET['action'] == 'backup'){
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    while($row = mysqli_fetch_row($result)){
        $tables[] = $row[0];
    }
    
    $output = "-- Database Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach($tables as $table){
        $query = mysqli_query($conn, "SELECT * FROM $table");
        $columns = mysqli_fetch_fields($query);
        
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $create = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
        $output .= $create[1] . ";\n\n";
        
        while($row = mysqli_fetch_assoc($query)){
            $values = [];
            foreach($row as $val){
                $values[] = $val === null ? 'NULL' : "'" . mysqli_real_escape_string($conn, $val) . "'";
            }
            $output .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
        $output .= "\n";
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
    echo $output;
    exit();
}

// Restore only server-generated backup files, with an admin CSRF token and strict upload limits.
if (isset($_POST['restore'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['admin_csrf'], $token)) {
        http_response_code(403);
        exit('Security check failed. Refresh the page and try again.');
    }

    $upload = $_FILES['backup_file'] ?? null;
    $valid_upload = $upload && $upload['error'] === UPLOAD_ERR_OK
        && is_uploaded_file($upload['tmp_name'])
        && $upload['size'] > 0 && $upload['size'] <= 10 * 1024 * 1024
        && strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION)) === 'sql';

    if (!$valid_upload) {
        echo "<script>alert('Invalid backup file. Choose a SQL backup smaller than 10 MB.');</script>";
    } else {
        $sql = file_get_contents($upload['tmp_name']);
        if ($sql === false || strpos($sql, "-- Database Backup") !== 0) {
            echo "<script>alert('This file is not a Book Library database backup.');</script>";
        } else {
            $restore_ok = mysqli_multi_query($conn, $sql);
            if ($restore_ok) {
                do {
                    if ($result = mysqli_store_result($conn)) mysqli_free_result($result);
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));
                $restore_ok = mysqli_errno($conn) === 0;
            }
            if ($restore_ok) {
                $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
                echo "<script>alert('Database restore completed. Please review the website.');</script>";
            } else {
                error_log('Database restore failed: ' . mysqli_error($conn));
                echo "<script>alert('Restore failed. Check the backup file and database permissions.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Backup & Restore</title>
    <link rel="stylesheet" href="style.css?v=8000">
    <style>
        .backup-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .backup-box { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; text-align: center; }
        .backup-box h2 { margin-bottom: 15px; color: #0b1f3a; }
        .backup-box p { color: #666; margin-bottom: 20px; }
        .backup-box button { padding: 12px 30px; font-size: 16px; }
        .dark-mode .backup-box { background: #16213e; color: white; }
        .dark-mode .backup-box h2 { color: white; }
        .dark-mode .backup-box p { color: #aaa; }
        .backup-box input[type="file"] { margin: 15px auto; display: block; }
    </style>
</head>
<body>

<nav>
    <div class="logo">💾 Backup & Restore</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_backup.php" class="active">Backup</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>💾 Backup & Restore</h1>
    <p>Create database backup or restore from backup file</p>
</section>

<div class="backup-container">
    <!-- Backup -->
    <div class="backup-box">
        <h2>📤 Create Backup</h2>
        <p>Download a complete SQL backup of your database</p>
        <a href="?action=backup">
            <button class="green">📥 Download Backup</button>
        </a>
    </div>

    <!-- Restore -->
    <div class="backup-box">
        <h2>📥 Restore Backup</h2>
        <p>Upload a SQL file to restore your database</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="file" name="backup_file" accept=".sql" required>
            <button type="submit" name="restore" class="green">🔄 Restore Database</button>
        </form>
        <p style="font-size:12px;color:#dc3545;margin-top:10px;">⚠️ Warning: This will overwrite all existing data!</p>
    </div>
</div>

<script>
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}
function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>