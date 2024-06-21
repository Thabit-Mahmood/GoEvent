<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    // If the user is not logged in or is not an organizer, redirect to the login page
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validate the event ID

if (!$event_id) {
    header("Location: organizerpanel.php");
    exit();
}

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

<body class="create-event-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <h1 class="myevents-heading"><?php echo htmlspecialchars($event['event_name'], ENT_QUOTES, 'UTF-8'); ?></h1>

    <main class="view-event-container">
        <div class="event-details">
            <p><i class="fa fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><i class="fa fa-list-ul" aria-hidden="true"></i> <?php echo htmlspecialchars($event['category_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><i class="fa fa-tag" aria-hidden="true"></i> $<?php echo htmlspecialchars($event['ticket_price'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['event_description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php if ($event['event_picture']): ?>
                <img src="<?php echo htmlspecialchars($event['event_picture'], ENT_QUOTES, 'UTF-8'); ?>" alt="Event Picture" class="event-thumbnail">
            <?php endif; ?>
            <p><i class="fa fa-check-circle" aria-hidden="true"></i> <?php echo $event['pending_approval'] ? 'Pending Approval' : 'Approved'; ?></p>
        </div>
        <button onclick="location.href='organizerpanel.php'">Back to Organizer Panel</button>
    </main>
</body>

</html>
