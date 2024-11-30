<?php
session_start();
include 'partials/connect.php'; // Include database connection

$error = null; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch user by email
    $stmt = $con->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $hashed_password);
    $stmt->fetch();

    // Debugging output
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "User ID: " . htmlspecialchars($user_id) . "<br>";
    echo "Hashed Password: " . htmlspecialchars($hashed_password) . "<br>";

    // Verify password
    if ($user_id && password_verify($password, $hashed_password)) {
        // Password matches, start session for the user
        $_SESSION['user_id'] = $user_id; // Store the user ID in the session
        $_SESSION['username'] = $username; // Store the username in the session
        header("Location: index.php"); // Redirect to index page
        exit();
    } else {
        // Set error message if email or password is incorrect
        $error = "Wrong email or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style> 
    /* login.css */
 body {
            background: url('https://i.imgur.com/kLDKeZP.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(51, 51, 51, 0.8); /* Dark background with 80% opacity */
            z-index: -1;
        }
.container {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.1);
    padding: 2rem;
    border-radius: 15px;
    width: 300px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.form h2 {
    text-align: center;
    color: #333333;
}

.form input {
    width: 100%;
    padding: 10px;
    margin: 7px 0;
    border: none;
    border-radius: 5px;
    background: rgba(176, 174, 174, 0.2);
    color: #2d2b2b;
}

.form input::placeholder {
    color: #fff;
}

.form button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    background-color: #333333;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    margin-top: 20%;
}

.form button:hover {
    background-color: #4e4d4d;
}


    </style>
</head>
<body>
    <div class="container">
        <form class="form" method="post" action="login.php">
            <h2 style = "color: #fff;">Login</h2>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
              <a href="forgot.php" style="color: #fff; text-decoration: none;">Forgot Password?</a>
            <button type="submit">Login</button>
            <a href="signup.php" style="color: #fff; text-decoration: none;">Create Account? Click here</a>
        </form>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
