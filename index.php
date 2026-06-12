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

$chatBooks = mysqli_query($conn,
"SELECT title, author, description, category
 FROM books
 LIMIT 1000");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Library Management System</title>

<link rel="stylesheet" href="style.css?v=2400">
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
            Hello 👋 Tell me a book name or category like programming, science, islamic, urdu, novel, history or horror.
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
let bookData = [
<?php while($chat = mysqli_fetch_assoc($chatBooks)){ ?>
{
title: "<?php echo addslashes($chat['title']); ?>",
author: "<?php echo addslashes($chat['author']); ?>",
description: "<?php echo addslashes($chat['description'] ?? ''); ?>",
category: "<?php echo addslashes($chat['category'] ?? ''); ?>"
},
<?php } ?>
];

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
    chatBody.innerHTML += `<div class="bot-message" id="typing">Typing...</div>`;
    chatBody.scrollTop = chatBody.scrollHeight;

    let reply = getBotReply(message);

    setTimeout(() => {
        let typing = document.getElementById("typing");

        if(typing){
            typing.remove();
        }

        chatBody.innerHTML += `<div class="bot-message">${reply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 700);

    input.value = "";
}

function getBotReply(message){

    if(message.includes("hello") || message.includes("hi") || message.includes("salam")){
        return "👋 Hello! Tell me a book name or category like horror, novel, science, adventure, detective, history, children, fantasy or poetry.";
    }

    if(message.includes("download")){
        return "⬇ Open the Books page and press Download EPUB under any book.";
    }

    if(message.includes("login")){
        return "🔐 Use the Login page to access your account.";
    }

    if(message.includes("register") || message.includes("signup")){
        return "📝 Use the Register page to create a new account.";
    }

    if(message.includes("favorite")){
        return "❤️ Login first, then press Favorite under any book. Favorite books show in your Profile.";
    }

    if(message.includes("review") || message.includes("rating")){
        return "⭐ You can rate books and write reviews on the Books page.";
    }

    if(message.includes("admin")){
        return "🛡 Admin can manage books, users, reviews, requests and analytics from Admin Panel.";
    }

    if(message.includes("dark")){
        return "🌙 Press the Dark button in the navbar to switch theme.";
    }

    let categories = {
        horror: ["horror","vampire","dracula","frankenstein","ghost","gothic","terror","fear","dark","mystery"],
        novel: ["novel","fiction","story","classic","moby","pride","love","family"],
        science: ["science","scientist","physics","chemistry","biology","invisible","time machine"],
        adventure: ["adventure","sea","island","treasure","journey","travel","pirate"],
        detective: ["detective","sherlock","holmes","crime","case","mystery"],
        history: ["history","historical","war","revolution","empire"],
        children: ["children","child","alice","wonderland","animal","family"],
        fantasy: ["fantasy","wonderland","dream","strange","magic"],
        poetry: ["poetry","poem","poems"],
        romance: ["romance","love","marriage"],
        programming: ["programming","coding","computer","assembly","software","java","python","algorithm"]
    };

    for(let cat in categories){
        if(message.includes(cat)){
            return recommendByWords(cat, categories[cat]);
        }
    }

    let words = message.split(" ").filter(w => w.length > 2);

    let matches = bookData.filter(book => {
        let text = (
            book.title + " " +
            book.author + " " +
            book.description + " " +
            book.category
        ).toLowerCase();

        return words.some(word => text.includes(word));
    });

    if(matches.length > 0){
        return makeReply(matches, "📚 Recommended Books");
    }

    return "😢 No matching books found. Try Dracula, Frankenstein, Sherlock, horror, novel, adventure, detective, science, children or fantasy.";
}

function recommendByWords(category, words){

    let matches = bookData.filter(book => {
        let text = (
            book.title + " " +
            book.author + " " +
            book.description + " " +
            book.category
        ).toLowerCase();

        return words.some(word => text.includes(word));
    });

    if(matches.length === 0){
        return "😢 No books found for " + category + ". Try another word like novel, adventure, detective, science or children.";
    }

    return makeReply(matches, "📚 Recommended " + category + " books");
}

function makeReply(matches, heading){

    let reply = heading + ":<br><br>";

    matches.slice(0,5).forEach(book => {
        reply += "• " + book.title + " - " + book.author + "<br>";
    });

    reply += "<br><a href='books.php'>Open Books Page</a>";

    return reply;
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