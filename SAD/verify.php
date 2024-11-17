<?php
include 'connect.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve submitted data
    $username = $_POST['username'];
    $input_code = $_POST['verification_code'];

    // Fetch the stored verification code for this user
    $query = "SELECT verification_code FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        // Compare the submitted code with the stored code
        if ($row['verification_code'] == $input_code) {
            echo "Verification successful!";
            // Update user status or redirect to the login page if necessary
        } else {
            echo "Invalid verification code! Please try again.";
        }
    } else {
        echo "User not found.";
    }
}
?>
