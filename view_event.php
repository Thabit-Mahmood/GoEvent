<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    // If the user is not logged in or is not an organizer, redirect to the login page
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = $_GET['id'];

// Fetch event details from the database
$stmt = $conn->prepare("SELECT e.*, c.category_name FROM events e 
                        LEFT JOIN event_categories c ON e.category_id = c.category_id
                        WHERE e.event_id = ? AND e.organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    // If the event is not found or not owned by the organizer, redirect to the organizer panel
    header("Location: organizerpanel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="view-event-container">
        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
        <div class="event-details">
            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($event['ticket_price']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
            <?php if ($event['event_picture']): ?>
                <img src="<?php echo htmlspecialchars($event['event_picture']); ?>" alt="Event Picture" class="event-thumbnail">
            <?php endif; ?>
            <p><strong>Status:</strong> <?php echo $event['pending_approval'] ? 'Pending Approval' : 'Approved'; ?></p>
        </div>
        <button onclick="location.href='organizerpanel.php'">Back to Organizer Panel</button>
    </main>
</body>

</html>
