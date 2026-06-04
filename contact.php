<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="logo">📖 Library Management System</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="admin_login.php">Admin</a></li>
        <li><a class="active" href="contact.php">Contact</a></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Contact Us</h1>
    <p>Have any questions? Send us a message.</p>
</section>

<section class="contact-section">

    <div class="contact-info">
        <h2>Library Information</h2>
        <p><b>Address:</b> University Campus Library</p>
        <p><b>Email:</b> universitylibrary172@gmail.com</p>
        <p><b>Phone:</b> 03706725643</p>
        <p><b>Timing:</b> Monday - Saturday, 9:00 AM - 5:00 PM</p>
    </div>

    <div class="contact-form">
        <h2>Send Message</h2>

        <form action="message_process.php" method="POST">

            <input type="text" 
                   name="name" 
                   placeholder="Your Name" 
                   required>

            <input type="email" 
                   name="email" 
                   placeholder="Your Email" 
                   required>

            <textarea name="message" 
                      placeholder="Your Message" 
                      required></textarea>

            <button type="submit">
                Send Message
            </button>

        </form>
    </div>

</section>

</body>
</html>