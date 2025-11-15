<?php
session_start();
require 'db.php'; // your MySQL connection file

$message = '';

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = 'You have been logged out successfully.';
    // We can add a class for styling success messages later if needed
}


if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND status='active' LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role']; 
            $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";

            header("Location: index.php?login=success");
            exit;
        } else {
            $message = "Invalid username or password!";
        }
    } else {
        $message = "User not found or account inactive!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - TravelGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif;}
        body {
            min-height:100vh;
            background-image: url('img/signup-image.jpg'); /* same bg image */
            background-size:cover;
            background-position:center;
        }
        .container {
            display:flex;
            justify-content:flex-start;
            align-items:center;
            min-height:100vh;
            width:100%;
            padding-left: 10%;
        }

        form {
            width:100%;
            max-width:400px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding:30px;
            border-radius:10px;
            box-shadow:0 4px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        form h2 {text-align:center; margin-bottom:20px; color:#13357B;}
        form input {width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc;}
        form button {width:100%; padding:10px; background:#13357B; color:#fff; border:none; border-radius:5px; cursor:pointer; font-weight:500;}
        form button:hover {background:#1f05e6;}
        .message {color: #d93025; font-weight: 500; margin-bottom:15px; text-align:center;}
        .signup-link {text-align:center; margin-top:10px; color: #fff; font-weight: 500;}
        .signup-link a {color: #fff; text-decoration: underline;}
    </style>
</head>
<body>

<div class="container">
    <form method="post" action="">
        <h2>Login</h2>
        <?php if($message != ''): ?>
            <div class="message <?php echo (isset($_GET['logout'])) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <div class="signup-link">Don’t have an account? <a href="signup.php">Sign Up</a></div>
    </form>
</div>

<style>
    .message.error { color: #d93025; }
    .message.success { 
        color: #28a745; /* A green color for success */
        background-color: rgba(40, 167, 69, 0.1);
        border: 1px solid rgba(40, 167, 69, 0.5);
        padding: 10px;
        border-radius: 5px;
    }
</style>

</body>
</html>
