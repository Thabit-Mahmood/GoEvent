<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items from the database
$stmt = $conn->prepare("SELECT c.event_id, c.quantity, e.event_name, e.ticket_price 
                        FROM cart c 
                        JOIN events e ON c.event_id = e.event_id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (count($cart_items) == 0) {
    // If the cart is empty, redirect to the cart page
    header("Location: cart.php");
    exit();
}

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['ticket_price'] * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the checkout
    $conn->begin_transaction();

    try {
        foreach ($cart_items as $item) {
            // Insert the order into the booked_events table
            $stmt = $conn->prepare("INSERT INTO booked_events (user_id, event_id, quantity, total_price, booking_date) VALUES (?, ?, ?, ?, NOW())");
            $total_item_price = $item['ticket_price'] * $item['quantity'];
            $stmt->bind_param("iiid", $user_id, $item['event_id'], $item['quantity'], $total_item_price);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        // Clear the cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $conn->commit();
        $success = "Checkout completed successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "An error occurred during checkout: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="checkout-container">
        <h1>Checkout</h1>
        <?php if ($error) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success) : ?>
            <p class="success"><?php echo $success; ?></p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['event_name']); ?></td>
                            <td>$<?php echo number_format($item['ticket_price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['ticket_price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h2>Total: $<?php echo number_format($total_price, 2); ?></h2>
            <form action="checkout.php" method="POST">
                <button type="submit">Confirm Checkout</button>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>