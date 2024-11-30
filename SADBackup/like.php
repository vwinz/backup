<?php
session_start(); // Start the session
include 'partials/connect.php'; // Include your database connection

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $upload_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

    // Check if the user has already liked the post
    $check_like_stmt = $con->prepare("SELECT 1 FROM likes WHERE user_id = ? AND upload_id = ?");
    $check_like_stmt->bind_param("ii", $user_id, $upload_id);
    $check_like_stmt->execute();
    $check_like_stmt->store_result();

    // Get the user who uploaded the post (the post owner)
    $post_owner_stmt = $con->prepare("SELECT user_id FROM uploads WHERE upload_id = ?");
    $post_owner_stmt->bind_param("i", $upload_id);
    $post_owner_stmt->execute();
    $post_owner_result = $post_owner_stmt->get_result();
    $post_owner = $post_owner_result->fetch_assoc();
    $post_owner_stmt->close();

    if ($check_like_stmt->num_rows == 0) {
        // If the user has not liked the post, insert a new like
        $stmt = $con->prepare("INSERT INTO likes (user_id, upload_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $upload_id);
        $stmt->execute();
        $stmt->close();

        // Create a notification for the post owner
        if ($post_owner) {
            $post_owner_id = $post_owner['user_id'];

            // Insert a like notification for the post owner
            $notification_stmt = $con->prepare("INSERT INTO notifications (user_id, upload_id, notification_type) VALUES (?, ?, 'like')");
            $notification_stmt->bind_param("ii", $post_owner_id, $upload_id);
            $notification_stmt->execute();
            $notification_stmt->close();
        }
    } else {
        // If the user has already liked the post, remove the like (unlike)
        $delete_like_stmt = $con->prepare("DELETE FROM likes WHERE user_id = ? AND upload_id = ?");
        $delete_like_stmt->bind_param("ii", $user_id, $upload_id);
        $delete_like_stmt->execute();
        $delete_like_stmt->close();

        // Optionally, you could also remove the notification if the like is undone
        $delete_notification_stmt = $con->prepare("DELETE FROM notifications WHERE user_id = ? AND upload_id = ? AND notification_type = 'like'");
        $delete_notification_stmt->bind_param("ii", $post_owner['user_id'], $upload_id);
        $delete_notification_stmt->execute();
        $delete_notification_stmt->close();
    }

    $check_like_stmt->close();
    header("Location: upload.php"); // Redirect to avoid resubmission
    exit();
} else {
    echo "You must be logged in to like or unlike a post.";
}
?>
