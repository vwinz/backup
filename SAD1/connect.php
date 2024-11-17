<?php
$host = 'localhost';  // Database host
$user = 'root';  // Database username
$password = '';  // Database password
$database = 'sad';  // Database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
