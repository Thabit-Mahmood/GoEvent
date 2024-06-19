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

$user_id = $_GET['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

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
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="admin-container">
        <h1>Edit User</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="input-group">
                <label for="user_type">Role</label>
                <select id="user_type" name="user_type" required>
                    <option value="regular" <?php if ($user['user_type'] == 'regular') echo 'selected'; ?>>Regular</option>
                    <option value="organizer" <?php if ($user['user_type'] == 'organizer') echo 'selected'; ?>>Organizer</option>
                    <option value="admin" <?php if ($user['user_type'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            <div class="input-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit">Update User</button>
        </form>
    </main>
</body>

</html>
