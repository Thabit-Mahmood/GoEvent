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

// Monthly revenue
$stmt = $conn->prepare("SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month, SUM(total_price) AS monthly_revenue FROM booked_events WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?) GROUP BY month");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$monthly_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total bookings for each event and average rating
$stmt = $conn->prepare("SELECT e.event_id, e.event_name, IFNULL(SUM(b.quantity), 0) AS total_bookings, IFNULL(AVG(r.rating), 0) AS average_rating, COUNT(r.review_id) AS total_reviews, IFNULL(SUM(b.total_price), 0) AS total_revenue FROM events e LEFT JOIN booked_events b ON e.event_id = b.event_id LEFT JOIN event_reviews r ON e.event_id = r.event_id WHERE e.organizer_id = ? GROUP BY e.event_id, e.event_name");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Latest approved events
$stmt = $conn->prepare("SELECT event_name, event_date FROM events WHERE organizer_id = ? AND pending_approval = 0 ORDER BY event_date DESC LIMIT 5");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$latest_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top performing event
$stmt = $conn->prepare("SELECT event_name, SUM(quantity) AS total_bookings FROM booked_events JOIN events ON booked_events.event_id = events.event_id WHERE events.organizer_id = ? GROUP BY booked_events.event_id ORDER BY total_bookings DESC LIMIT 1");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$top_event = $stmt->get_result()->fetch_assoc();

// Least performing event
$stmt = $conn->prepare("SELECT event_name, SUM(quantity) AS total_bookings FROM booked_events JOIN events ON booked_events.event_id = events.event_id WHERE events.organizer_id = ? GROUP BY booked_events.event_id ORDER BY total_bookings ASC LIMIT 1");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$least_event = $stmt->get_result()->fetch_assoc();

// Event approval rate
$stmt = $conn->prepare("SELECT (SUM(CASE WHEN pending_approval = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS approval_rate FROM events WHERE organizer_id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$approval_rate = $stmt->get_result()->fetch_assoc()['approval_rate'];
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body class="organizer-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <div class="welcome">
            <h1 class="myevents-heading">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
    <main class="organizer-container">

        <div class="grid-container">
            <section class="stats">
                <div class="stat-item">
                    <h3>Total Events <i class="fa fa-calendar"></i></h3>
                    <p><?php echo $total_events; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Bookings <i class="fa fa-ticket-alt"></i></h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Revenue <i class="fa fa-dollar-sign"></i></h3>
                    <p>RM <?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </section>
            <section class="chart-container">
                <h2>Monthly Revenue</h2>
                <canvas id="revenueChart"></canvas>
            </section>
            <section class="chart-container-2">
                <h2>Average Event Ratings</h2>
                <canvas id="ratingsChart"></canvas>
            </section>
            <section class="chart-container-3">
                <h2>Total Bookings per Event</h2>
                <canvas id="bookingsChart"></canvas>
            </section>
            <section class="events-data">
                <h2>Events Data</h2>
                <?php foreach ($events_data as $event): ?>
                <div class="event-item">
                    <p class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></p>
                    <p>Bookings: <?php echo $event['total_bookings']; ?></p>
                    <p>Avg. Rating: <?php echo number_format($event['average_rating'], 2); ?> <i class="fa fa-star"></i></p>
                    <p>Reviews: <?php echo $event['total_reviews']; ?></p>
                    <p>Revenue: RM<?php echo number_format($event['total_revenue'], 2); ?></p>
                </div>
                <?php endforeach; ?>
            </section>
            <section class="approval-rate">
    <h2>Event Approval Rate</h2>
    <canvas id="approvalRateChart"></canvas>
</section>
            <section class="top-event">
                <h2>Top Performing Event</h2>
                <?php if ($top_event): ?>
                <p>Event: <?php echo htmlspecialchars($top_event['event_name']); ?></p>
                <p>Total Bookings: <?php echo $top_event['total_bookings']; ?></p>
                <?php else: ?>
                <p>No bookings available.</p>
                <?php endif; ?>
            </section>

            <section class="latest-activity">
                <h2>Latest Approved Events</h2>
                <?php foreach ($latest_events as $event): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fa fa-calendar-check"></i>
                    </div>
                    <div class="activity-details">
                        <p class="activity-title"><?php echo htmlspecialchars($event['event_name']); ?></p>
                        <p class="activity-time"><?php echo htmlspecialchars($event['event_date']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        
        // Update for Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly_revenue, 'month')); ?>,
        datasets: [{
            label: 'Monthly Revenue',
            data: <?php echo json_encode(array_column($monthly_revenue, 'monthly_revenue')); ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 2,
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

const approvalRateCtx = document.getElementById('approvalRateChart').getContext('2d');
const approvalRateChart = new Chart(approvalRateCtx, {
    type: 'pie',
    data: {
        labels: ['Approved', 'Pending'],
        datasets: [{
            data: [<?php echo $approval_rate; ?>, <?php echo 100 - $approval_rate; ?>],
            backgroundColor: ['#25a58f', '#f44336'],
            borderColor: ['#25a58f', '#f44336'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Event Approval Rate'
            }
        }
    }
});



        const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
        const bookingsChart = new Chart(bookingsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($events_data, 'event_name')); ?>,
                datasets: [{
                    label: 'Total Bookings',
                    data: <?php echo json_encode(array_column($events_data, 'total_bookings')); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        

        const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
        const ratingsChart = new Chart(ratingsCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($events_data, 'event_name')); ?>,
                datasets: [{
                    label: 'Average Rating',
                    data: <?php echo json_encode(array_column($events_data, 'average_rating')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Average Event Ratings'
                    }
                }
            }
        });
    </script>
</body>

</html>
