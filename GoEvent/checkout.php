<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items from the database
$stmt = $conn->prepare("SELECT c.event_id, c.quantity, e.event_name, e.ticket_price, e.event_picture 
                        FROM cart c 
                        JOIN events e ON c.event_id = e.event_id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="cart-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <main class="cart-container">
    <div class="cart-content">
      <h1 class="cart-title">Checkout</h1>
      <?php if (count($cart_items) > 0): ?>
      <div class="cart-wrapper">
        <div class="cart-items">
        <div class="payment-method" id="add-payment-method">
            <h3>Payment method</h3>
            <span class="add-method"><i class="fa fa-plus" aria-hidden="true"></i>
            <span>
          </div>
          <?php foreach ($cart_items as $item): ?>
          <?php $total_price += $item['ticket_price'] * $item['quantity']; ?>
          <div class="cart-item">
              <img src="<?php echo htmlspecialchars($item['event_picture']); ?>" alt="Event Picture"
                class="cart-event-picture">
            <div class="item-details">
                <h2 class="item-name">
                  <?php echo htmlspecialchars(ucfirst($item['event_name'])); ?>
                </h2>
              <div class="line">
                <p>Quantity: <?php echo $item['quantity']; ?></p>
                <p>RM <?php echo number_format($item['ticket_price'], 2); ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="card-info">
          <div class="payment-popup" id="payment-popup">
            <div class="payment-popup-content">
              <span class="close-btn" id="close-popup">&times;</span>
              <form id="payment-form" action="process_payment.php" method="POST">
                <div class="input-group">
                  <input type="text" id="card_number" name="card_number" required>
                  <span class="input-placeholder">Card number</span>
                </div>
                <div class="input-group-row">
                  <div class="input-group">
                    <input type="text" id="expiry_date" name="expiry_date" required>
                    <span class="input-placeholder">MM/YY</span>
                  </div>
                  <div class="input-group">
                    <input type="text" id="security_code" name="security_code" required>
                    <span class="input-placeholder">Security code</span>
                  </div>
                </div>
                <div class="input-group">
                  <input type="text" id="cardholder_name" name="cardholder_name" required>
                  <span class="input-placeholder">Cardholder name</span>
                </div>
              </form>
            </div>
            <div class="overlay"></div>
          </div>
        </div>
        <div class="order-summary">
          <h2>Order Summary</h2>
          <div class="summary-line">
            <p>Subtotal</p>
            <p id="subtotal">RM <?php echo number_format($total_price, 2); ?></p>
          </div>
          <div class="summary-line">
            <h3>Total</h3>
            <h3 id="total">RM <?php echo number_format($total_price, 2); ?></h3>
          </div>
          <button form="payment-form" type="submit">Confirm purchase</button>
        </div>
      </div>
      <?php else: ?>
      <p>Your cart is empty.</p>
      <h1 class="cart-title2">Your cart is empty</h1>
      <div class="continue-shopping-button">
        <a href="home.php" class="continue-shopping">Continue shopping</a>
      </div>
      <img class="empty-cart-image" src="images/empty_cart_logo.png" alt="Empty Cart Image">
      <?php endif; ?>
    </div>
  </main>

  <script>
    document.getElementById('add-payment-method').addEventListener('click', function () {
      document.getElementById('payment-popup').style.display = 'flex';
    });

    document.getElementById('close-popup').addEventListener('click', function () {
      document.getElementById('payment-popup').style.display = 'none';
    });

    window.addEventListener('click', function (event) {
      if (event.target == document.getElementById('payment-popup')) {
        document.getElementById('payment-popup').style.display = 'none';
      }
    });
  </script>
</body>

</html>
