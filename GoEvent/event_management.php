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
            <h1 class="myevents-heading">Event Approval Requests</h1>
        </div>
        <?php if (count($events) > 0): ?>
            <section class="organizer-section">
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Name <i class="fa fa-calendar-alt"></i></th>
                            <th>Description <i class="fa fa-info-circle"></i></th>
                            <th>Date <i class="fa fa-clock"></i></th>
                            <th>Price <i class="fa fa-dollar-sign"></i></th>
                            <th>Organizer <i class="fa fa-user"></i></th>
                            <th>Actions <i class="fa fa-cogs"></i></th>
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
                                    <div class="action-buttons">
                                        <form method="POST" action="admin_approve_event.php" style="display:inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                            <button type="submit" class="btn btn-edit"><i class="fa fa-check"></i> Approve</button>
                                        </form>
                                        <form method="POST" action="admin_reject_event.php" style="display:inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                            <button type="submit" class="btn btn-delete"><i class="fa fa-times"></i> Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php else: ?>
            <p>No events pending approval at the moment.</p>
        <?php endif; ?>
    </main>
</body>

</html>
