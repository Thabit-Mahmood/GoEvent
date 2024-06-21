<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    // If no event ID is provided, redirect to the home page
    header("Location: home.php");
    exit();
}

$event_id = $_GET['id'];

// Fetch event details from the database
$stmt = $conn->prepare("SELECT e.event_name, e.event_description, e.event_date, e.ticket_price, e.event_picture, u.username AS organizer, u.email AS organizer_email 
                        FROM events e 
                        JOIN users u ON e.organizer_id = u.user_id 
                        WHERE e.event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    // If the event is not found, redirect to the home page
    header("Location: home.php");
    exit();
}

// Calculate how many people already booked the event
$booked_stmt = $conn->prepare("SELECT COUNT(*) as total_booked FROM cart WHERE event_id = ?");
$booked_stmt->bind_param("i", $event_id);
$booked_stmt->execute();
$booked_result = $booked_stmt->get_result();
$booked_data = $booked_result->fetch_assoc();
$total_booked = $booked_data['total_booked'];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();

    if ($cart_item) {
        // Event already in cart, update quantity
        $new_quantity = $cart_item['quantity'] + 1;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $event_id);
        if ($stmt->execute()) {
            $_SESSION['cart_success'] = "Event added to cart!";
        } else {
            $_SESSION['cart_errvor'] = "Failed to update event quantity in cart.";
        }
    } else {
        // Event not in cart, insert new row
        $stmt = $conn->prepare("INSERT INTO cart (user_id, event_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $event_id);
        if ($stmt->execute()) {
            $_SESSION['cart_success'] = "Event added to cart!";
        } else {
            $_SESSION['cart_error'] = "Failed to add event to cart.";
        }
    }
    // Redirect to avoid form resubmission
    header("Location: event.php?id=$event_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?php echo htmlspecialchars($event['event_name']); ?> - GoEvent
  </title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
</head>

<body class="bookedevent-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <?php if (isset($_SESSION['cart_success'])): ?>
  <p class="success">
    <?php 
      echo $_SESSION['cart_success']; 
      unset($_SESSION['cart_success']);
      ?>
  </p>
  <?php endif; ?>
  <?php if (isset($_SESSION['cart_error'])): ?>
  <p class="error">
    <?php 
      echo $_SESSION['cart_error']; 
      unset($_SESSION['cart_error']);
      ?>
  </p>
  <?php endif; ?>
  <main class="event-container">
    <div class="event-header">
      <?php if ($event['event_picture']): ?>
      <div class="event-image">
        <img src="<?php echo htmlspecialchars($event['event_picture']); ?>" alt="Event Picture" class="event-picture">
      </div>
      <?php endif; ?>
      <?php 
            if (isset($event['event_date'])) {
              $event_date = new DateTime($event['event_date']);
              $formatted_date = $event_date->format('d/m/Y \a\t h:ia');
            } else {
              $formatted_date = 'Date not set';
            }
          ?>
      <h1>
        <?php echo htmlspecialchars(ucfirst($event['event_name'])); ?>
      </h1>
      <div class="event-info">
        <p><strong><i class="fa fa-calendar" aria-hidden="true"></i></strong>
          <?php echo htmlspecialchars($formatted_date); ?>
        </p>
        <p><strong><i class="fa fa-tag" aria-hidden="true"></i></strong> RM
          <?php echo htmlspecialchars($event['ticket_price']); ?>
        </p>
      </div>

      <form action="event.php?id=<?php echo $event_id; ?>" method="POST">
        <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart
          <p class="button-date">Starts
            <?php echo date('F jS', strtotime($event['event_date'])); ?>
          </p>
        </button>
        <p><strong>
            <?php echo $total_booked; ?>
          </strong> Already purchased
      </form>
    </div>
    <div class="event-sidebar">
      <p><strong>
          <?php echo htmlspecialchars(ucfirst($event['event_name'])); ?>
        </strong><br>
        <?php echo nl2br(htmlspecialchars($event['event_description'])); ?>
      </p>
      <hr class="divider">
      <p><strong>Organizer:</strong>
        <?php echo htmlspecialchars($event['organizer']); ?>
      </p>
      <p><strong>Email:</strong>
        <?php echo htmlspecialchars($event['organizer_email']); ?>
      </p>
    </div>
  </main>
  <script>
    // Hide success and error messages after a few seconds
    setTimeout(function () {
      var successMessage = document.querySelector('.success');
      var errorMessage = document.querySelector('.error');
      if (successMessage) {
        successMessage.style.display = 'none';
      }
      if (errorMessage) {
        errorMessage.style.display = 'none';
      }
    }, 3000); // Adjust the time as needed (3000 milliseconds = 3 seconds)
  </script>
</body>

</html>