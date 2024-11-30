<?php

// Remote InfinityFree database connection setup
$servername = "sql206.infinityfree.com"; // Correct remote server name
$username = "if0_37607517";             // Your remote database username
$password = "dK9nyutVLBO4sm";           // Your remote database password
$dbname = "if0_37607517_uploads_db";    // Your remote database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if sensor data is provided
if (isset($_GET['sensor_value'])) {
    $sensor_value = $_GET['sensor_value'];

    // Insert data into the database
    $sql = "INSERT INTO sensor_data (water_level) VALUES ('$sensor_value')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "No sensor data provided.";
}

$conn->close();
?>
