<?php
include 'connect.php'; // Include database connection

$error = null; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists in the database
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();

    if ($username) {
        session_start();
        $_SESSION['username'] = $username; // Store the username in the session
        header("Location: index.php"); // Redirect to index page
        exit();
    } else {
        $error = "Wrong email or password."; // Set the error message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css"> <!-- Link to login CSS -->
</head>
<body>
    <div class="container">
        <form class="form" method="post" action="login.php">
            <h2>Login</h2>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <a href="signup.php" style="color: #007BFF; text-decoration: none;">Create Account? Click here</a>
        </form>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
