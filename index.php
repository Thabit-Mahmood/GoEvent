<?php
session_start();
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="index">
  <?php include 'header.php'; ?>
  <div class="container">
    <h1 class="header-line">
      <span>Welcome to <a href="index.php"><img src="images/logo.png" alt="GoEvent" class="logo"></a></span>
    </h1>
    <p class="tagline-line">Seamless Events, Unforgettable moments</p>
    <p class="tagline-line2">Ready to Book? Enter your email to create an Account.</p>

    <div class="input-wrapper">
      <form class="input-group" action="register.php" method="GET">
        <input type="email" id="email" name="email" required>
        <label class="input-placeholder" for="email">Email address</label>
        <button type="submit">Get Started <i class="fa fa-chevron-right" aria-hidden="true"></i></button>
      </form>
    </div>
  </div>
  <!-- <?php include 'footer.php'; ?> -->
</body>

</html>