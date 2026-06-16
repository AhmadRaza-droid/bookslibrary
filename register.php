<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }
        .form-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        .form-box h2 {
            text-align: center;
            color: #0b1f3a;
            margin-bottom: 25px;
        }
        .form-box input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
            box-sizing: border-box;
        }
        .form-box input:focus {
            outline: none;
            border-color: #0b1f3a;
        }
        .form-box button {
            width: 100%;
            padding: 12px;
            background: #0b1f3a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .form-box button:hover {
            background: #1a3a5c;
        }
        .form-box p {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .form-box a {
            color: #0b1f3a;
            text-decoration: none;
            font-weight: bold;
        }
        .form-box a:hover {
            text-decoration: underline;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="logo">📖 Library Management System</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="books.php">Books</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a class="active" href="register.php">Register</a></li>
    </ul>
</nav>

<section class="form-section">
    <div class="form-box">

        <h2>📝 Create Account</h2>

        <?php
        session_start();
        if(isset($_SESSION['register_error'])){
            echo '<div class="error-msg">' . $_SESSION['register_error'] . '</div>';
            unset($_SESSION['register_error']);
        }
        if(isset($_SESSION['register_success'])){
            echo '<div class="success-msg">' . $_SESSION['register_success'] . '</div>';
            unset($_SESSION['register_success']);
        }
        ?>

        <form method="POST" action="register_process.php">

            <input type="text" 
                   name="fullname"
                   placeholder="Enter Full Name" 
                   required>

            <input type="email" 
                   name="email"
                   placeholder="Enter Email" 
                   required>

            <input type="password" 
                   name="password"
                   placeholder="Enter Password" 
                   required>

            <input type="password" 
                   name="confirm_password"
                   placeholder="Confirm Password" 
                   required>

            <button type="submit" name="register">
                Register
            </button>

        </form>

        <p>
            Already have an account? 
            <a href="login.php">Login here</a>
        </p>

    </div>
</section>

</body>
</html>