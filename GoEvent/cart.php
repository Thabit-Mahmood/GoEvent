<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in or is not a regular user, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the number of items in the user's cart
$cart_count_stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
$cart_count_stmt->bind_param("i", $user_id);
$cart_count_stmt->execute();
$cart_count_result = $cart_count_stmt->get_result();
$cart_count_data = $cart_count_result->fetch_assoc();
$total_items_in_cart = $cart_count_data['total_items'] ?? 0;

// Handle remove item actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $event_id = $_POST['event_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit();
}

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
  <title>Cart - GoEvent</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="cart-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <main class="cart-container">
    <div class="cart-content">
      <h1>
        <span class="cart-title">Cart</span>
        <span class="cart-items-num">(<?php echo $total_items_in_cart;
        echo $total_items_in_cart == 1 ? " item" : " items";
        ?>)
        </span>
      </h1>
      <a href="home.php" class="continue-shopping">Continue shopping</a>
      <?php if (count($cart_items) > 0): ?>
      <div class="cart-wrapper">
        <div class="cart-items">
          <?php foreach ($cart_items as $item): ?>
          <?php $total_price += $item['ticket_price'] * $item['quantity']; ?>
          <div class="cart-item">
            <img src="<?php echo htmlspecialchars($item['event_picture']); ?>" alt="Event Picture"
              class="cart-event-picture">
            <div class="item-details">
              <h2>
                <?php echo htmlspecialchars(ucfirst($item['event_name'])); ?>
              </h2>
              <div class="line">
                <form action="cart.php" method="POST" class="update-form">
                  <label for="quantity-<?php echo $item['event_id']; ?>">Qty:</label>
                  <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required
                    class="quantity-input" data-event-id="<?php echo $item['event_id']; ?>">
                  <input type="hidden" name="event_id" value="<?php echo $item['event_id']; ?>">
                </form>
                <p>RM
                  <?php echo number_format($item['ticket_price'], 2); ?>
                </p>
              </div>
              <form action="cart.php" method="POST">
                <input type="hidden" name="event_id" value="<?php echo $item['event_id']; ?>">
                <div class="remove-container">
                  <button type="submit" name="remove_item" class="remove-from-cart">Remove</button>
                </div>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="order-summary">
          <h2>Order summary</h2>
          <p>Subtotal: RM<span id="subtotal"><?php echo number_format($total_price, 2); ?>
            </span></p>
          <h3>Estimated total: RM<span id="total"><?php echo number_format($total_price, 2); ?>
            </span></h3>
          <button onclick="location.href='checkout.php'">Proceed to Checkout</button>
        </div>
        <?php else: ?>
        <p>Your cart is empty.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    $(document).ready(function () {
      $('.quantity-input').on('change', function () {
        var event_id = $(this).data('event-id');
        var quantity = $(this).val();
        $.ajax({
          url: 'update_cart.php',
          method: 'POST',
          data: { event_id: event_id, quantity: quantity },
          success: function (response) {
            var data = JSON.parse(response);
            if (data.success) {
              location.reload();
            } else {
              alert('Failed to update cart');
            }
          }
        });
      });
    });
  </script>
</body>

</html>