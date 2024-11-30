<?php
session_start(); // Start the session
include 'partials/connect.php'; // Include your database connection

// Check if the user is logged in
$is_logged_in = isset($_SESSION["user_id"]);
$user_id = $is_logged_in ? $_SESSION["user_id"] : null;
$username = $is_logged_in ? $_SESSION["username"] : "Guest"; // Use "Guest" if not logged in

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($is_logged_in) { // Only allow comment if user is logged in
        // Get the comment text and upload ID from the form
        $comment_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $upload_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        if ($comment_text && $upload_id) { // Ensure both fields are provided
            // Prepare the SQL to insert the comment into the database
            $stmt = $con->prepare("
                INSERT INTO comments (text, user_id, upload_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("sii", $comment_text, $user_id, $upload_id);

            if ($stmt->execute()) {
                header("Location: upload.php"); // Redirect back to the upload page after comment is posted
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Please provide a comment.";
        }
    } else {
        echo "You must be logged in to comment.";
    }
}

$con->close(); // Close the database connection
?>
