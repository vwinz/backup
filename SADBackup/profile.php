<?php
session_start(); // Start the session
include 'partials/connect.php'; // Include your database connection

// Check if the user is logged in
$is_logged_in = isset($_SESSION["user_id"]);
$user_id = $is_logged_in ? $_SESSION["user_id"] : null;
$username = $is_logged_in ? $_SESSION["username"] : "Guest"; // Use "Guest" if not logged in

if (!$is_logged_in) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$uploads = [];

// Fetch the logged-in user's uploads
$stmt = $con->prepare("
    SELECT 
        u.upload_id, 
        u.image_data, 
        u.text, 
        u.timestamp, 
        COALESCE(likes.like_count, 0) AS likes, 
        COALESCE(user_likes.has_liked, 0) AS has_liked, 
        GROUP_CONCAT(DISTINCT liked_users.username) AS likers
    FROM uploads u
    LEFT JOIN (
        SELECT upload_id, COUNT(*) AS like_count 
        FROM likes 
        GROUP BY upload_id
    ) likes ON u.upload_id = likes.upload_id
    LEFT JOIN (
        SELECT upload_id, 1 AS has_liked
        FROM likes 
        WHERE user_id = ?
    ) user_likes ON u.upload_id = user_likes.upload_id
    LEFT JOIN likes l ON u.upload_id = l.upload_id
    LEFT JOIN users liked_users ON liked_users.user_id = l.user_id
    WHERE u.user_id = ?
    GROUP BY u.upload_id
    ORDER BY u.timestamp DESC
");
$stmt->bind_param("ii", $user_id, $user_id); // Bind the logged-in user ID
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Fetch comments for each upload
    $comment_stmt = $con->prepare("
        SELECT c.text, c.timestamp, u.username 
        FROM comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.upload_id = ? 
        ORDER BY c.timestamp ASC
    ");
    $comment_stmt->bind_param("i", $row['upload_id']);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();
    $row['comments'] = $comment_result->fetch_all(MYSQLI_ASSOC);
    $comment_stmt->close();

    $uploads[] = $row;
}

$stmt->close();

$con->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <style>
        /* Styles (same as your upload page styles) */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .upload-item {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .upload-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .upload-item p {
            font-size: 16px;
            margin-top: 10px;
        }

        .upload-item .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        button {
            background-color: #333333;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #555555;
        }

        .comment-section {
            margin-top: 15px;
        }

        .comment-section textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 5px;
        }

        .comment-section ul {
            list-style: none;
            padding: 0;
        }

        .comment-section ul li {
            background: #f4f4f4;
            margin-bottom: 5px;
            padding: 8px;
            border-radius: 4px;
        }

        .upload-form {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .upload-form button {
            background-color: #333333;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .upload-form button:hover {
            background-color: #555555;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Your Posts</h2>

        <?php if (!empty($uploads)): ?>
            <?php foreach ($uploads as $upload): ?>
                <div class="upload-item">
                    <strong><?php echo htmlspecialchars($username); ?>:</strong>
                    <?php if ($upload['image_data']): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($upload['image_data']); ?>" alt="Uploaded Image">
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($upload['text']); ?></p>
                    <small>Uploaded on: <?php echo htmlspecialchars($upload['timestamp']); ?></small>

                    <div class="actions">
                        <!-- Like Button -->
                        <form method="post" action="like.php">
                            <input type="hidden" name="post_id" value="<?php echo $upload['upload_id']; ?>"> 
                            <button 
                                type="submit" 
                                class="like-button"
                                data-likers="<?php echo htmlspecialchars($upload['likers']); ?>">
                                <?php if ($upload['has_liked']): ?>
                                    💖 Liked (<?php echo $upload['likes']; ?>)
                                <?php else: ?>
                                    ❤️ Like (<?php echo $upload['likes']; ?>)
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>

                    <!-- Comment Section -->
                    <div id="comment-<?php echo $upload['upload_id']; ?>" class="comment-section" style="display: block;">
                        <form method="post" action="comment.php">
                            <textarea name="comment" placeholder="Write a comment..."></textarea>
                            <input type="hidden" name="post_id" value="<?php echo $upload['upload_id']; ?>"> 
                            <button type="submit">Post Comment</button>
                        </form>
                        <ul>
                            <?php if (!empty($upload['comments'])): ?>
                                <?php foreach ($upload['comments'] as $comment): ?>
                                    <li><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['text']); ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have not uploaded any posts yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
