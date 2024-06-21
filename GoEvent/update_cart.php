<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $event_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
}
?>