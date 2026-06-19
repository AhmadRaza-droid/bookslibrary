<?php
include 'session_timeout.php';
include 'config.php';
include 'maintenance_check.php';


$search = "";
$category = "";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$limit = 6;
$offset = ($page - 1) * $limit;

$where = "WHERE 1";

if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $where .= " AND (
        title LIKE '%$search%'
        OR author LIKE '%$search%'
        OR description LIKE '%$search%'
        OR category LIKE '%$search%'
    )";
}

if(isset($_GET['category']) && $_GET['category'] != ""){
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $where .= " AND category LIKE '%$category%'";
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books $where");
$countRow = mysqli_fetch_assoc($countResult);

$totalBooks = $countRow['total'];
$totalPages = ceil($totalBooks / $limit);

$result = mysqli_query($conn, "SELECT * FROM books $where LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books</title>
    <link rel="stylesheet" href="style.css?v=1800">
</head>

<body>

<nav>
    <div class="logo">📖 Book's Library</div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a class="active" href="books.php">Books</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="contact.php">Contact</a></li>

        <?php if(isset($_SESSION['email']) && $_SESSION['email'] == "universitylibrary172@gmail.com"){ ?>
            <li><a href="admin_dashboard.php">Admin Panel</a></li>
        <?php } ?>

        <li><a href="about.php">About</a></li>
        <li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>
    </ul>
</nav>

<section class="page-header">
    <h1>Available Books</h1>
    <p>Read and download free books online.</p>
</section>

<section class="search-section">

<form method="GET" action="books.php">

<input type="text"
       name="search"
       id="searchInput"
       autocomplete="off"
       placeholder="Search books by title, author or description..."
       value="<?php echo htmlspecialchars($search); ?>">

<div id="suggestions"
style="background:white;
border:1px solid #ddd;
border-radius:8px;
margin-top:5px;
text-align:left;
position:relative;
z-index:999;">
</div>

<select name="category"
style="width:100%; padding:14px; margin-top:12px; border-radius:10px; border:2px solid #ddd;">

<option value="">All Categories</option>

<option value="programming" <?php if($category=="programming") echo "selected"; ?>>Programming</option>
<option value="science" <?php if($category=="science") echo "selected"; ?>>Science</option>
<option value="adventure" <?php if($category=="adventure") echo "selected"; ?>>Adventure</option>
<option value="movie" <?php if($category=="movie") echo "selected"; ?>>Movie</option>
<option value="history" <?php if($category=="history") echo "selected"; ?>>History</option>
<option value="islamic" <?php if($category=="islamic") echo "selected"; ?>>Islamic</option>
<option value="urdu" <?php if($category=="urdu") echo "selected"; ?>>Urdu</option>
<option value="business" <?php if($category=="business") echo "selected"; ?>>Business</option>
<option value="horror" <?php if($category=="horror") echo "selected"; ?>>Horror</option>
<option value="detective" <?php if($category=="detective") echo "selected"; ?>>Detective</option>
<option value="novel" <?php if($category=="novel") echo "selected"; ?>>Novel</option>
<option value="philosophy" <?php if($category=="philosophy") echo "selected"; ?>>Philosophy</option>

</select>

<button type="submit"
style="margin-top:12px; padding:12px 20px; border:none; border-radius:8px; background:#061b33; color:white; cursor:pointer;">
Filter Books
</button>

</form>

</section>

<section class="books-container">

<?php
if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

    $book_id = $row['id'];

    $reviewQuery = mysqli_query($conn,
    "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
     FROM reviews
     WHERE book_id='$book_id'");

    $reviewData = mysqli_fetch_assoc($reviewQuery);

    $avgRating = round($reviewData['avg_rating'], 1);
    $totalReviews = $reviewData['total_reviews'];

    $allReviews = mysqli_query($conn,
    "SELECT reviews.*, users.fullname
     FROM reviews
     JOIN users ON reviews.user_id = users.id
     WHERE reviews.book_id='$book_id'
     ORDER BY reviews.created_at DESC
     LIMIT 3");
?>

<div class="book-card">

<img src="<?php echo htmlspecialchars($row['cover_image_url']); ?>" alt="Book Cover">

<h3><?php echo htmlspecialchars($row['title']); ?></h3>

<p><strong>Author:</strong> <?php echo htmlspecialchars($row['author']); ?></p>

<p>
<strong>Rating:</strong>
<?php
if($totalReviews > 0){
    echo $avgRating . " ⭐ (" . $totalReviews . " reviews)";
} else {
    echo "No ratings yet";
}
?>
</p>

<?php if(isset($row['category']) && $row['category'] != ""){ ?>
<p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
<?php } ?>

<p><?php echo htmlspecialchars($row['description']); ?></p>

<p><strong>Downloads:</strong> <?php echo (int)$row['downloads']; ?></p>

<div class="book-buttons">

<a href="read_book.php?id=<?php echo $row['id']; ?>">
    <button>Read Book</button>
</a>

<a href="download_book.php?id=<?php echo $row['id']; ?>" target="_blank">
    <button>Download EPUB</button>
</a>

<?php if(isset($_SESSION['user_id'])){ ?>
    <a href="favorite_book.php?book_id=<?php echo $row['id']; ?>">
        <button style="background:red; color:white;">❤️ Favorite</button>
    </a>
<?php } ?>

</div>

<?php if(isset($_SESSION['user_id'])){ ?>

<form action="add_review.php" method="POST" style="margin-top:15px;">

    <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">

    <select name="rating" required style="width:100%; padding:10px; margin-bottom:10px;">
        <option value="">Rate Book</option>
        <option value="5">⭐⭐⭐⭐⭐</option>
        <option value="4">⭐⭐⭐⭐</option>
        <option value="3">⭐⭐⭐</option>
        <option value="2">⭐⭐</option>
        <option value="1">⭐</option>
    </select>

    <textarea name="review" placeholder="Write your review..." required
    style="width:100%; height:80px; padding:10px; margin-bottom:10px;"></textarea>

    <button type="submit" name="submit_review">
        Submit Review
    </button>

</form>

<?php } ?>

<h4 style="margin-top:15px;">User Reviews</h4>

<?php if(mysqli_num_rows($allReviews) > 0){ ?>

<?php while($reviewRow = mysqli_fetch_assoc($allReviews)){ ?>

<div style="background:#f5f5f5; padding:10px; margin-top:10px; border-radius:8px; text-align:left;">

    <p><strong><?php echo htmlspecialchars($reviewRow['fullname']); ?></strong></p>

    <p><?php echo $reviewRow['rating']; ?> ⭐</p>

    <p><?php echo htmlspecialchars($reviewRow['review']); ?></p>

</div>

<?php } ?>

<?php } else { ?>

<p style="margin-top:10px;">No reviews yet.</p>

<?php } ?>

</div>

<?php
}

} else {
    echo "<h2 style='text-align:center; width:100%;'>No books found</h2>";
}
?>

</section>

<div style="text-align:center; margin:30px;">

<?php if($page > 1){ ?>
    <a href="books.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
        <button>Previous</button>
    </a>
<?php } ?>

<span style="margin:0 15px; font-weight:bold;">
    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
</span>

<?php if($page < $totalPages){ ?>
    <a href="books.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
        <button>Next</button>
    </a>
<?php } ?>

</div>

<script>
let searchInput = document.getElementById("searchInput");
let suggestionsBox = document.getElementById("suggestions");

searchInput.addEventListener("keyup", function(){

    let q = this.value;

    if(q.length < 2){
        suggestionsBox.innerHTML = "";
        return;
    }

    fetch("search_suggestions.php?q=" + encodeURIComponent(q))
    .then(response => response.text())
    .then(data => {
        suggestionsBox.innerHTML = data;
    });

});

function selectSuggestion(title){
    searchInput.value = title;
    suggestionsBox.innerHTML = "";
}

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