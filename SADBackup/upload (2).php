    <?php
    session_start(); // Start the session
    include 'partials/connect.php'; // Include your database connection

    // Check if the user is logged in
    $is_logged_in = isset($_SESSION["user_id"]);
    $user_id = $is_logged_in ? $_SESSION["user_id"] : null;
    $username = $is_logged_in ? $_SESSION["username"] : "Guest"; // Use "Guest" if not logged in

    $notification_count = 0;
if ($is_logged_in) {
    $stmt = $con->prepare("
        SELECT COUNT(DISTINCT upload_id) AS unread_count 
        FROM notifications 
        WHERE user_id = ? AND read_status = 0
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $notification_count = $row['unread_count'];
    }
    $stmt->close();
}


        $notifications = [];
    if ($is_logged_in) {
$stmt = $con->prepare("
 SELECT 
        u.upload_id, 
        u.text, 
        u.timestamp, 
        GROUP_CONCAT(DISTINCT liked_users.username ORDER BY liked_users.username ASC) AS likers
    FROM uploads u
    JOIN likes l ON u.upload_id = l.upload_id
    LEFT JOIN users liked_users ON l.user_id = liked_users.user_id
    WHERE u.user_id = ? 
    GROUP BY u.upload_id
    ORDER BY u.timestamp DESC
");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
    }


    $uploads = [];

    // Fetch all uploads from the database
    $stmt = $con->prepare("
  SELECT 
            u.upload_id, 
            u.image_data, 
            u.text, 
            u.timestamp, 
            COALESCE(likes.like_count, 0) AS likes, 
            COALESCE(user_likes.has_liked, 0) AS has_liked, 
            users.username,
            GROUP_CONCAT(DISTINCT liked_users.username) AS likers
        FROM uploads u
        JOIN users ON u.user_id = users.user_id 
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
        GROUP BY u.upload_id
        ORDER BY u.timestamp DESC
    ");

    $stmt->bind_param("i", $user_id); // Bind the logged-in user ID
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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($is_logged_in) { // Allow upload only if logged in
            $text = isset($_POST['text']) ? trim($_POST['text']) : '';
            $image_data = null; // Initialize image data to null

            // Check if an image was uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
            }

            if ($text || $image_data) { // Only insert if there's either text or image
                $stmt = $con->prepare("
                    INSERT INTO uploads (user_id, image_data, text) 
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iss", $user_id, $image_data, $text);

                if ($stmt->execute()) {
                    header("Location: upload.php"); // Redirect to avoid form resubmission
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Please provide either text or an image to upload.";
            }
        } else {
            echo "You must be logged in to upload images."; // Message for users not logged in
        }
    }

    $con->close(); // Close the database connection
    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload</title>
        <style>
            /* Reset some default styles */

    /* Adjust the profile images positioning */
    /* Container for profile images, positioned in the upper-right corner */
    /* Wrapper to hold both profile images */
    .profile-images-wrapper {
        position: absolute;
        top: 20px;   /* Distance from the top of the page */
        right: 20px; /* Distance from the right edge of the page */
        display: flex; /* Display the images next to each other */
        align-items: center; /* Vertically center the images */
    }

    /* Styling for each profile image */
    .notification-img-left, .profile-img-right {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        margin-left: 10px; /* Adds space between the two images */
    }




    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
    }

    /* Center the container */
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

    /* Card design for each post/upload */
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

    /* Comment button and styling */
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

    /* Comment Section styling */
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

    /* Tooltip for likers */
    .likers-tooltip {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px;
        border-radius: 5px;
        font-size: 14px;
        white-space: nowrap;
        max-width: 200px;
        z-index: 10;
    }

    .like-button:hover + .likers-tooltip {
        display: block;
    }

    /* Form styling */
    .upload-form {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    /* Input fields and button inside the form */
    .upload-form input[type="file"],
    .upload-form textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
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

    /* If the user is not logged in, give a message */
    .upload-form p {
        text-align: center;
        color: #555;
    }

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    padding: 5px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
}
/* Notification card styling */
.notification-card {
    position: absolute;
    top: 70px; /* Position it below the notification icon */
    right: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
    display: none; /* Initially hidden */
    z-index: 1000;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.notifications-header h3 {
    margin: 0;
}

.close-notifications {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #333;
}

.close-notifications:hover {
    color: #f00;
}

.notification-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notification-card ul li {
    background: #f9f9f9;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}


        </style>
    </head>
    <body>

    <div class="profile-images-wrapper">
    <div style="position: relative;">
        <img src="https://i.imgur.com/2BSB45V.png" alt="Profile" class="notification-img-left" id="notification-icon">
        <span class="notification-count" id="notification-count"><?php echo $notification_count; ?></span>
    </div>
    <a href="profile.php">
        <img src="https://i.imgur.com/pxddEpB.png" alt="Profile" class="profile-img-right">
    </a>
</div>




<div id="notification-card" class="notification-card" style="display: none;">
    <div class="notifications-header">
        <h3>Your Notifications</h3>
        <button id="close-notifications" class="close-notifications">&times;</button>
    </div>
    <ul>
        <?php 
        foreach ($notifications as $notification): 
            $likers = explode(",", $notification['likers']);
            $filtered_likers = array_filter($likers, function($liker) use ($logged_in_user) {
                return $liker !== $_SESSION['username'];
            });

            $liker_count = count($filtered_likers);
            $first_liker = reset($filtered_likers) ?: 'Someone';

            if ($liker_count > 0): 
        ?>
                <li>
                    <strong>
                        <?php
                        if ($liker_count === 1) {
                            echo "$first_liker liked your post: \"" . htmlspecialchars($notification['text']) . "\"";
                        } elseif ($liker_count > 1) {
                            echo "$first_liker and " . ($liker_count - 1) . " others liked your post: \"" . htmlspecialchars($notification['text']) . "\"";
                        }
                        ?>
                    </strong>
                    <small>on <?php echo htmlspecialchars($notification['timestamp']); ?></small>
                </li>
        <?php 
            endif;
        endforeach; 
        ?>
    </ul>
</div>




        <div class="container">
            <!-- Upload Form Section -->
            <?php if ($is_logged_in): ?>
                <div class="upload-form">
                    <h2>Upload Your Post</h2>
                    <form method="post" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="image">
                        <textarea name="text" placeholder="Add a description..."></textarea>
                        <button type="submit">Upload</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="upload-form">
                    <p>You must be logged in to upload images. Please <a href="login.php">log in</a> or <a href="signup.php">sign up</a>.</p>
                </div>
            <?php endif; ?>

            <h2>All Uploads</h2>
            <?php if (!empty($uploads)): ?>
                <?php foreach ($uploads as $upload): ?>
                    <div class="upload-item">
                        <strong><?php echo htmlspecialchars($upload['username']); ?>:</strong>
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
                                <!-- Tooltip for likers -->
                                <div class="likers-tooltip"></div>
                            </form>

                            <!-- Comment Button -->
                            <button onclick="toggleComments(<?php echo $upload['upload_id']; ?>)">
                                💬 Comment (<?php echo count($upload['comments']); ?>)
                            </button>
                        </div>

                        <!-- Comment Section -->
                        <div id="comment-<?php echo $upload['upload_id']; ?>" class="comment-section" style="display: none;">
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
                <p>No uploads found.</p>
            <?php endif; ?>
        </div>

        <script>
        function toggleComments(uploadId) {
            const commentSection = document.getElementById('comment-' + uploadId);
            // Toggle visibility
            if (commentSection.style.display === 'none' || commentSection.style.display === '') {
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }
        }

        
        document.getElementById('notification-icon').addEventListener('click', function() {
            var notificationsContainer = document.querySelector('.notifications-container');
            if (notificationsContainer.style.display === 'block') {
                notificationsContainer.style.display = 'none';
            } else {
                notificationsContainer.style.display = 'block';
            }
        });

// Function to fetch the updated notification count
function fetchNotificationCount() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            const notificationCountEl = document.getElementById('notification-count');
            
            if (count > 0) {
                notificationCountEl.textContent = count; // Show the count
                notificationCountEl.style.display = 'block'; // Ensure the badge is visible
            } else {
                notificationCountEl.style.display = 'none'; // Hide the badge when count is 0
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}

// Auto-refresh notification count every 30 seconds
setInterval(fetchNotificationCount, 30000);

// Mark notifications as read
document.getElementById('notification-icon').addEventListener('click', function() {
    fetch('mark_notifications_read.php').then(() => {
        const notificationCountEl = document.getElementById('notification-count');
        notificationCountEl.style.display = 'none'; // Hide the badge after marking as read
    });
});

document.getElementById('notification-icon').addEventListener('click', function() {
    var notificationCard = document.getElementById('notification-card');
    // Toggle visibility of the notification card
    if (notificationCard.style.display === 'none' || notificationCard.style.display === '') {
        notificationCard.style.display = 'block';
    } else {
        notificationCard.style.display = 'none';
    }
});

// Close the notification card when clicking the close button
document.getElementById('close-notifications').addEventListener('click', function() {
    document.getElementById('notification-card').style.display = 'none';
});


        </script>
    </body>
    </html>