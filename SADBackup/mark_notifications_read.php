<?php
session_start();
include 'partials/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("UPDATE notifications SET read_status = 1 WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

header("Location: upload.php");
exit();
?>
