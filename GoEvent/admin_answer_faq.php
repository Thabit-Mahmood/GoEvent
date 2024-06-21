<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];
    $answer_text = $_POST['answer_text'];

    // Insert the answer into the faq table
    $stmt = $conn->prepare("INSERT INTO faq (question_id, answer_text) VALUES (?, ?)");
    $stmt->bind_param("is", $question_id, $answer_text);
    $stmt->execute();

    // Fetch the user_id associated with the question
    $stmt = $conn->prepare("SELECT user_id FROM user_questions WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();

    $user_id = $question['user_id'];

    // Insert notification for the user
    $notification_text = "Your question has been answered. Check the FAQ section.";
    $notification_link = "faq.php";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_text, link) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $notification_text, $notification_link);
    $stmt->execute();

    // Redirect back to the manage FAQ page
    header("Location: admin_manage_faq.php");
    exit();
}
?>
