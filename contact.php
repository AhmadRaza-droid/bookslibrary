<?php
session_start();
include 'config.php';

// Get settings from database
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM settings");
if($result){
    while($row = mysqli_fetch_assoc($result)){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Handle message submission
if(isset($_POST['send_message'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $query = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";
    if(mysqli_query($conn, $query)){
        echo "<script>alert('✅ Message sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to send message. Please try again.');</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="style.css?v=8000">
    <style>
        .contact-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .contact-info {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .contact-info h2 {
            color: #0b1f3a;
            margin-bottom: 20px;
        }
        .contact-info .info-item {
            margin: 15px 0;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .contact-info .info-item strong {
            color: #0b1f3a;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .contact-form h2 {
            color: #0b1f3a;
            margin-bottom: 20px;
        }
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }
        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #0b1f3a;
        }
        .contact-form button {
            width: 100%;
            padding: 14px;
            background: #0b1f3a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .contact-form button:hover {
            background: #1a3a5c;
        }
        .dark-mode .contact-info {
            background: #16213e;
            color: white;
        }
        .dark-mode .contact-info h2 {
            color: white;
        }
        .dark-mode .contact-info .info-item {
            border-bottom-color: #333;
        }
        .dark-mode .contact-info .info-item strong {
            color: #ffc72c;
        }
        .dark-mode .contact-form {
            background: #16213e;
            color: white;
        }
        .dark-mode .contact-form h2 {
            color: white;
        }
        .dark-mode .contact-form input,
        .dark-mode .contact-form textarea {
            background: #1a1a3a;
            color: white;
            border-color: #333;
        }
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
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
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
        <li><a class="active" href="contact.php">Contact</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>📩 Contact Us</h1>
    <p>Have any questions? Send us a message.</p>
</section>

<div class="contact-container">
    <!-- Contact Info -->
    <div class="contact-info">
        <h2>📌 Library Information</h2>
        
        <div class="info-item">
            <strong>📍 Address:</strong><br>
            <?php echo htmlspecialchars($settings['contact_address'] ?? 'University Campus Library'); ?>
        </div>
        
        <div class="info-item">
            <strong>📧 Email:</strong><br>
            <a href="mailto:<?php echo htmlspecialchars($settings['contact_email'] ?? 'universitylibrary172@gmail.com'); ?>">
                <?php echo htmlspecialchars($settings['contact_email'] ?? 'universitylibrary172@gmail.com'); ?>
            </a>
        </div>
        
        <div class="info-item">
            <strong>📞 Phone:</strong><br>
            <?php echo htmlspecialchars($settings['contact_phone'] ?? '+92 300 1234567'); ?>
        </div>
        
        <div class="info-item">
            <strong>🕐 Timing:</strong><br>
            Monday - Saturday, 9:00 AM - 5:00 PM
        </div>
    </div>
    
    <!-- Contact Form -->
    <div class="contact-form">
        <h2>✉️ Send Message</h2>
        
        <form method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
            <button type="submit" name="send_message">📤 Send Message</button>
        </form>
    </div>
</div>

<footer>
    <p><?php echo htmlspecialchars($settings['footer_text'] ?? '© 2026 Book\'s Library. All Rights Reserved.'); ?></p>
</footer>

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