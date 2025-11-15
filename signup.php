<?php
session_start();
require 'db.php'; // your MySQL connection file

$message = '';

if(isset($_POST['signup'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $country = $_POST['country'];
    $username = $_POST['username'];
    // Securely hash the password before storing it
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $message = "Username or Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, gender, email, country, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstname, $lastname, $gender, $email, $country, $username, $password);
        if($stmt->execute()){
            // Auto-login the user and set success message
            $_SESSION['user'] = $username;
            $_SESSION['success_message'] = "Welcome to TravelGuide, " . htmlspecialchars($username) . "! Your account is ready.";
            header("Location: index.php?signup=success");
            exit;
        } else {
            $message = "Error! Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - TravelGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif;}
        :root{
          --bg:#1e293b;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(15, 23, 36, 0.6);
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif; }
        body {
            min-height:100vh;
            background-image: url('img/signup-image.jpg');
            background-image: linear-gradient(rgba(15, 23, 36, 0.5), rgba(15, 23, 36, 0.8)), url('img/signup-image.jpg');
            background-size:cover;
            background-position:center;
            color: #e6eef8;
        }
        .container {
            display:flex;
            justify-content:flex-start;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            width:100%;
            padding-left: 10%;
            padding: 40px 20px;
        }

        form {
            width:100%;
            max-width:400px;
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent white */
            backdrop-filter: blur(10px); /* The blur effect */
            -webkit-backdrop-filter: blur(10px); /* For Safari */
            padding:30px;
            border-radius:10px;
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding:40px;
            border-radius:12px;
            box-shadow:0 4px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        form h2 {text-align:center; margin-bottom:20px; color:#13357B;}
        form input, form select {width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc;}
        form button {width:100%; padding:10px; background:#13357B; color:#fff; border:none; border-radius:5px; cursor:pointer; font-weight:500;}
        form button:hover {background:#1f05e6;}
        .message {color: #d93025; font-weight: 500; margin-bottom:15px; text-align:center;}
        .login-link {text-align:center; margin-top:10px; color: #fff; font-weight: 500;}
        .login-link a {color: #fff; text-decoration: underline;}
        form h2 {text-align:center; margin-bottom:25px; color:#fff; font-size: 28px;}
        form input, form select {width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #fff; font-size: 16px;}
        form input::placeholder, form select { color: var(--muted); }
        form input:focus, form select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        form select option { background-color: var(--card); color: #e6eef8; }
        form button {width:100%; padding:12px; background:var(--accent); color:#041022; border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size: 16px; transition: background-color 0.3s;}
        form button:hover {background:#4fbfff;}
        .message {color: #f87171; background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 500; margin-bottom:15px; text-align:center; padding: 10px; border-radius: 8px;}
        .login-link {text-align:center; margin-top:20px; color: var(--muted); font-weight: 500;}
        .login-link a {color: var(--accent); text-decoration: none; font-weight: 600;}
        .login-link a:hover {text-decoration: underline;}
    </style>
</head>
<body>

<div class="container">
    <form method="post" action="">
        <h2>Create Account</h2>
        <?php if($message != '') echo '<div class="message">'.$message.'</div>'; ?>
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="country" placeholder="Country" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="signup">Sign Up</button>
        <div class="login-link">Already have an account? <a href="login.php">Login</a></div>
    </form>
</div>

</body>
</html>
