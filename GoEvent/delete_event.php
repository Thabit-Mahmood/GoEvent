<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    // If the user is not logged in or is not an organizer, redirect to the login page
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validate the event ID

if (!$event_id) {
    header("Location: manage_events.php");
    exit();
}

// Fetch event details from the database
$stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    // If the event is not found or not owned by the organizer, redirect to the organizer panel
    header("Location: manage_events.php");
    exit();
}

// Delete the event from the database
$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
if ($stmt->execute()) {
    // Redirect to organizer panel after successful deletion
    header("Location: manage_events.php?message=Event+deleted+successfully");
    exit();
} else {
    // Handle deletion error
    header("Location: manage_events.php?error=Error+deleting+event");
    exit();
}
?>
