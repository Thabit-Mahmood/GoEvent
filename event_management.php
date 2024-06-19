<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch events pending approval
$stmt = $conn->prepare("SELECT e.event_id, e.event_name, e.event_description, e.event_date, e.ticket_price, e.event_picture, u.username as organizer_name FROM events e JOIN users u ON e.organizer_id = u.user_id WHERE e.pending_approval = 1");
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Approval - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="admin-container">
        <h1>Event Approval Requests</h1>
        <?php if (count($events) > 0): ?>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Organizer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_description']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td>$<?php echo number_format($event['ticket_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($event['organizer_name']); ?></td>
                            <td>
                                <form method="POST" action="admin_approve_event.php" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                    <button type="submit">Approve</button>
                                </form>
                                <form method="POST" action="admin_reject_event.php" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                    <button type="submit">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No events pending approval at the moment.</p>
        <?php endif; ?>
    </main>
</body>

</html>
