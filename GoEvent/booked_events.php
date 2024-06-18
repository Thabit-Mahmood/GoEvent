<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch booked events for the logged-in user
$stmt = $conn->prepare("SELECT b.booking_id, e.event_id, e.event_name, e.event_date, b.quantity, b.total_price, b.booking_date 
                        FROM booked_events b 
                        JOIN events e ON b.event_id = e.event_id 
                        WHERE b.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booked_events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booked Events - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="booked-events-container">
        <h1>Your Booked Events</h1>
        <?php if (count($booked_events) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Event Date</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Booking Date</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booked_events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td><?php echo $event['quantity']; ?></td>
                            <td>$<?php echo number_format($event['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($event['booking_date']); ?></td>
                            <td><a href="provide_feedback.php?event_id=<?php echo $event['event_id']; ?>">Provide Feedback</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no booked events.</p>
        <?php endif; ?>
    </main>
</body>

</html>
