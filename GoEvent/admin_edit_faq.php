<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faq_id = $_POST['faq_id'];
    $answer_text = $_POST['answer_text'];

    // Update the FAQ entry in the database
    $stmt = $conn->prepare("UPDATE faq SET answer_text = ? WHERE faq_id = ?");
    $stmt->bind_param("si", $answer_text, $faq_id);

    if ($stmt->execute()) {
        header("Location: admin_manage_faq.php?success=FAQ+entry+updated+successfully");
    } else {
        header("Location: admin_manage_faq.php?error=Error+updating+FAQ+entry");
    }
    exit();
}
?>
