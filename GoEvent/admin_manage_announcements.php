<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch announcements
$stmt = $conn->prepare("SELECT announcement_id, announcement_text, created_at FROM announcements");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="admin-container">
        <h1>Manage Announcements</h1>
        <section class="admin-section">
            <h2>Create New Announcement</h2>
            <form method="POST" action="admin_create_announcement.php">
                <textarea name="announcement_text" required></textarea>
                <button type="submit">Create Announcement</button>
            </form>
        </section>
        <section class="admin-section">
            <h2>Existing Announcements</h2>
            <?php if (count($announcements) > 0): ?>
                <table class="announcements-table">
                    <thead>
                        <tr>
                            <th>Announcement</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $announcement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($announcement['announcement_text']); ?></td>
                                <td><?php echo htmlspecialchars($announcement['created_at']); ?></td>
                                <td>
                                    <form method="POST" action="admin_delete_announcement.php" style="display:inline;">
                                        <input type="hidden" name="announcement_id" value="<?php echo $announcement['announcement_id']; ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No announcements available.</p>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
