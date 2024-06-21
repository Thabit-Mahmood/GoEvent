<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users
$stmt = $conn->prepare("SELECT user_id, username, email, user_type FROM users");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - GoEvent</title>
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
            <h1 class="myevents-heading">Manage Users</h1>
        </div>
        <section class="organizer-section">
            <?php if (count($users) > 0): ?>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Username <i class="fa fa-user"></i></th>
                            <th>Email <i class="fa fa-envelope"></i></th>
                            <th>Role <i class="fa fa-user-tag"></i></th>
                            <th>Actions <i class="fa fa-cogs"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-edit" onclick="location.href='edit_user.php?user_id=<?php echo $user['user_id']; ?>'"><i class="fa fa-edit"></i> Edit</button>
                                        <form method="POST" action="delete_user.php" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button class="btn btn-delete" type="submit" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
            <button class="overlay-button" onclick="location.href='add_user.php'"><i class="fa fa-plus"></i></button>
        </section>
    </main>
</body>

</html>
