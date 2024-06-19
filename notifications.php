<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_as_read'])) {
    $notification_id = $_POST['notification_id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="notifications-container">
        <h1>Notifications</h1>
        <?php if (count($notifications) > 0): ?>
            <ul class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <li class="<?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                        <p><?php echo htmlspecialchars($notification['notification_text']); ?></p>
                        <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                <button type="submit" name="mark_as_read">Mark as Read</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No notifications available at the moment.</p>
        <?php endif; ?>
    </main>
</body>

</html>
