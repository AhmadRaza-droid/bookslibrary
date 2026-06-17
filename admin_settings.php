<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// Update settings
if(isset($_POST['update_settings'])){
    $errors = 0;
    foreach($_POST as $key => $value){
        if($key == 'update_settings') continue;
        $value = mysqli_real_escape_string($conn, $value);
        $query = "UPDATE settings SET setting_value='$value' WHERE setting_key='$key'";
        if(!mysqli_query($conn, $query)){
            $errors++;
        }
    }
    if($errors == 0){
        echo "<script>alert('✅ Settings updated successfully!'); window.location.href='admin_settings.php';</script>";
    } else {
        echo "<script>alert('❌ Some settings failed to update. Please try again.');</script>";
    }
    exit();
}

// Get all settings
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM settings");
if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} else {
    // If no data, insert default
    mysqli_query($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
        ('site_name', 'Book\'s Library'),
        ('site_tagline', 'Read. Learn. Grow.'),
        ('site_logo', ''),
        ('footer_text', '© 2026 Book\'s Library. All Rights Reserved.'),
        ('contact_email', 'admin@bookslibrary.com'),
        ('contact_phone', '+92 300 1234567'),
        ('contact_address', 'Management & Technology University, Lahore'),
        ('maintenance_mode', '0'),
        ('allow_registration', '1')");
    
    // Refresh settings
    $result = mysqli_query($conn, "SELECT * FROM settings");
    while($row = mysqli_fetch_assoc($result)){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Site Settings</title>
    <link rel="stylesheet" href="style.css?v=8000">
    <style>
        .settings-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .settings-form { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .settings-form .form-group { margin-bottom: 20px; }
        .settings-form label { display: block; font-weight: bold; margin-bottom: 8px; color: #0b1f3a; }
        .settings-form input, .settings-form textarea, .settings-form select { 
            width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;
            box-sizing: border-box;
        }
        .settings-form input:focus, .settings-form textarea:focus { outline: none; border-color: #0b1f3a; }
        .btn-green { background: #28a745; color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-size: 16px; width: 100%; }
        .btn-green:hover { background: #218838; }
        .dark-mode .settings-form { background: #16213e; color: white; }
        .dark-mode .settings-form label { color: white; }
        .dark-mode .settings-form input, .dark-mode .settings-form textarea, .dark-mode .settings-form select {
            background: #1a1a3a; color: white; border-color: #333;
        }
        .dark-mode .settings-container h2 { color: white; }
        .dark-mode .settings-form .subtitle { color: #aaa; }
        .dark-mode .btn-green { background: #28a745; }
        .dark-mode .btn-green:hover { background: #218838; }
        .subtitle { color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>

<nav>
    <div class="logo">⚙️ Site Settings</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_settings.php" class="active">Settings</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>⚙️ Site Settings</h1>
    <p>Manage your website configuration</p>
</section>

<div class="settings-container">
    <div class="settings-form">
        <h2 style="margin-bottom:5px;">Site Configuration</h2>
        <p class="subtitle">Update your website settings below</p>
        
        <form method="POST">
            <div class="form-group">
                <label>🏠 Site Name</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Book\'s Library'); ?>">
            </div>
            
            <div class="form-group">
                <label>📝 Site Tagline</label>
                <input type="text" name="site_tagline" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? 'Read. Learn. Grow.'); ?>">
            </div>
            
            <div class="form-group">
                <label>📧 Contact Email</label>
                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'admin@bookslibrary.com'); ?>">
            </div>
            
            <div class="form-group">
                <label>📞 Contact Phone</label>
                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '+92 300 1234567'); ?>">
            </div>
            
            <div class="form-group">
                <label>📍 Address</label>
                <textarea name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address'] ?? 'Management & Technology University, Lahore'); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>📄 Footer Text</label>
                <input type="text" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? '© 2026 Book\'s Library. All Rights Reserved.'); ?>">
            </div>
            
            <div class="form-group">
                <label>🔧 Maintenance Mode</label>
                <select name="maintenance_mode">
                    <option value="0" <?php echo ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>Disabled</option>
                    <option value="1" <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>Enabled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📝 Allow Registration</label>
                <select name="allow_registration">
                    <option value="1" <?php echo ($settings['allow_registration'] ?? '1') == '1' ? 'selected' : ''; ?>>Yes</option>
                    <option value="0" <?php echo ($settings['allow_registration'] ?? '1') == '0' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            
            <button type="submit" name="update_settings" class="btn-green">💾 Save Settings</button>
        </form>
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