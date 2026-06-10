<?php
include 'session_timeout.php';
include 'config.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");

    exit();

}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,

"SELECT * FROM users
WHERE id='$user_id'"

);

$user = mysqli_fetch_assoc($result);

$favorites = mysqli_query($conn,

"SELECT books.*

FROM favorites

JOIN books
ON favorites.book_id = books.id

WHERE favorites.user_id='$user_id'"

);

?>

<!DOCTYPE html>
<html>

<head>

<title>
My Profile
</title>

<link rel="stylesheet"
href="style.css?v=3000">

</head>

<body>

<nav>

<div class="logo">
📖 Library Management System
</div>

<ul>

<li>
<a href="index.php">
Home
</a>
</li>

<li>
<a href="books.php">
Books
</a>
</li>

<li>
<a class="active"
href="profile.php">
Profile
</a>
</li>

<?php
if(
isset($_SESSION['email'])
&&
$_SESSION['email'] ==
"universitylibrary172@gmail.com"
){
?>

<li>
<a href="admin_dashboard.php">
Admin Panel
</a>
</li>

<?php } ?>

<li>
<a href="logout.php">
Logout
</a>
</li>
<li><button onclick="toggleDarkMode()" class="dark-btn">🌙 Dark</button></li>


</ul>

</nav>

<section class="form-section">

<div class="form-box">

<h2>
My Profile
</h2>

<p>

<b>
Name:
</b>

<?php
echo htmlspecialchars($user['fullname']);
?>

</p>

<p>

<b>
Email:
</b>

<?php
echo htmlspecialchars($user['email']);
?>

</p>

<p>

<b>
Password:
</b>

********

</p>

<br>

<a href="change_password.php">

<button>

Change Password

</button>

</a>

<br><br>

<a href="logout.php">

<button>

Logout

</button>

</a>

<!-- FAVORITE BOOKS -->

<h2 style="margin-top:40px;">

❤️ My Favorite Books

</h2>

<?php

if(mysqli_num_rows($favorites) > 0){

while($book = mysqli_fetch_assoc($favorites)){

?>

<div style="
background:#f5f5f5;
padding:15px;
margin-top:15px;
border-radius:12px;
text-align:left;">

<h3>

<?php
echo htmlspecialchars($book['title']);
?>

</h3>

<p>

<strong>
Author:
</strong>

<?php
echo htmlspecialchars($book['author']);
?>

</p>

<?php
if(
isset($book['category'])
&&
$book['category'] != ""
){
?>

<p>

<strong>
Category:
</strong>

<?php
echo htmlspecialchars($book['category']);
?>

</p>

<?php } ?>

<a href="books.php">

<button style="margin-top:10px;">

View Book

</button>

</a>

</div>

<?php

}

}
else{

echo "

<p style='margin-top:20px;'>

No favorite books yet 😢

</p>

";

}

?>

</div>

</section>
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
