<?php
include 'connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    
    // Generate verification code
    $verification_code = random_int(1000, 9999);

    // Save user to database
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone_number, verification_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $password, $email, $phone_number, $verification_code);
    $stmt->execute();
    $stmt->close();

    // Send verification code via Python script
    $command = escapeshellcmd("python send_verification.py $phone_number $verification_code");
    shell_exec($command);

    // Redirect to verify page
    header("Location: verify.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css"> <!-- Link to signup CSS -->
</head>
<body>
    <div class="container">
        <form class="form" method="post" action="signup.php">
            <h2>Sign Up</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
