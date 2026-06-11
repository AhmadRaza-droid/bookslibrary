<?php
session_start();
include 'config.php';

$books = mysqli_query($conn, "SELECT * FROM books LIMIT 8");

$recentBooks = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC LIMIT 4");

$topRated = mysqli_query($conn,
"SELECT books.*, AVG(reviews.rating) AS avg_rating
 FROM books
 JOIN reviews ON books.id = reviews.book_id
 GROUP BY books.id
 ORDER BY avg_rating DESC
 LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Library Management System</title>

<link rel="stylesheet" href="style.css?v=2300">
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
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="hero">
    <div class="hero-text">
        <h1>Welcome To <br><b>Digital Library</b></h1>

        <p>
            Manage books easily and explore a world of knowledge.
            Read. Learn. Grow.
        </p>

        <div class="hero-buttons">
            <a href="books.php" class="btn">📖 Explore Books</a>
            <a href="login.php" class="btn-outline">👤 Login Now</a>
        </div>
    </div>
</section>

<section class="features">

    <div class="card">
        <h3>📘 Huge Collection</h3>
        <p>Explore thousands of books across different categories.</p>
    </div>

    <div class="card">
        <h3>🔍 Easy Search</h3>
        <p>Find books quickly by title, author, or category.</p>
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

                <img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>"
                     alt="Book Cover"
                     style="width:100%; height:180px; object-fit:cover; border-radius:10px;">

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <p><?php echo htmlspecialchars($row['author']); ?></p>

                <a href="books.php">
                    <button>View Book</button>
                </a>

            </div>

        <?php } ?>

    </div>
</section>

<section class="books">
    <h2>🆕 Recently Added Books</h2>

    <div class="book-container">

        <?php while($recent = mysqli_fetch_assoc($recentBooks)){ ?>

            <div class="book-card">

                <img src="<?php echo htmlspecialchars($recent['cover_image_url']); ?>"
                     alt="Book Cover"
                     style="width:100%; height:180px; object-fit:cover; border-radius:10px;">

                <h3><?php echo htmlspecialchars($recent['title']); ?></h3>

                <p><?php echo htmlspecialchars($recent['author']); ?></p>

                <a href="books.php">
                    <button>Read Now</button>
                </a>

            </div>

        <?php } ?>

    </div>
</section>

<section class="books">
    <h2>⭐ Top Rated Books</h2>

    <div class="book-container">

        <?php if(mysqli_num_rows($topRated) > 0){ ?>

            <?php while($top = mysqli_fetch_assoc($topRated)){ ?>

                <div class="book-card">

                    <img src="<?php echo htmlspecialchars($top['cover_image_url']); ?>"
                         alt="Book Cover"
                         style="width:100%; height:180px; object-fit:cover; border-radius:10px;">

                    <h3><?php echo htmlspecialchars($top['title']); ?></h3>

                    <p><?php echo htmlspecialchars($top['author']); ?></p>

                    <p>
                        <strong>Rating:</strong>
                        <?php echo round($top['avg_rating'], 1); ?> ⭐
                    </p>

                    <a href="books.php">
                        <button>View Book</button>
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>No rated books yet.</p>

        <?php } ?>

    </div>
</section>

<footer>
    <p>© 2026 UMT University Library. All Rights Reserved.</p>
    <p>Developed by Ahmad Raza</p>
</footer>

<div class="chatbot-icon" onclick="toggleChat()">
    🤖
</div>

<div class="chatbot-box" id="chatbot">

    <div class="chat-header">
        AI Library Assistant
    </div>

    <div class="chat-body" id="chat-body">

        <div class="bot-message">
            Hello 👋 Ask me anything about books, downloads, login, admin or this website.
        </div>

    </div>

    <div class="chat-input">

        <input type="text"
               id="userInput"
               placeholder="Ask something..."
               onkeydown="if(event.key === 'Enter'){sendMessage();}">

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
let message = input.value.trim();
let chatBody = document.getElementById("chat-body");

if(message==""){
return;
}

chatBody.innerHTML +=
`<div class="user-message">${message}</div>`;

chatBody.innerHTML +=
`<div class="bot-message" id="typing">
Typing...
</div>`;

chatBody.scrollTop = chatBody.scrollHeight;

input.value="";

fetch("chatbot.php",{
method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:"message="+encodeURIComponent(message)
})

.then(response => response.text())

.then(reply => {

let typing = document.getElementById("typing");

if(typing){
typing.remove();
}

chatBody.innerHTML +=
`<div class="bot-message">${reply}</div>`;

chatBody.scrollTop = chatBody.scrollHeight;

})

.catch(() => {

let typing = document.getElementById("typing");

if(typing){
typing.remove();
}

chatBody.innerHTML +=
`<div class="bot-message">
⚠ Error connecting to chatbot.
</div>`;

});

}

function getBotReply(message){

    if(message.includes("hello") || message.includes("hi") || message.includes("salam")){
        return "👋 Hello! I am your AI Library Assistant. How can I help you?";
    }

    if(message.includes("book") || message.includes("library") || message.includes("read")){
        return "📚 Open the Books page to search, read and download books online.";
    }

    if(message.includes("download") || message.includes("epub")){
        return "⬇ Press Download EPUB under any book to download it.";
    }

    if(message.includes("search") || message.includes("find")){
        return "🔍 Use the search bar and category filter on the Books page.";
    }

    if(message.includes("category") || message.includes("filter")){
        return "📂 You can filter books by programming, science, adventure, history, horror, novel and more.";
    }

    if(message.includes("favorite") || message.includes("wishlist")){
        return "❤️ Login first, then press Favorite under any book. Your favorites show on Profile.";
    }

    if(message.includes("review") || message.includes("rating")){
        return "⭐ Logged-in users can rate books and write reviews on the Books page.";
    }

    if(message.includes("recent") || message.includes("history")){
        return "🕒 Recently viewed books appear in your Profile after you open a book.";
    }

    if(message.includes("login")){
        return "🔐 Use the Login page to access your account.";
    }

    if(message.includes("register") || message.includes("signup")){
        return "📝 Use the Register page to create a new account.";
    }

    if(message.includes("admin")){
        return "🛡 The Admin Panel allows admin to manage books, users, reviews and analytics.";
    }

    if(message.includes("contact") || message.includes("message") || message.includes("help")){
        return "📞 Use the Contact page to send a message to the library team.";
    }

    if(message.includes("developer") || message.includes("created") || message.includes("made")){
        return "👨‍💻 This website is developed by Ahmad Raza.";
    }

    if(message.includes("dark")){
        return "🌙 Press the Dark button in the navbar to switch theme.";
    }

    return "🤖 Ask me about books, search, downloads, favorites, reviews, login, admin, profile or website features.";
}
</script>

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