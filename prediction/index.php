<?php
header('Content-Type: application/json'); // Declare the response type as JSON

// Database connection
$host = 'localhost';  // Replace with your database host
$user = 'root';       // Replace with your database username
$password = '';       // Replace with your database password
$dbname = 'water_level'; // Replace with your database name

// Create a connection to the MySQL database
$conn = new mysqli($host, $user, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// SQL query to fetch all water level data
$sql = "SELECT water_level, timestamp FROM sensor_data ORDER BY id ASC";  // Update table name if necessary
$result = $conn->query($sql);

// Check if any data was returned
$allData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Push the water level data and timestamp into the array
        $allData[] = [
            'water_level' => $row['water_level'],
            'timestamp' => $row['timestamp'] // Ensure this column exists and is formatted as 'Y-m-d H:i:s'
        ];
    }
    // Return the historical data as a JSON response
    echo json_encode($allData);
} else {
    echo json_encode([]);  // If no data found, return an empty array
}

// Close the database connection
$conn->close();
?>
