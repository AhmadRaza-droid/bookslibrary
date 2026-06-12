<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_GET['id'])){
    header("Location: books.php");
    exit();
}

$id = (int)$_GET['id'];

$result = mysqli_query($conn,
"SELECT * FROM books WHERE id='$id'");

$book = mysqli_fetch_assoc($result);

if(!$book){
    header("Location: books.php");
    exit();
}

if(isset($_SESSION['user_id'])){

    $user_id = $_SESSION['user_id'];

    mysqli_query($conn,
    "INSERT INTO recently_viewed(user_id, book_id)
     VALUES('$user_id','$id')");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="style.css?v=10000">
</head>

<body>

<nav>
    <div class="logo">📖 Reading Book</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">

    <h1><?php echo htmlspecialchars($book['title']); ?></h1>

    <p>
        Author:
        <?php echo htmlspecialchars($book['author']); ?>
    </p>

</section>

<div style="padding:20px;">

    <iframe
        src="<?php echo htmlspecialchars($book['read_link']); ?>"
        style="width:100%; height:850px; border:2px solid #061b33; border-radius:12px;">
    </iframe>

</div>

<?php if(isset($_SESSION['user_id'])){ ?>

<div style="padding:20px; max-width:800px; margin:auto;">

<h2>📖 Reading Progress</h2>

<input type="hidden"
       id="book_id"
       value="<?php echo $id; ?>">

<select id="progress"
style="width:100%; padding:12px; margin-bottom:20px;"
onchange="saveProgress()">

    <option value="0">0%</option>
    <option value="25">25%</option>
    <option value="50">50%</option>
    <option value="75">75%</option>
    <option value="100">100%</option>

</select>

<h2>🔖 Save Bookmark</h2>

<form action="add_bookmark.php" method="POST">

    <input type="hidden"
           name="book_id"
           value="<?php echo $id; ?>">

    <textarea
        name="note"
        placeholder="Example: Resume from Chapter 5..."
        required
        style="width:100%; height:120px; padding:10px;">
    </textarea>

    <br><br>

    <button type="submit">
        Save Bookmark
    </button>

</form>

</div>

<?php } ?>

<script>

function saveProgress(){

    let book_id =
    document.getElementById("book_id").value;

    let progress =
    document.getElementById("progress").value;

    fetch("save_progress.php",{

        method:"POST",

        headers:{
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:
        "book_id="+book_id+
        "&progress="+progress

    });

}

if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
}

function toggleDarkMode(){

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
    }
    else{
        localStorage.setItem("theme","light");
    }
}

</script>

</body>
</html>