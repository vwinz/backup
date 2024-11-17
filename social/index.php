<?php
session_start();
include 'connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["username"];

// Fetch all uploads
$sql = "SELECT users.username, uploads.image_data, uploads.text, uploads.timestamp 
        FROM uploads 
        JOIN users ON uploads.user_id = users.user_id 
        ORDER BY uploads.timestamp DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
    
    <div id="upload-section">
        <h2>Upload an Image</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <textarea name="text" placeholder="Write something..." required></textarea>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div id="display-section">
        <h2>Uploaded Images and Text</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="upload-item">';
                echo '<strong>' . htmlspecialchars($row['username']) . ':</strong>';
                echo '<img src="data:image/jpeg;base64,' . base64_encode($row['image_data']) . '" alt="Uploaded Image">';
                echo '<p>' . htmlspecialchars($row['text']) . '</p>';
                echo '<small>Uploaded on: ' . $row['timestamp'] . '</small>';
                echo '</div>';
            }
        } else {
            echo "<p>No uploads yet.</p>";
        }
        $con->close();
        ?>
    </div>
</body>
</html>
