<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<section class="form-section">
    <div class="form-box">

        <h2>Create Account</h2>

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

            <button type="submit">
                Register
            </button>

        </form>

        <p>
            Already have an account? 
            <a href="login.php">Login here</a>
        </p>

    </div>
</section>