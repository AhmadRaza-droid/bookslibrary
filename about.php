<?php
session_start();
include 'config.php';

// Get settings
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM settings");
if($result){
    while($row = mysqli_fetch_assoc($result)){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - <?php echo htmlspecialchars($settings['site_name'] ?? 'Library Management System'); ?></title>
    <link rel="stylesheet" href="style.css?v=10">
    <style>
        .about-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .about-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            animation: fadeUp 0.6s ease;
            transition: transform 0.3s ease;
        }
        .about-card:hover {
            transform: translateY(-5px);
        }
        .about-card h2 {
            color: #0b1f3a;
            margin-bottom: 15px;
            font-size: 28px;
        }
        .about-card h2 .icon {
            margin-right: 10px;
        }
        .about-card p {
            color: #555;
            line-height: 1.8;
            font-size: 16px;
        }
        .about-card .highlight {
            color: #0b1f3a;
            font-weight: bold;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .team-member {
            background: #f5f8ff;
            padding: 12px 18px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            border-left: 3px solid #0b1f3a;
        }
        .team-member:hover {
            transform: scale(1.05);
            background: #e8edf5;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .team-member .name {
            font-weight: bold;
            color: #0b1f3a;
        }
        .team-member .role {
            font-size: 12px;
            color: #888;
        }
        .version-badge {
            display: inline-block;
            background: #0b1f3a;
            color: white;
            padding: 5px 18px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f5f8ff;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-item .number {
            font-size: 32px;
            font-weight: bold;
            color: #0b1f3a;
        }
        .stat-item .label {
            font-size: 14px;
            color: #888;
            margin-top: 5px;
        }
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        .dark-mode .about-card {
            background: #16213e;
            color: white;
        }
        .dark-mode .about-card h2 {
            color: white;
        }
        .dark-mode .about-card p {
            color: #ccc;
        }
        .dark-mode .about-card .highlight {
            color: #ffc72c;
        }
        .dark-mode .team-member {
            background: #1a1a3a;
            border-left-color: #ffc72c;
        }
        .dark-mode .team-member .name {
            color: white;
        }
        .dark-mode .team-member:hover {
            background: #2a2a4e;
        }
        .dark-mode .stat-item {
            background: #1a1a3a;
        }
        .dark-mode .stat-item .number {
            color: #ffc72c;
        }
        .dark-mode .stat-item .label {
            color: #aaa;
        }
        .dark-mode .version-badge {
            background: #ffc72c;
            color: #0b1f3a;
        }
        @media (max-width: 768px) {
            .about-card {
                padding: 25px;
            }
            .about-card h2 {
                font-size: 22px;
            }
            .team-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">📖 <?php echo htmlspecialchars($settings['site_name'] ?? 'Library Management System'); ?></div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="profile.php">Profile</a></li>
        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
            <li><a href="admin_dashboard.php">Admin Panel</a></li>
        <?php } ?>
        <li><a class="active" href="about.php">About</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>📖 About <?php echo htmlspecialchars($settings['site_name'] ?? 'University Library'); ?></h1>
    <p><?php echo htmlspecialchars($settings['site_tagline'] ?? 'Digital platform for reading and exploring books online.'); ?></p>
</section>

<div class="about-container">

    <!-- Mission Card -->
    <div class="about-card">
        <h2><span class="icon">📚</span> About Our Library</h2>
        <p>
            <span class="highlight"><?php echo htmlspecialchars($settings['site_name'] ?? 'University Library'); ?></span> 
            is a modern digital platform where students can read, explore, and download books online. 
            Our mission is to make learning simple, fast, and accessible for everyone.
        </p>
        <br>
        <p>
            This Library Management System helps students manage books, explore categories, 
            track reading progress, and improve their learning experience.
        </p>
        <br>
        <span class="version-badge">📌 Version <?php echo htmlspecialchars($settings['site_tagline'] ?? '2.0'); ?></span>
    </div>

    <!-- Stats Card -->
    <div class="about-card" style="animation-delay: 0.2s;">
        <h2><span class="icon">📊</span> Library Stats</h2>
        <div class="stats-row">
            <div class="stat-item">
                <div class="number pulse"><?php 
                    $total_books = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM books"));
                    echo $total_books; 
                ?></div>
                <div class="label">📚 Total Books</div>
            </div>
            <div class="stat-item">
                <div class="number pulse"><?php 
                    $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
                    echo $total_users; 
                ?></div>
                <div class="label">👥 Total Users</div>
            </div>
            <div class="stat-item">
                <div class="number pulse"><?php 
                    $total_downloads = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM downloads"));
                    echo $total_downloads; 
                ?></div>
                <div class="label">📥 Total Downloads</div>
            </div>
        </div>
    </div>

    <!-- Developer Card -->
    <div class="about-card" style="animation-delay: 0.4s;">
        <h2><span class="icon">👨‍💻</span> Developer</h2>
        <p style="font-size:18px; font-weight:bold; color:#0b1f3a;">
            Ahmad Raza
        </p>
        <p style="color:#888;">Full Stack Developer</p>
    </div>

    <!-- Team Card -->
    <div class="about-card" style="animation-delay: 0.6s;">
        <h2><span class="icon">🤝</span> Supporters & Contributors</h2>
        <div class="team-grid">
            <div class="team-member">
                <div class="name">Muhammad Anas</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Muhammad Umair Hassan</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Amjad Ali Awan</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Muhammad Dawood</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Munawar Hussain</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Ehtasham Bilal</div>
                <div class="role">Contributor</div>
            </div>
            <div class="team-member">
                <div class="name">Khaleel</div>
                <div class="role">Contributor</div>
            </div>
        </div>
    </div>

</div>

<footer>
    <p><?php echo htmlspecialchars($settings['footer_text'] ?? '© 2026 University Library. All Rights Reserved.'); ?></p>
    <p>Developed by Ahmad Raza</p>
</footer>

<script>
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
    }
    else{
        localStorage.setItem("theme", "light");
    }
}
</script>

</body>
</html>