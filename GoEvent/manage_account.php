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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    } else {
        $error = "Error updating profile. Please try again.";
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if (!empty($new_password) && $new_password === $confirm_password) {
            $password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $password, $user_id);

            if ($stmt->execute()) {
                session_destroy(); // Log out the user
                header("Location: logout.php"); // Redirect to the login page
                exit();
            } else {
                $error = "Error updating password. Please try again.";
            }
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

// Handle profile picture update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = 'profile_pictures/' . basename($_FILES['profile_picture']['name']);
        if (!is_dir('profile_pictures')) {
            mkdir('profile_pictures', 0777, true);
        }
        move_uploaded_file($file_tmp, $file_name);
        $profile_picture = $file_name;

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $profile_picture, $user_id);

        if ($stmt->execute()) {
            $success = "Profile picture updated successfully!";
            $_SESSION['profile_picture'] = $profile_picture;
        } else {
            $error = "Error updating profile picture. Please try again.";
        }
    } else {
        $error = "Error uploading profile picture. Please try again.";
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

<body class="manage-account-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <main class="container-xl px-4 mt-4">
    <nav class="profile-title">
      <h1 class="myevents-heading">Manage your GoEvent account</h1>
    </nav>
    <hr class="profile-hr">
    <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
    <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <div class="row">
      <div class="col-xl-4">
        <div class="card mb-4 mb-xl-0">
          <div class="card-header">Profile Picture</div>
          <div class="card-body text-center">
            <img class="img-account-profile rounded-circle mb-2"
              src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
            <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
            <span id="file-name" class="file-name"></span>
            <form id="upload-profile" method="POST" action="" enctype="multipart/form-data">
              <input type="file" id="profile_picture" name="profile_picture" class="inputfile" onchange="displayFileName()">
              <label for="profile_picture" class="btn btn-primary">Choose new image</label>
              <button type="submit" name="update_picture" id="create-account-btn" class="create-account-btn">Upload new image</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-xl-8">
        <div class="card mb-4">
          <div class="card-header">Account Details</div>
          <div class="card-body">
            <form method="POST" action="">
              <input type="hidden" name="update_profile" value="1">
              <div class="row gx-3 mb-3">
                <div class="input-wrapper mb-3">
                  <div class="input-group">
                    <input type="text" id="inputUsername" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    <label class="input-placeholder" for="inputUsername">Username</label>
                  </div>
                </div>
                <div class="col-md-6 input-wrapper">
                  <div class="input-group">
                    <input type="email" id="inputEmailAddress" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <label class="input-placeholder" for="inputEmailAddress">Email</label>
                  </div>
                </div>
              </div>
              <button class="btn btn-primary" type="submit">Save changes</button>
            </form>
            <hr class="profile-hr">
            <div>
              <p>Current Password: <span>********</span> <button id="create-account-btn" class="create-account-btn" type="button" onclick="togglePasswordChange()">Change Password</button></p>
              <div id="password-change-form" style="display:none;">
                <form method="POST" action="">
                  <input type="hidden" name="update_password" value="1">
                  <div class="col-md-6 input-wrapper">
                    <div class="input-group">
                      <input type="password" id="current_password" name="current_password" required>
                      <label class="input-placeholder" for="current_password">Current Password</label>
                    </div>
                  </div>
                  <div class="row gx-3 mb-3">
                    <div class="col-md-6 input-wrapper">
                      <div class="input-group">
                        <input type="password" id="password" name="password">
                        <label class="input-placeholder" for="password">New Password</label>
                      </div>
                    </div>
                    <div class="col-md-6 input-wrapper">
                      <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password">
                        <label class="input-placeholder" for="confirm_password">Confirm New Password</label>
                      </div>
                    </div>
                  </div>
                  <button class="btn btn-primary" type="submit">Update Password</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    function displayFileName() {
      var input = document.getElementById('profile_picture');
      var fileName = input.files[0].name;
      document.getElementById('file-name').textContent = fileName;
    }

    function togglePasswordChange() {
      var form = document.getElementById('password-change-form');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
  </script>
</body>

</html>
