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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="manage-events-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="organizer-event-container">
        <div class="welcome">
            <h1 class="myevents-heading">Your Events!</h1>
        </div>
        <section class="organizer-section">
            <?php if (count($events) > 0): ?>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Name <i class="fa fa-calendar-alt"></i></th>
                            <th>Event Date <i class="fa fa-clock"></i></th>
                            <th>Status <i class="fa fa-info-circle"></i></th>
                            <th>Actions <i class="fa fa-cogs"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $event['pending_approval'] ? 'Pending Approval' : 'Approved'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-view" onclick="location.href='view_event.php?id=<?php echo $event['event_id']; ?>'"><i class="fa fa-eye"></i> View</button>
                                        <button class="btn btn-edit" onclick="location.href='edit_event.php?id=<?php echo $event['event_id']; ?>'"><i class="fa fa-edit"></i> Edit</button>
                                        <button class="btn btn-delete" onclick="confirmDelete(<?php echo $event['event_id']; ?>)"><i class="fa fa-trash"></i> Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <span class="drawer-toggle" onclick="toggleDrawer('drawer-<?php echo $event['event_id']; ?>')"><i class="fa fa-comments"></i> Reviews</span>
                                    <div id="drawer-<?php echo $event['event_id']; ?>" class="drawer">
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
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8'); ?>:</strong>
                                                            <span><?php echo htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </div>
                                                        <div class="star-rating">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <?php if ($i <= $review['rating']): ?>
                                                                    <i class="fa fa-star"></i>
                                                                <?php else: ?>
                                                                    <i class="fa fa-star-o"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p>No reviews yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not created any events yet.</p>
            <?php endif; ?>
            <button class="overlay-button" onclick="location.href='create_event.php'"><i class="fa fa-plus"></i></button>
        </section>
    </main>
    <script>
        function toggleDrawer(id) {
            const drawer = document.getElementById(id);
            drawer.classList.toggle('open');
        }

        function confirmDelete(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                window.location.href = 'delete_event.php?id=' + eventId;
            }
        }
    </script>
</body>

</html>
