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

$total_items_in_cart = 0;
foreach ($cart_items as $item) {
    $total_items_in_cart += $item['quantity'];
}
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
      <h1>
        <span class="cart-title">Checkout</span>
        <span class="cart-items-num">(<?php echo $total_items_in_cart; 
        echo $total_items_in_cart == 1 ? " item" : " items"; 
        ?>)
        </span>
      </h1>
      <?php if (count($cart_items) > 0): ?>
      <div class="cart-wrapper">
        <div class="cart-items">
          <div class="payment-method" id="add-payment-method">
            <h3>Payment method</h3>
            <span class="add-method"><i class="fa fa-plus" aria-hidden="true"></i></span>
          </div>
          <div class="saved-card-info" id="saved-card-info" style="display:none;">
            <div class="payment-method">
              <h3>Payment method</h3>
              <span class="saved-card"></span>
            </div>
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
              <form id="card-details-form">
                <div class="input-group">
                  <input type="text" id="card_number" name="card_number" required>
                  <span class="input-placeholder">Card number</span>
                </div>
                <span class="card-error-message" id="card-number-error"></span>
                <div class="input-group-row">
                  <div class="input-group">
                    <input type="text" id="expiry_date" name="expiry_date" required>
                    <span class="input-placeholder">MM/YY</span>
                  </div>
                  <span class="card-error-message" id="expiry-date-error"></span>
                  <div class="input-group">
                    <input type="text" id="security_code" name="security_code" required>
                    <span class="input-placeholder">Security code</span>
                  </div>
                  <span class="card-error-message" id="security-code-error"></span>
                </div>
                <div class="input-group">
                  <input type="text" id="cardholder_name" name="cardholder_name" required>
                  <span class="input-placeholder">Cardholder name</span>
                </div>
                <span class="card-error-message" id="cardholder-name-error"></span>
                <button type="button" id="save-card-btn" class="save-card-btn">Save Card Details</button>
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
          <form id="payment-form" action="process_payment.php" method="POST">
            <!-- Hidden inputs to carry card details -->
            <input type="hidden" id="hidden_card_number" name="card_number">
            <input type="hidden" id="hidden_expiry_date" name="expiry_date">
            <input type="hidden" id="hidden_security_code" name="security_code">
            <input type="hidden" id="hidden_cardholder_name" name="cardholder_name">
            <button type="submit">Confirm purchase</button>
            <span class="no-card-error-message" id="no-card-error"></span>
          </form>
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

    document.getElementById('card_number').addEventListener('input', function (event) {
      const value = event.target.value.replace(/\D/g, ''); // Remove all non-digit characters
      const formattedValue = value.replace(/(.{4})/g, '$1 ').trim(); // Add a space every 4 digits
      event.target.value = formattedValue.substring(0, 19); // Limit to 16 digits + 3 spaces
    });

    document.getElementById('expiry_date').addEventListener('input', function (event) {
      const value = event.target.value.replace(/\D/g, ''); // Remove all non-digit characters
      if (value.length >= 2) {
        event.target.value = value.substring(0, 2) + '/' + value.substring(2, 4); // Add slash after MM
      } else {
        event.target.value = value;
      }
    });

    document.getElementById('security_code').addEventListener('input', function (event) {
      const value = event.target.value.replace(/\D/g, ''); // Remove all non-digit characters
      event.target.value = value.substring(0, 3); // Limit to 3 digits
    });

    document.getElementById('save-card-btn').addEventListener('click', function () {
      const cardNumber = document.getElementById('card_number').value.trim();
      const expiryDate = document.getElementById('expiry_date').value.trim();
      const securityCode = document.getElementById('security_code').value.trim();
      const cardholderName = document.getElementById('cardholder_name').value.trim();
      
      // Reset error messages
      const resetErrors = () => {
        document.getElementById('card-number-error').textContent = '';
        document.getElementById('expiry-date-error').textContent = '';
        document.getElementById('security-code-error').textContent = '';
        document.getElementById('cardholder-name-error').textContent = '';
        document.getElementById('no-card-error').textContent = '';
        document.getElementById('card-number-error').style.display = 'none';
        document.getElementById('expiry-date-error').style.display = 'none';
        document.getElementById('security-code-error').style.display = 'none';
        document.getElementById('cardholder-name-error').style.display = 'none';
        document.getElementById('no-card-error').style.display = 'none';
      };

      // Validate card number
      const validateCardNumber = () => {
        if (!cardNumber) {
          document.getElementById('card-number-error').textContent = 'Card number required';
          document.getElementById('card-number-error').style.display = 'block';
          return false;
        }
        const digitsOnly = cardNumber.replace(/\s/g, '');
        if (digitsOnly.length !== 16) {
          document.getElementById('card-number-error').textContent = 'Card number must be 16 digits';
          document.getElementById('card-number-error').style.display = 'block';
          return false;
        }
        return true;
      };

      // Validate expiry date
      const validateExpiryDate = () => {
        const expiryRegex = /^(0[1-9]|1[0-2])\/[0-9]{2}$/;
        if (!expiryDate || !expiryRegex.test(expiryDate)) {
          document.getElementById('expiry-date-error').textContent = 'Enter a valid expiration date (MM/YY)';
          document.getElementById('expiry-date-error').style.display = 'block';
          return false;
        }
        const [month, year] = expiryDate.split('/');
        const currentYear = new Date().getFullYear() % 100; // Get last two digits of current year
        const currentMonth = new Date().getMonth() + 1;
        if (year < currentYear || (year == currentYear && month < currentMonth)) {
          document.getElementById('expiry-date-error').textContent = 'Card Expired';
          document.getElementById('expiry-date-error').style.display = 'block';
          return false;
        }
        if (year > currentYear + 10) {
          document.getElementById('expiry-date-error').textContent = 'Year is too far into the future';
          document.getElementById('expiry-date-error').style.display = 'block';
          return false;
        }
        return true;
      };

      // Validate security code
      const validateSecurityCode = () => {
        if (!securityCode || securityCode.length !== 3) {
          document.getElementById('security-code-error').textContent = 'Security code is required';
          document.getElementById('security-code-error').style.display = 'block';
          return false;
        }
        return true;
      };

      // Validate cardholder name
      const validateCardholderName = () => {
        if (!cardholderName) {
          document.getElementById('cardholder-name-error').textContent = 'Cardholder name required';
          document.getElementById('cardholder-name-error').style.display = 'block';
          return false;
        }
        return true;
      };

      // Validate form
      resetErrors();
      if (!validateCardNumber()) return;
      if (!validateExpiryDate()) return;
      if (!validateSecurityCode()) return;
      if (!validateCardholderName()) return;

      // Save card details to hidden form inputs
      document.getElementById('hidden_card_number').value = cardNumber;
      document.getElementById('hidden_expiry_date').value = expiryDate;
      document.getElementById('hidden_security_code').value = securityCode;
      document.getElementById('hidden_cardholder_name').value = cardholderName;

      // Display the last 4 digits of the card number
      const last4Digits = cardNumber.slice(-4);
      document.querySelector('.saved-card').textContent = `.... ${last4Digits}`;

      // Hide the add payment method and show the saved card info
      document.getElementById('add-payment-method').style.display = 'none';
      document.getElementById('saved-card-info').style.display = 'block';

      // Close the popup
      document.getElementById('payment-popup').style.display = 'none';
    });

    document.getElementById('payment-form').addEventListener('submit', function (event) {
      const cardNumber = document.getElementById('hidden_card_number').value;
      if (!cardNumber) {
        event.preventDefault();
        document.getElementById('no-card-error').textContent = 'Please add a payment method';
        document.getElementById('no-card-error').style.display = 'block';
      }
    });
  </script>
</body>

</html>
