<?php
session_start(); // Start the session at the very top
include 'partials/connect.php'; // Database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = null; // Initialize error variable

// Debugging: Output the phone number stored in session
echo "Phone number in session: " . ($_SESSION['phone_number'] ?? "Not set") . "<br>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_code = $_POST['verification_code'];
    $phone_number = $_SESSION['phone_number'] ?? null;

    if (!$phone_number) {
        die("No phone number found in session.");
    }

    // Check the verification code in the database
    $stmt = $con->prepare("SELECT verification_code FROM users WHERE phone_number = ?");
    if (!$stmt) {
        die("Preparation failed: " . $con->error);
    }
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $stmt->bind_result($verification_code);
    $stmt->fetch();
    $stmt->close();

    // Verify the entered code
    if ($verification_code && strval($verification_code) === $input_code) {
        header("Location: login.php?verification=success");
        exit();
    } else {
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
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="container">
        <form class="form" method="post" action="verify.php">
            <h2>Verify Phone Number</h2>
            <input type="text" name="verification_code" placeholder="Verification Code" required>
            <button type="submit">Verify</button>
        </form>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
