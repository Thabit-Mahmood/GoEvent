<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    $feedback_text = $_POST['feedback_text'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("INSERT INTO event_reviews (event_id, user_id, review_text, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $event_id, $user_id, $feedback_text, $rating);
    $stmt->execute();

    header("Location: booked_events.php");
    exit();
} else {
    $event_id = $_GET['event_id'];
    $stmt = $conn->prepare("SELECT event_name FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="feedback-container">
        <h1>Provide Feedback for <?php echo htmlspecialchars($event['event_name']); ?></h1>
        <form method="POST" action="provide_feedback.php">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <div class="input-group">
                <label for="feedback_text">Feedback</label>
                <textarea id="feedback_text" name="feedback_text" required></textarea>
            </div>
            <div class="input-group">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" required>
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>
            <button type="submit">Submit Feedback</button>
        </form>
    </main>
</body>

</html>
