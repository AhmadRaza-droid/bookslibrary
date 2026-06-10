<?php
session_start();
include 'config.php';
$books = mysqli_query($conn, "SELECT * FROM books LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css?v=100">
</head>
<body>

<nav>
    <div class="logo">📖 Library Management System</div>

    <ul>
        <li><a class="active" href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="profile.php">Profile</a></li>
        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
    <li><a href="admin_dashboard.php">Admin Panel</a></li>
<?php } ?>
        <li><a href="about.php">About</a></li>
    </ul>
</nav>

<section class="hero">
    <div class="hero-text">
               <h1>Welcome To <br><b>Digital Library</b></h1>
        <p>Manage books easily and explore a world of knowledge. Read. Learn. Grow.</p>

        <a href="books.php" class="btn">📖 Explore Books</a>
        <a href="login.php" class="btn-outline">👤 Login Now</a>
    </div>
</section>

<section class="features">
    <div class="card">
        <h3>📘 Huge Collection</h3>
        <p>Explore thousands of books across different categories.</p>
    </div>

    <div class="card">
        <h3>🔍 Easy Search</h3>
        <p>Find books quickly by title or author.</p>
    </div>

    <div class="card">
        <h3>📥 Easy Download</h3>
        <p>Read books online and download EPUB files.</p>
    </div>

    <div class="card">
        <h3>🛡 Secure & Reliable</h3>
        <p>Your data is safe with our secure system.</p>
    </div>
</section>

<section class="books">
    <h2>Featured Books</h2>

    <div class="book-container">

        <?php while($row = mysqli_fetch_assoc($books)){ ?>

            <div class="book-card">
                <img src="<?php echo $row['cover_image_url']; ?>" alt="Book Cover" style="width:100%; height:180px; object-fit:cover; border-radius:10px;">

                <h3><?php echo $row['title']; ?></h3>
                <p><?php echo $row['author']; ?></p>

                <a href="books.php">
                    <button>View Book</button>
                </a>
            </div>

        <?php } ?>

    </div>
</section>
 
<footer>

    <p>
        © 2026 UMT University Library. All Rights Reserved.
    </p>

    <p>
        Developed by Ahmad Raza
    </p>

</footer>
<!-- AI CHATBOT -->

<div class="chatbot-icon" onclick="toggleChat()">
    🤖
</div>

<div class="chatbot-box" id="chatbot">

    <div class="chat-header">
        AI Library Assistant
    </div>

    <div class="chat-body" id="chat-body">

        <div class="bot-message">
            Hello 👋 Ask me about books, login, admin, downloads or contact.
        </div>

    </div>

    <div class="chat-input">

        <input type="text"
               id="userInput"
               placeholder="Ask something...">

        <button onclick="sendMessage()">Send</button>

    </div>

</div>
<script>
function toggleChat(){
    let bot = document.getElementById("chatbot");
    bot.style.display = (bot.style.display === "flex") ? "none" : "flex";
}

function sendMessage(){
    let input = document.getElementById("userInput");
    let message = input.value.toLowerCase().trim();
    let chatBody = document.getElementById("chat-body");

    if(message === ""){
        return;
    }

    chatBody.innerHTML += `<div class="user-message">${input.value}</div>`;

    let reply = getBotReply(message);

    setTimeout(() => {
        chatBody.innerHTML += `<div class="bot-message">${reply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 400);

    input.value = "";
}

function getBotReply(message){

    if(message.includes("website") || message.includes("system") || message.includes("library") || message.includes("about")){
        return "📚 This is UMT University Library, a digital library website where users can search, read, and download books online.";
    }

    if(message.includes("home") || message.includes("main page")){
        return "🏠 The Home page shows the welcome section, website features, featured books, footer, and AI assistant.";
    }

    if(message.includes("book") || message.includes("books") || message.includes("kitab")){
        return "📖 Open the Books page to view all available books. You can read books online or download EPUB files.";
    }

    if(message.includes("search") || message.includes("find") || message.includes("dhundo")){
        return "🔍 Go to the Books page and use the search bar. You can search by book title, author name, or description.";
    }

    if(message.includes("read") || message.includes("online")){
        return "📘 To read a book online, open the Books page and press the Read Book button under any book.";
    }

    if(message.includes("download") || message.includes("epub") || message.includes("file")){
        return "⬇ To download a book, open the Books page and press the Download EPUB button.";
    }

    if(message.includes("login") || message.includes("sign in")){
        return "🔐 Use the Login page to access your user account.";
    }

    if(message.includes("register") || message.includes("signup") || message.includes("sign up") || message.includes("account")){
        return "📝 Use the Register page to create a new user account.";
    }

    if(message.includes("forgot") || message.includes("reset") || message.includes("password") || message.includes("otp")){
        return "🔑 If you forget your password, use Forgot Password. The system sends an OTP to your email for verification.";
    }

    if(message.includes("profile")){
        return "👤 The Profile page shows your user account information.";
    }

    if(message.includes("contact") || message.includes("message") || message.includes("email") || message.includes("support") || message.includes("help")){
        return "📞 Use the Contact page to send a message to the library team. Your message is also sent by email.";
    }

    if(message.includes("admin")){
        return "🛡 The Admin panel is for library admin only. Admin can manage books, users, and website data.";
    }

    if(message.includes("developer") || message.includes("made") || message.includes("created")){
        return "👨‍💻 This website is developed by Ahmad Raza.";
    }

    if(message.includes("contributor") || message.includes("supporter")){
        return "🤝 Supporters and contributors are Muhammad Anas, Muhammad Umair Hassan, Amjad Ali Awan, Muhammad Dawood, Munawar Hussain, Ehtasham Bilal, and Khaleel.";
    }

    if(message.includes("safe") || message.includes("secure") || message.includes("security")){
        return "🛡 The website uses login, registration, password reset, and OTP verification for better security.";
    }

    if(message.includes("thank")){
        return "😊 You're welcome! Happy reading.";
    }

    if(message.includes("hello") || message.includes("hi") || message.includes("salam")){
        return "👋 Hello! I am your AI Library Assistant. Ask me anything about this website.";
    }

    return "🤖 I can answer questions about this website, books, search, read, download, login, register, OTP, profile, contact, admin, developer, and contributors.";
}
</script>
</body>
</html>