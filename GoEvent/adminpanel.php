<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch platform statistics
$stmt = $conn->prepare("SELECT COUNT(*) AS total_events FROM events");
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_events = $stats['total_events'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_users = $stats['total_users'];

$stmt = $conn->prepare("SELECT SUM(quantity) AS total_bookings FROM booked_events");
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_bookings = $stats['total_bookings'];

$stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM booked_events");
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
    <title>Admin Panel - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="admin-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <section class="admin-section">
            <h2>Statistics</h2>
            <div class="stats">
                <div class="stat-item">
                    <h3>Total Events</h3>
                    <p><?php echo $total_events; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
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
