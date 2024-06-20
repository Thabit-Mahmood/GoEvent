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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="manage-events-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="organizer-event-container">
        <div class="welcome">
            <h1 class="myevents-heading">Your Event Bookings</h1>
        </div>
        <section class="organizer-section">
            <?php if (count($bookings) > 0): ?>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Name <i class="fa fa-calendar-alt"></i></th>
                            <th>Attendee <i class="fa fa-user"></i></th>
                            <th>Email <i class="fa fa-envelope"></i></th>
                            <th>Quantity <i class="fa fa-ticket-alt"></i></th>
                            <th>Total Price <i class="fa fa-dollar-sign"></i></th>
                            <th>Booking Date <i class="fa fa-clock"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo $booking['quantity']; ?></td>
                                <td>RM<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bookings available at the moment.</p>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
