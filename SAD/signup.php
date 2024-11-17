<?php
session_start();
require_once 'connect.php';  // Database connection file
require_once 'vendor/autoload.php'; // Twilio SDK

use Twilio\Rest\Client;

// Twilio API credentials
$account_sid = 'your_account_sid';
$auth_token = 'your_auth_token';
$twilio_number = 'your_twilio_phone_number';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    
    // Generate a random 6-digit verification code
    $verification_code = rand(100000, 999999);
    
    // Store user info and verification code in session
    $_SESSION['signup_data'] = [
        'email' => $email,
        'username' => $username,
        'password' => $password,
        'phone' => $phone,
        'verification_code' => $verification_code
    ];

    // Initialize Twilio client
    $client = new Client($account_sid, $auth_token);

    // Send SMS with verification code
    try {
        $client->messages->create(
            $phone,
            ['from' => $twilio_number, 'body' => "Your verification code is: $verification_code"]
        );
        echo "Verification code sent to $phone.";
    } catch (Exception $e) {
        echo "Failed to send verification code: " . $e->getMessage();
    }
}
?>
