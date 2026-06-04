<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM users WHERE id='$user_id'");

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>

    <title>My Profile</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<nav>

    <div class="logo">
        📖 Library Management System
    </div>

    <ul>

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="books.php">Books</a>
        </li>

        

        <li>
            <a class="active" href="profile.php">Profile</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>

</nav>

<section class="form-section">

    <div class="form-box">

        <h2>My Profile</h2>

        <p>
            <b>Name:</b>
            <?php echo $user['fullname']; ?>
        </p>

        <p>
            <b>Email:</b>
            <?php echo $user['email']; ?>
        </p>

        <p>
            <b>Password:</b>
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

    </div>

</section>

</body>
</html>

