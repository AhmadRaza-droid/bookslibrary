<?php
// Check if user is admin
$is_admin = false;
if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $check_admin = mysqli_query($conn, "SELECT is_admin FROM users WHERE id='$uid'");
    if($check_admin && mysqli_num_rows($check_admin) > 0){
        $admin_data = mysqli_fetch_assoc($check_admin);
        $is_admin = ($admin_data['is_admin'] == 1);
    }
}
?>

<nav>
    <div class="logo">📖 <?php echo htmlspecialchars($settings['site_name'] ?? 'Library Management System'); ?></div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="profile.php">👤 Profile</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
            
            <?php if($is_admin): ?>
                <li><a href="admin_dashboard.php" style="color:#ffc72c;font-weight:bold;">🛠️ Admin Panel</a></li>
            <?php endif; ?>
            
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
        
        <li><a href="contact.php">Contact</a></li>
        <li><a href="about.php">About</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>