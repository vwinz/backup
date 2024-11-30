<?php
// Enable error reporting for debugging
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection
include 'partials/connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve POST data
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    
    // Generate verification code
    $verification_code = random_int(1000, 9999);

    // Prepare the SQL statement
    $stmt = $con->prepare("INSERT INTO users (username, password, email, phone_number, verification_code) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die("Preparation failed: " . $con->error); // Print any error in preparation
    }

    // Bind parameters
    $stmt->bind_param("ssssi", $username, $password, $email, $phone_number, $verification_code);
    
    // Execute the statement
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error); // Print any error in execution
    }

    // Store the phone number in session for verification
    $_SESSION['phone_number'] = $phone_number;

    // Send the verification code via Vonage API
    $api_key = "01138807"; // Your Vonage API Key
    $api_secret = "hyLfUlsxqs8ldB0B"; // Your Vonage API Secret

    // Set up the data for the API call
    $url = 'https://rest.nexmo.com/v1/messages';
    $data = [
        'from' => 'Vonage APIs',
        'to' => $phone_number,
        'text' => "Your verification code is: $verification_code",
    ];
    
    // Create a JSON payload
    $json_data = json_encode($data);
    
    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$api_key:$api_secret")
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    
    // Execute the cURL request
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die('cURL error: ' . curl_error($ch)); // Print cURL error if it occurs
    }
    curl_close($ch);

    // Optionally check the response for success (you can expand this based on your needs)
    $response_data = json_decode($response, true);
    if (isset($response_data['messages'][0]['status']) && $response_data['messages'][0]['status'] == '0') {
        // Message sent successfully
    } else {
        echo "Error sending message: " . $response_data['messages'][0]['error-text'];
    }

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
