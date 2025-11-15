<?php
session_start();
require '../db.php'; // Adjust path to go up one level to the root

$message = '';

// If an admin is already logged in, redirect them to the dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare to fetch the user, but only if they have the 'admin' role
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=? AND role='admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify the password against the hashed password in the database
        if(password_verify($password, $user['password'])) { 
            // Set session variables for the admin
            $_SESSION['user'] = $user['username'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_role'] = 'admin';
            
            // Redirect to the admin dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Incorrect password!";
        }
    } else {
        // This message is intentionally generic for security
        $message = "Invalid credentials or not an admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Travel Tales</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0f1724;--card:#0b1220;--muted:#9aa4b2;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03);}
        * {margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat', sans-serif;}
        body {
            min-height:100vh;
            background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);
            color: #e6eef8;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            width:100%;
            max-width:400px;
            background: var(--card);
            padding:40px;
            border-radius:12px;
            box-shadow:0 8px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
        form h2 {text-align:center; margin-bottom:25px; color:#fff; font-weight: 700; font-size: 24px;}
        form input {width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background: var(--glass); color: #e6eef8; font-size: 16px;}
        form input::placeholder { color: #9aa4b2; }
        form input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        form button {width:100%; padding:12px; background:var(--accent); color:var(--card); border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size: 16px; margin-top: 10px;}
        form button:hover {opacity: 0.9;}
        .message {color: #f0ad4e; font-weight: 500; margin-bottom:15px; text-align:center; background: rgba(240, 173, 78, 0.1); padding: 8px; border-radius: 5px;}
        .back-link {text-align:center; margin-top:20px; font-size: 14px;}
        .back-link a {color: #9aa4b2; text-decoration: none;}
        .back-link a:hover {color: #fff;}
    </style>
</head>
<body>

<form method="post" action="">
    <h2>Admin Panel Login</h2>
    <?php if($message != '') echo '<div class="message">'.$message.'</div>'; ?>
    <input type="text" name="username" placeholder="Admin Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
    <div class="back-link"><a href="../index.php">&larr; Back to Main Site</a></div>
</form>

</body>
</html>