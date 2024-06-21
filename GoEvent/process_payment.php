<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted payment details
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $security_code = $_POST['security_code'];
    $cardholder_name = $_POST['cardholder_name'];
    $user_id = $_SESSION['user_id'];

    // Perform basic validation
    if (empty($card_number) || empty($expiry_date) || empty($security_code) || empty($cardholder_name)) {
        $_SESSION['payment_error'] = "All fields are required.";
        header("Location: checkout.php");
        exit();
    }

    // Hash and encrypt card details
    $encryption_key = 'your-secret-key'; // Use a strong key and store it securely
    $last4digits = substr(str_replace(' ', '', $card_number), -4);
    $encrypted_card_number = openssl_encrypt($card_number, 'aes-256-cbc', $encryption_key, 0, '1234567890123456');
    $hashed_expiry_date = hash_hmac('sha256', $expiry_date, $encryption_key);
    $hashed_security_code = hash_hmac('sha256', $security_code, $encryption_key);
    $hashed_cardholder_name = hash_hmac('sha256', $cardholder_name, $encryption_key);

    // Fetch cart items from the database
    $stmt = $conn->prepare("SELECT c.event_id, c.quantity, e.ticket_price FROM cart c JOIN events e ON c.event_id = e.event_id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    if (count($cart_items) == 0) {
        $_SESSION['payment_error'] = "Your cart is empty.";
        header("Location: checkout.php");
        exit();
    }

    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['ticket_price'] * $item['quantity'];
    }

    // Insert booking and payment details into booked_events table
    $conn->begin_transaction();
    try {
        foreach ($cart_items as $item) {
            $total_event_price = $item['ticket_price'] * $item['quantity'];
            $stmt = $conn->prepare("INSERT INTO booked_events (user_id, event_id, quantity, total_price, booking_date, card_number_last4, encrypted_card_number, hashed_expiry_date, hashed_security_code, hashed_cardholder_name) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidsssss", $user_id, $item['event_id'], $item['quantity'], $total_event_price, $last4digits, $encrypted_card_number, $hashed_expiry_date, $hashed_security_code, $hashed_cardholder_name);
            $stmt->execute();
        }

        // Clear the cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['payment_success'] = "Payment successful!";
        header("Location: success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['payment_error'] = "Failed to process payment. Please try again.";
        header("Location: checkout.php");
        exit();
    }
} else {
    header("Location: checkout.php");
    exit();
}
?>