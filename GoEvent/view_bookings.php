<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    // If the user is not logged in or is not an organizer, redirect to the login page
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];

// Fetch bookings for the events organized by the logged-in organizer
$stmt = $conn->prepare("SELECT b.booking_id, b.user_id, b.event_id, b.quantity, b.total_price, b.booking_date, e.event_name, u.username, u.email 
                        FROM booked_events b 
                        JOIN events e ON b.event_id = e.event_id 
                        JOIN users u ON b.user_id = u.user_id 
                        WHERE e.organizer_id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="bookings-container">
        <h1>Your Event Bookings</h1>
        <?php if (count($bookings) > 0): ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Attendee</th>
                        <th>Email</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Booking Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                            <td><?php echo $booking['quantity']; ?></td>
                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings available at the moment.</p>
        <?php endif; ?>
    </main>
</body>

</html>
