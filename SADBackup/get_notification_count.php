<?php
session_start();
include 'partials/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("
    SELECT COUNT(DISTINCT upload_id) AS unread_count 
    FROM notifications 
    WHERE user_id = ? AND read_status = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['unread_count'] ?? 0;
$stmt->close();

echo json_encode(['count' => $count]);
?>
