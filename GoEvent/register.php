<?php
session_start();
require 'db.php'; // Include the database connection

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = 'regular'; // Default user type; you can change this logic if needed

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Generate username from email
        $username = explode('@', $email)[0];

        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $user_type);

            if ($stmt->execute()) {
                // Get the user ID of the newly created user
                $user_id = $stmt->insert_id;

                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['email'] = $email;
                $_SESSION['profile_picture'] = ''; // Set a default profile picture if needed
                $_SESSION['loggedin'] = true;

                // Redirect based on user type
                if ($user_type == 'regular') {
                    header("Location: home.php");
                } elseif ($user_type == 'organizer') {
                    header("Location: organizerpanel.php");
                } elseif ($user_type == 'admin') {
                    header("Location: adminpanel.php");
                }
                exit();
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}

$prefilled_email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="register-body">
  <div class="register-container">
    <img src="images/logo.png" alt="GoEvent Logo" class="logo">
    <h1>Create a GoEvent Account</h1>
    <h4 class="subheading">Join GoEvent today</h4>
    <?php if ($error) : ?>
    <p class="error">
      <?php echo $error; ?>
    </p>
    <?php endif; ?>
    <form id="registerForm" action="register.php" method="POST" novalidate>
      <div class="input-wrapper">
        <div class="input-group">
          <input type="email" id="email" name="email" value="<?php echo $prefilled_email; ?>" required>
          <label class="input-placeholder" for="email">Email address</label>
        </div>
      </div>
      <span class="error-message" id="email-error"></span>
      <div class="password-wrapper">
        <div class="input-wrapper">
          <div class="input-group">
            <input type="password" id="password" name="password" required>
            <label class="input-placeholder" for="password">Password</label>
          </div>
        </div>
        <span class="error-message" id="password-error"></span>
      </div>
      <div class="password-wrapper">
        <div class="input-wrapper">
          <div class="input-group">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <label class="input-placeholder" for="confirm_password">Confirm</label>
          </div>
        </div>
        <span class="error-message" id="confirm-password-error"></span>
      </div>
      <div class="show-password">
        <input type="checkbox" id="show_password">
        <label for="show_password">Show password</label>
      </div>
      <div id="button-container" class="button-container">
        <button id="btn-primary" class="btn-primary" type="button" onclick="window.location.href='login.php'">Already
          have an account?</button>
        <button id="create-account-btn" class="create-account-btn" type="submit">Sign Up</button>
      </div>
    </form>
  </div>

  <script src="js/main.js"></script>
</body>

</html>