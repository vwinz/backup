<?php
// update_password.php

session_start();

// Database connection
$servername = "localhost";
$username = "root";   // Your database username
$password = "";       // Your database password
$dbname = "account";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User exists, proceed to update password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_hashed_password, $email);
        if ($stmt->execute()) {
            echo "Password updated successfully!";
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "Email not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    // Handle non-POST requests
    echo "Invalid request method!";
}
?>
