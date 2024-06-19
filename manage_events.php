<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];

// Fetch events created by the organizer
$stmt = $conn->prepare("SELECT event_id, event_name, event_date, pending_approval FROM events WHERE organizer_id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="organizer-container">
        <h1>Manage Your Events</h1>
        <section class="organizer-section">
            <h2>Your Events</h2>
            <?php if (count($events) > 0): ?>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                                <td><?php echo $event['pending_approval'] ? 'Pending Approval' : 'Approved'; ?></td>
                                <td>
                                    <a href="edit_event.php?id=<?php echo $event['event_id']; ?>">Edit</a>
                                    <a href="view_event.php?id=<?php echo $event['event_id']; ?>">View</a>
                                    <a href="delete_event.php?id=<?php echo $event['event_id']; ?>" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <h3>Reviews</h3>
                                    <?php
                                    $review_stmt = $conn->prepare("SELECT r.review_text, r.rating, u.username FROM event_reviews r JOIN users u ON r.user_id = u.user_id WHERE r.event_id = ?");
                                    $review_stmt->bind_param("i", $event['event_id']);
                                    $review_stmt->execute();
                                    $review_result = $review_stmt->get_result();
                                    $reviews = $review_result->fetch_all(MYSQLI_ASSOC);
                                    ?>
                                    <?php if (count($reviews) > 0): ?>
                                        <ul class="reviews-list">
                                            <?php foreach ($reviews as $review): ?>
                                                <li>
                                                    <strong><?php echo htmlspecialchars($review['username']); ?>:</strong>
                                                    <span><?php echo htmlspecialchars($review['review_text']); ?></span>
                                                    <span>Rating: <?php echo htmlspecialchars($review['rating']); ?>/5</span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No reviews yet.</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not created any events yet.</p>
            <?php endif; ?>
            <button onclick="location.href='create_event.php'">Create New Event</button>
        </section>
    </main>
</body>

</html>
