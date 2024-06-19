<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Handle password change
    if (!empty($_POST['password']) && $_POST['password'] === $_POST['confirm_password']) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $username, $email, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    } else {
        $error = "Error updating profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="manage-account-container">
        <h1>Manage Your Account</h1>
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
                <label for="password">New Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </main>
</body>

</html>
