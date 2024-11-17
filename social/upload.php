<?php
session_start();
include 'connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $text = $con->real_escape_string($_POST['text']);

    // Check if file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        
        $sql = "INSERT INTO uploads (user_id, image_data, text) VALUES ('$user_id', '$image_data', '$text')";
        if ($con->query($sql) === TRUE) {
            echo "Uploaded successfully!";
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $con->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}
?>
