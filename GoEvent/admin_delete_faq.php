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

    // Delete the FAQ entry from the database
    $stmt = $conn->prepare("DELETE FROM faq WHERE faq_id = ?");
    $stmt->bind_param("i", $faq_id);

    if ($stmt->execute()) {
        header("Location: admin_manage_faq.php?success=FAQ+entry+deleted+successfully");
    } else {
        header("Location: admin_manage_faq.php?error=Error+deleting+FAQ+entry");
    }
    exit();
}
?>