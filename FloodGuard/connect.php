<?php
$servername = "sql206.infinityfree.com";
$username = "if0_37607517";
$password = "dK9nyutVLBO4sm";
$dbname = "if0_37607517_uploads_db";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error); // This will show the exact error
} else {
    echo "Connected successfully"; // For debugging
}
?>