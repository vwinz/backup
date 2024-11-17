<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log debug information
function debug_log($message) {
    $logFile = 'debug.log';
    $currentDate = date('Y-m-d H:i:s');
    $formattedMessage = "[{$currentDate}] - {$message}\n";
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "login";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    debug_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    debug_log("Form submitted with action: " . $action);

    if ($action == 'register') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        debug_log("Executing SQL: " . $sql);

        if ($conn->query($sql) === TRUE) {
            echo "Registration successful!";
            debug_log("Registration successful for username: " . $username);
        } else {
            echo "Error: " . $conn->error;
            debug_log("Error during registration: " . $conn->error);
        }
    } elseif ($action == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username'";
        debug_log("Executing SQL: " . $sql);

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                echo "Login successful!";
                debug_log("Login successful for username: " . $username);
            } else {
                echo "Invalid password.";
                debug_log("Invalid password for username: " . $username);
            }
        } else {
            echo "No user found with that username.";
            debug_log("No user found with username: " . $username);
        }
    } else {
        echo "Invalid action.";
        debug_log("Invalid action: " . $action);
    }
}

// Close the database connection
$conn->close();
?>
