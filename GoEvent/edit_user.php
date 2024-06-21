<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        // If the user is not found, redirect to the manage users page
        header("Location: manage_users.php");
        exit();
    }
} else {
    // If no user_id is provided, redirect to the manage users page
    header("Location: manage_users.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // Handle password change
    if (!empty($_POST['password']) && $_POST['password'] === $_POST['confirm_password']) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, user_type = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $username, $email, $password, $user_type, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $username, $email, $user_type, $user_id);
    }

    if ($stmt->execute()) {
        $success = "User updated successfully!";
        // Refresh user details after update
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Error updating user. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - GoEvent</title>
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
            <h1 class="myevents-heading">Edit User</h1>
        </div>
        <section class="organizer-section">
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <form method="POST" action="" class="register-container">
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        <label class="input-placeholder" for="username">Username</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <label class="input-placeholder" for="email">Email</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <select id="user_type" name="user_type" required>
                            <option value="regular" <?php if ($user['user_type'] == 'regular') echo 'selected'; ?>>Regular</option>
                            <option value="organizer" <?php if ($user['user_type'] == 'organizer') echo 'selected'; ?>>Organizer</option>
                            <option value="admin" <?php if ($user['user_type'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                        <label class="input-placeholder" for="user_type">Role</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="password" id="password" name="password">
                        <label class="input-placeholder" for="password">New Password</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password">
                        <label class="input-placeholder" for="confirm_password">Confirm New Password</label>
                    </div>
                </div>
                <button type="submit" class="create-account-btn">Update User</button>
            </form>
        </section>
    </main>
</body>

</html>
