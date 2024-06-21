<?php
require 'db.php'; // Include the database connection

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, user_type, email, profile_picture FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
        $_SESSION['loggedin'] = true; // Ensure this session variable is set
    } else {
        // If user data is not found, log out the user
        session_destroy();
        header("Location: index.php");
        exit();
    }

    // Get cart item count for the logged-in user
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count_data = $result->fetch_assoc();
    $total_items_in_cart = $cart_count_data['total_items'] ?? 0;

    // Fetch notifications for the user
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Count unread notifications
    $unread_count = 0;
    foreach ($notifications as $notification) {
        if (!$notification['is_read']) {
            $unread_count++;
        }
    }
} else {
    $_SESSION['loggedin'] = false; // Ensure this session variable is set if the user is not logged in
}

// Function to set the active class on the current page
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css?v=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <title>GoEvent Header</title>
</head>

<body>
  <header>
    <div class="header-container">
      <div class="logo-container">
        <a href="index.php"><img src="images/logo.png" alt="GoEvent" class="logo"></a>
      </div>
      <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
      <nav class="nav-bar">
        <ul>
          <?php if ($_SESSION['user_type'] === 'admin') : ?>
          <li class="<?php echo isActive('adminpanel.php'); ?>"><a href="adminpanel.php">Admin Panel</a></li>
          <li class="<?php echo isActive('manage_users.php'); ?>"><a href="manage_users.php">Manage Users</a></li>
          <li class="<?php echo isActive('admin_manage_faq.php'); ?>"><a href="admin_manage_faq.php">Manage FAQ</a></li>
          <li class="<?php echo isActive('event_management.php'); ?>"><a href="event_management.php">Event Management</a></li>
          <?php elseif ($_SESSION['user_type'] === 'organizer') : ?>
          <li class="<?php echo isActive('organizerpanel.php'); ?>"><a href="organizerpanel.php">Home</a></li>
          <li class="<?php echo isActive('manage_events.php'); ?>"><a href="manage_events.php">Manage Events</a></li>
          <li class="<?php echo isActive('view_bookings.php'); ?>"><a href="view_bookings.php">View Bookings</a></li>
          <?php else : ?>
          <li class="<?php echo isActive('home.php'); ?>"><a href="home.php">Home</a></li>
          <?php if ($_SESSION['user_type'] === 'regular') : ?>
          <li class="<?php echo isActive('booked_events.php'); ?>"><a href="booked_events.php">My Events</a></li>
          <li class="<?php echo isActive('faq.php'); ?>"><a href="faq.php">FAQ</a></li>
          <?php endif; ?>
          <?php endif; ?>
          <li class="<?php echo isActive('announcements.php'); ?>"><a href="announcements.php">Announcements</a></li>
          <li class="<?php echo isActive('group_members_details.php'); ?>"><a href="group_members_details.php">Group Memebers</a></li>
        </ul>
      </nav>
      <?php endif; ?>
      <div class="user-container">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
        <div class="notification-container">
          <a href="#" class="notification-icon <?php echo isActive('notifications.php'); ?>" onclick="toggleNotificationDropdown()">
            <i class="fa fa-bell" aria-hidden="true"></i>
            <?php if ($unread_count > 0): ?>
            <span class="cart-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </a>
          <div class="notification-dropdown" id="notificationDropdown">
            <ul>
              <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                  <li class="<?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" data-id="<?php echo $notification['notification_id']; ?>">
                    <a href="<?php echo $notification['link'] ? $notification['link'] : '#'; ?>" onclick="markNotificationAsRead(event, <?php echo $notification['notification_id']; ?>)">
                      <p><?php echo htmlspecialchars($notification['notification_text']); ?></p>
                      <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li>No notifications available at the moment.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
        <?php if ($_SESSION['user_type'] === 'regular') : ?>
        <a href="cart.php" class="cart-icon <?php echo isActive('cart.php'); ?>">
          <i class="fa fa-shopping-cart" aria-hidden="true"></i>
          <?php if ($total_items_in_cart > 0) : ?>
          <span class="cart-badge"><?php echo $total_items_in_cart; ?></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>
        <div class="profile" onclick="toggleProfileDropdown()">
          <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture"
            class="profile-picture" id="profilePicture">
          <div class="profile-dropdown">
            <p class="account-info">GoEvent Account</p>
            <p class="username">
              <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?>
            </p>
            <p class="email">
              <?php echo htmlspecialchars($_SESSION['email']); ?>
            </p>
          </div>
        </div>
        <div class="dropdown" id="profileDropdown">
          <p>
            <?php echo htmlspecialchars($_SESSION['email']); ?>
          </p>
          <p><img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture"
              class="profile-picture-edit" id="profilePicture-edit"></p>
          <p>
            <?php echo ucfirst(htmlspecialchars($_SESSION['user_type'])); ?> GoEvent account
          </p>
          <p>Hi,
            <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?>!
          </p>
          <a class="manage-account" href="manage_account.php">Manage your GoEvent account</a>
          <div class="button-group">
            <button onclick="location.href='add_account.php'" class="add-account-btn"><i class="fa fa-plus-circle"
                id="plus" aria-hidden="true"></i> Add account</button>
            <button onclick="location.href='logout.php'" class="sign-out-btn"><i class="fa fa-sign-out"
                aria-hidden="true"></i> Sign out</button>
          </div>
        </div>
        <?php else : ?>
        <button onclick="location.href='login.php'" class="sign-in-btn">Sign In</button>
        <?php endif; ?>
      </div>
    </div>
    <script src="js/main.js"></script>
  </header>

  <script>
    function toggleProfileDropdown() {
      var dropdown = document.getElementById('profileDropdown');
      dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
    }

    function toggleNotificationDropdown() {
      var dropdown = document.getElementById('notificationDropdown');
      dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
    }

    function markNotificationAsRead(event, notificationId) {
      event.preventDefault(); // Prevent the default link behavior
      var link = event.currentTarget;
      fetch('mark_notifications_as_read.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: notificationId })
      }).then(response => response.json())
        .then(data => {
          if (data.success) {
            document.querySelector('li[data-id="' + notificationId + '"]').classList.remove('unread');
            document.querySelector('li[data-id="' + notificationId + '"]').classList.add('read');
            // Redirect to the event page after marking as read
            window.location.href = link.href;
          }
        });
    }

    document.addEventListener('click', function (event) {
      var profileDropdown = document.getElementById('profileDropdown');
      var profilePicture = document.getElementById('profilePicture');
      var notificationDropdown = document.getElementById('notificationDropdown');
      var notificationIcon = document.querySelector('.notification-icon');

      if (!profileDropdown.contains(event.target) && !profilePicture.contains(event.target)) {
        profileDropdown.style.display = 'none';
      }

      if (!notificationDropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
        notificationDropdown.style.display = 'none';
      }
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const profile = document.querySelector('.profile');
      const profileDropdown = document.querySelector('.profile-dropdown');
      const dropdown = document.querySelector('.dropdown');

      profile.addEventListener('mouseover', function () {
        if (window.getComputedStyle(dropdown).display === 'none') {
          profileDropdown.style.display = 'block';
        }
      });

      profile.addEventListener('mouseout', function () {
        profileDropdown.style.display = 'none';
      });

      dropdown.addEventListener('showDropdown', function () {
        profileDropdown.style.display = 'none';
      });

      profile.addEventListener('click', function () {
        profileDropdown.style.display = 'none';
      });

      function showDropdown() {
        dropdown.style.display = 'block';
        dropdown.dispatchEvent(new CustomEvent('showDropdown'));
      }

      function hideDropdown() {
        dropdown.style.display = 'none';
      }

      document.querySelector('.some-button').addEventListener('click', function () {
        if (window.getComputedStyle(dropdown).display === 'none') {
          showDropdown();
        } else {
          hideDropdown();
        }
      });
    });
  </script>

</body>

</html>
