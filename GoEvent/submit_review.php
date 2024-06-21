<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted review details
    $event_id = $_POST['event_id'];
    $user_id = $_POST['user_id'];
    $review_text = $_POST['review_text'];
    $rating = $_POST['rating'];

    // Perform basic validation
    if (empty($review_text) || empty($rating)) {
        $_SESSION['review_error'] = "All fields are required.";
        header("Location: booked_events.php");
        exit();
    }

    // Insert review details into event_reviews table
    $stmt = $conn->prepare("INSERT INTO event_reviews (event_id, user_id, review_text, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisi", $event_id, $user_id, $review_text, $rating);

    if ($stmt->execute()) {
        $_SESSION['review_success'] = "Review submitted successfully!";
    } else {
        $_SESSION['review_error'] = "Failed to submit review. Please try again.";
    }

    header("Location: booked_events.php");
    exit();
}
?>
