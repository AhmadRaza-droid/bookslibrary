<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

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

// Restore Backup
if(isset($_POST['restore'])){
    $file = $_FILES['backup_file']['tmp_name'];
    if(file_exists($file)){
        $sql = file_get_contents($file);
        $queries = explode(";", $sql);
        foreach($queries as $query){
            if(trim($query) != ''){
                mysqli_query($conn, $query);
            }
        }
        echo "<script>alert('✅ Database restored successfully!');</script>";
    } else {
        echo "<script>alert('❌ Error restoring backup!');</script>";
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