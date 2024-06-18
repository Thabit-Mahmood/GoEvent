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

    // Update event to approved
    $stmt = $conn->prepare("UPDATE events SET pending_approval = 0 WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    // Fetch the organizer_id and event_name for the event
    $stmt = $conn->prepare("SELECT organizer_id, event_name FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    $organizer_id = $event['organizer_id'];
    $event_name = $event['event_name'];

    // Insert notification for the organizer
    $notification_text_organizer = "Your event '$event_name' has been approved.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_text) VALUES (?, ?)");
    $stmt->bind_param("is", $organizer_id, $notification_text_organizer);
    $stmt->execute();

    // Insert notifications for all regular users
    $notification_text_regular = "A new event '$event_name' is now available.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_text) SELECT user_id, ? FROM users WHERE user_type = 'regular'");
    $stmt->bind_param("s", $notification_text_regular);
    $stmt->execute();

    // Redirect back to the admin panel
    header("Location: adminpanel.php");
    exit();
}
?>