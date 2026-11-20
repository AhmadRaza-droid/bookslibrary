<?php
// Verify the signed-in account's admin role with a prepared query.
$is_admin = false;
if (!empty($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $check_admin = mysqli_prepare($conn, "SELECT is_admin FROM users WHERE id = ? LIMIT 1");
    if ($check_admin) {
        mysqli_stmt_bind_param($check_admin, 'i', $uid);
        mysqli_stmt_execute($check_admin);
        $admin_result = mysqli_stmt_get_result($check_admin);
        if ($admin_result && ($admin_data = mysqli_fetch_assoc($admin_result))) {
            $is_admin = ((int)$admin_data['is_admin'] === 1);
        }
        mysqli_stmt_close($check_admin);
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