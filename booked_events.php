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

<body class="bookedevent-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="home-container">
        <h1 class="myevents-heading">Your Booked Events</h1>
        <div class="review-success">
        <?php if (isset($_SESSION['review_success'])): ?>
        <div class="success">
            <?php echo $_SESSION['review_success']; unset($_SESSION['review_success']); ?>
        </div>
        <?php endif; ?>
        </div>
        <?php if (count($booked_events) > 0): ?>
        <div id="booked-events-container" class="events-container">
            <div class="events">
                <?php foreach ($booked_events as $event): ?>
                <?php 
                $event_date = new DateTime($event['event_date']);
                $formatted_date = $event_date->format('d/m/Y \a\t h:ia');
                ?>
                <div class="event-card">
                    <a class="link-card" href="event.php?id=<?php echo $event['event_id']; ?>">
                        <div class="event-details">
                            <h3><?php echo htmlspecialchars(ucfirst($event['event_name'])); ?></h3>
                            <p><i class="fa fa-calendar" aria-hidden="true"></i> <?php echo htmlspecialchars($formatted_date); ?></p>
                            <p><i class="fa fa-ticket" aria-hidden="true"></i> Quantity: <?php echo $event['quantity']; ?></p>
                            <p><i class="fa fa-money-bill" aria-hidden="true"></i> RM <?php echo number_format($event['total_price'], 2); ?></p>
                            <a href="#" class="review-link" data-event-id="<?php echo $event['event_id']; ?>"><i class="fa fa-star" aria-hidden="true"></i> Review</a>
                            <p class="booking-date"><?php echo htmlspecialchars($event['booking_date']); ?></p>
                        </div>
                    </a>
                    <div class="feedback-form" id="review-form-<?php echo $event['event_id']; ?>" style="display: none;">
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <textarea name="review_text" rows="4" placeholder="Enter your review here" required></textarea>
                            <div class="rating">
                                <input type="radio" name="rating" value="5" id="5-<?php echo $event['event_id']; ?>"><label for="5-<?php echo $event['event_id']; ?>">★</label>
                                <input type="radio" name="rating" value="4" id="4-<?php echo $event['event_id']; ?>"><label for="4-<?php echo $event['event_id']; ?>">★</label>
                                <input type="radio" name="rating" value="3" id="3-<?php echo $event['event_id']; ?>"><label for="3-<?php echo $event['event_id']; ?>">★</label>
                                <input type="radio" name="rating" value="2" id="2-<?php echo $event['event_id']; ?>"><label for="2-<?php echo $event['event_id']; ?>">★</label>
                                <input type="radio" name="rating" value="1" id="1-<?php echo $event['event_id']; ?>"><label for="1-<?php echo $event['event_id']; ?>">★</label>
                            </div>
                            <button type="submit">Submit Review</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <p>You have no booked events.</p>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.review-link').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const eventId = link.getAttribute('data-event-id');
                    const form = document.getElementById('review-form-' + eventId);
                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                });
            });
        });
    </script>
</body>

</html>
