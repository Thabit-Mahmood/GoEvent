<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];

// Fetch statistics
// Total number of events
$stmt = $conn->prepare("SELECT COUNT(*) AS total_events FROM events WHERE organizer_id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_events = $stats['total_events'];

// Total quantity of bookings
$stmt = $conn->prepare("SELECT SUM(quantity) AS total_bookings FROM booked_events WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?)");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_bookings = $stats['total_bookings'];

// Total revenue
$stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM booked_events WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?)");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_revenue = $stats['total_revenue'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Panel - GoEvent</title>
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
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <section class="organizer-section">
            <h2>Statistics</h2>
            <div class="stats">
                <div class="stat-item">
                    <h3>Total Events</h3>
                    <p><?php echo $total_events; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Revenue</h3>
                    <p>$<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
