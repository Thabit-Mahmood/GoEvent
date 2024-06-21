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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $user_type);

    if ($stmt->execute()) {
        $success = "User added successfully!";
    } else {
        $error = "Error adding user. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - GoEvent</title>
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
            <h1 class="myevents-heading">Add New User</h1>
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
                        <input type="text" id="username" name="username" required>
                        <label class="input-placeholder" for="username">Username</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="email" id="email" name="email" required>
                        <label class="input-placeholder" for="email">Email</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="password" id="password" name="password" required>
                        <label class="input-placeholder" for="password">Password</label>
                    </div>
                </div>
                <div class="input-wrapper">
                    <div class="input-group">
                        <select id="user_type" name="user_type" required>
                            <option value="regular">Regular</option>
                            <option value="organizer">Organizer</option>
                            <option value="admin">Admin</option>
                        </select>
                        <label class="input-placeholder" for="user_type">Role</label>
                    </div>
                </div>
                <button type="submit" class="create-account-btn">Add User</button>
            </form>
        </section>
    </main>
</body>

</html>
