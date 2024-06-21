<?php
session_start();
require 'db.php';

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    // Prepare and execute the query to get the user details
    $stmt = $conn->prepare("SELECT user_id, username, user_type, profile_picture, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['email'] = $email;
        $_SESSION['profile_picture'] = $user['profile_picture'];
        $_SESSION['loggedin'] = true;

        // Redirect based on user type
        if ($user['user_type'] == 'regular') {
            header("Location: home.php");
        } elseif ($user['user_type'] == 'organizer') {
            header("Location: organizerpanel.php");
        } elseif ($user['user_type'] == 'admin') {
            header("Location: adminpanel.php");
        }
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="register-body">
  <div class="register-container">
    <img src="images/logo.png" alt="GoEvent Logo" class="logo">
    <h1>Login to GoEvent</h1>
    <h4 class="subheading">Welcome back!</h4>
    <?php if ($error) : ?>
    <p class="error">
      <?php echo $error; ?>
    </p>
    <?php endif; ?>
    <form id="loginForm" action="login.php" method="POST" novalidate>
      <div class="input-wrapper">
        <div class="input-group">
          <input type="email" id="email" name="email" required>
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
      <div class="show-password">
        <input type="checkbox" id="show_password">
        <label for="show_password">Show password</label>
      </div>
      <div id="button-container" class="button-container">
        <button id="btn-primary" class="btn-primary" type="button" onclick="window.location.href='register.php'">Create
          account</button>
        <button id="login-btn" class="create-account-btn" type="submit">Login</button>
      </div>
    </form>
  </div>

  <script src="js/main.js"></script>
  <script>
    document.getElementById('show_password').addEventListener('change', function () {
      const password = document.getElementById('password');
      const type = this.checked ? 'text' : 'password';
      password.type = type;
    });

    document.getElementById('loginForm').addEventListener('submit', function (event) {
      let valid = true;

      // Custom email validation
      const email = document.getElementById('email');
      const emailError = document.getElementById('email-error');
      if (!email.value) {
        emailError.textContent = 'Please fill out this field';
        emailError.classList.add('active');
        email.classList.add('input-error');
        valid = false;
      } else if (!email.value.includes('@')) {
        emailError.textContent = 'Please include an "@" in the email address';
        emailError.classList.add('active');
        email.classList.add('input-error');
        valid = false;
      } else {
        emailError.textContent = '';
        emailError.classList.remove('active');
        email.classList.remove('input-error');
      }

      if (!valid) {
        event.preventDefault();
        return;
      }

      // Custom password validation
      const password = document.getElementById('password');
      const passwordError = document.getElementById('password-error');
      if (!password.value) {
        passwordError.textContent = 'Please fill out this field';
        passwordError.classList.add('active');
        password.classList.add('input-error');
        valid = false;
      } else {
        passwordError.textContent = '';
        passwordError.classList.remove('active');
        password.classList.remove('input-error');
      }

      if (!valid) {
        event.preventDefault();
        return;
      }
    });

    document.getElementById('email').addEventListener('input', function () {
      if (this.classList.contains('input-error')) {
        this.classList.remove('input-error');
        document.getElementById('email-error').classList.remove('active');
      }
    });

    document.getElementById('password').addEventListener('input', function () {
      if (this.classList.contains('input-error')) {
        this.classList.remove('input-error');
        document.getElementById('password-error').classList.remove('active');
      }
    });
  </script>
</body>

</html>
