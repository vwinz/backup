<?php
// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Local database connection setup
$local_servername = "localhost"; // Use localhost if running locally
$local_username = "root"; // Local MySQL username
$local_password = ""; // Local MySQL password (leave empty if none)
$local_dbname = "water_level"; // Replace with your local database name

// Remote InfinityFree database connection setup
$remote_servername = "sql206.infinityfree.com";
$remote_username = "if0_37607517";
$remote_password = "dK9nyutVLBO4sm";
$remote_dbname = "if0_37607517_uploads_db";

// Connect to the local database
$conn = new mysqli($local_servername, $local_username, $local_password, $local_dbname);
if ($conn->connect_error) {
    die("Local connection failed: " . $conn->connect_error);
} else {
    echo "Connected to local database successfully. <br>";
}

// Connect to the InfinityFree database
$remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);
if ($remote_conn->connect_error) {
    die("Remote connection failed: " . $remote_conn->connect_error);
} else {
    echo "Connected to InfinityFree database successfully. <br>";
}

// Function to transfer data to InfinityFree database
function transferDataToInfinityFree($water_level) {
    global $remote_conn;

    // Check if the remote connection is valid
    if (isset($remote_conn) && $remote_conn instanceof mysqli && !$remote_conn->connect_error) {
        $stmt = $remote_conn->prepare("INSERT INTO sensor_data (water_level) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("i", $water_level);
            if ($stmt->execute()) {
                echo "Data transferred successfully: " . $water_level . "<br>";
            } else {
                echo "Error executing statement: " . $stmt->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "Failed to prepare statement: " . $remote_conn->error . "<br>";
        }
    } else {
        echo "Invalid remote database connection.<br>";
    }
}

// Fetch the latest data from the local database
$result = $conn->query("SELECT water_level FROM sensor_data ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $latestWaterLevel = $row['water_level'];
    transferDataToInfinityFree($latestWaterLevel);
} else {
    echo "No data found in local database.<br>";
}

// Close both connections
if (isset($remote_conn) && $remote_conn instanceof mysqli) {
    $remote_conn->close();
}
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
