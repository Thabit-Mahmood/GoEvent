<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];

    // Fetch the organizer_id for the event
    $stmt = $conn->prepare("SELECT organizer_id, event_name FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    $organizer_id = $event['organizer_id'];
    $event_name = $event['event_name'];

    // Delete the event from the database
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    // Insert notification for the organizer
    $notification_text = "Your event '$event_name' has been rejected.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_text) VALUES (?, ?)");
    $stmt->bind_param("is", $organizer_id, $notification_text);
    $stmt->execute();

    // Redirect back to the admin panel
    header("Location: adminpanel.php");
    exit();
}
?>
