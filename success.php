<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Assuming username is stored in session

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation - GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="confirmation-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <main class="confirmation-container">
    <div class="confirmation-content">
      <h1>Thank you for your purchase, <?php echo htmlspecialchars($username); ?>!</h1>
      <p>Your order has been successfully processed.</p>
      <div class="continue-shopping-button">
        <a href="home.php" class="continue-shopping">Continue shopping</a>
      </div>
    </div>
  </main>
</body>

</html>
