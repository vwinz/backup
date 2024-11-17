<?php
session_start();
require_once 'connect.php'; // Include your database connection
require_once 'vendor/autoload.php'; // Include the Twilio SDK (ensure this is the correct path)

use Twilio\Rest\Client;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Twilio credentials
$account_sid = 'your_account_sid'; // Replace with your actual Account SID
$auth_token = 'your_auth_token'; // Replace with your actual Auth Token
$twilio_number = '+18312268305'; // Your Twilio phone number (should be a verified number)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs from the signup form
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $phone_number = $_POST['phone'];

    // Debugging: Output received input values
    echo "<pre>";
    echo "Received Input:\n";
    echo "Email: $email\n";
    echo "Username: $username\n";
    echo "Phone Number: $phone_number\n";
    echo "</pre>";

    // Generate a random verification code between 1000 and 9999
    $verification_code = rand(1000, 9999);
    echo "Generated Verification Code: $verification_code\n"; // Debugging: Output generated code

    // Prepare SQL statement to store user info and verification code in the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone_number, verification_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $phone_number, $verification_code);

    if ($stmt->execute()) {
        // Send SMS with the verification code using Twilio
        $client = new Client($account_sid, $auth_token);

        try {
            $client->messages->create(
                $phone_number, // User's phone number
                ['from' => $twilio_number, 'body' => "Your verification code is: $verification_code"] // Message body
            );
            echo "Verification code sent to $phone_number.";
        } catch (Exception $e) {
            echo "Failed to send verification code: " . $e->getMessage(); // Debugging: Output error message
        }
    } else {
        echo "Failed to register user: " . $stmt->error; // Handle registration errors
    }
}
?>
