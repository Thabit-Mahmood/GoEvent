<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['id'] ?? null;

if (!$notificationId) {
    http_response_code(400);
    echo json_encode(['error' => 'No notification ID provided']);
    exit();
}

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
$stmt->bind_param("ii", $notificationId, $user_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>
