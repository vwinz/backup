<?php
session_start(); // Start the session
include 'connect.php'; // Include database connection

$error = null; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_code = $_POST['verification_code'];

    // Retrieve the phone number from the session
    $phone_number = $_SESSION['phone_number'] ?? null;

    // Check the verification code in the database
    $stmt = $conn->prepare("SELECT verification_code FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $stmt->bind_result($verification_code);
    $stmt->fetch();
    $stmt->close();

    // Verify the entered code against the one in the database
    if ($verification_code && strval($verification_code) === $input_code) {
        // Code is correct, redirect to login.php
        header("Location: login.php?verification=success");
        exit();
    } else {
        // Code is incorrect, show an error message
        $error = "Verification failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Phone Number</title>
    <link rel="stylesheet" href="signup.css"> <!-- Link to signup CSS -->
</head>
<body>
    <div class="container">
        <form class="form" method="post" action="verify.php">
            <h2>Verify Phone Number</h2>
            <input type="text" name="verification_code" placeholder="Verification Code" required>
            <button type="submit">Verify</button>
        </form>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
