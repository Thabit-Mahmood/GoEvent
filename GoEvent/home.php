<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    header("Location: login.php");
    exit();
}

// Fetch categories for the filter
$category_stmt = $conn->prepare("SELECT * FROM event_categories");
$category_stmt->execute();
$category_result = $category_stmt->get_result();
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

// Handle search and filters
$search_query = "";
$category_filter = "";
$date_filter = "";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';

    $sql = "SELECT event_id, event_name, event_description, event_picture, event_date, ticket_price FROM events WHERE pending_approval = 0";

    if (!empty($search_query)) {
        $sql .= " AND (event_name LIKE CONCAT('%', ?, '%') OR event_description LIKE CONCAT('%', ?, '%'))";
    }

    if (!empty($category_filter)) {
        $category_filter_str = implode(',', (array)$category_filter); // Convert array to comma-separated string
        $sql .= " AND category_id IN ($category_filter_str)";
    }

    if (!empty($date_filter)) {
        $sql .= " AND DATE(event_date) = ?";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($search_query) && !empty($category_filter) && !empty($date_filter)) {
        $stmt->bind_param("sss", $search_query, $search_query, $date_filter);
    } elseif (!empty($search_query) && !empty($category_filter)) {
        $stmt->bind_param("ss", $search_query, $search_query);
    } elseif (!empty($search_query) && !empty($date_filter)) {
        $stmt->bind_param("ss", $search_query, $search_query, $date_filter);
    } elseif (!empty($category_filter) && !empty($date_filter)) {
        $stmt->bind_param("ss", $category_filter, $date_filter);
    } elseif (!empty($search_query)) {
        $stmt->bind_param("ss", $search_query, $search_query);
    } elseif (!empty($date_filter)) {
        $stmt->bind_param("s", $date_filter);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Default event fetch
    $stmt = $conn->prepare("SELECT event_id, event_name, event_description, event_picture, event_date, ticket_price FROM events WHERE pending_approval = 0 ORDER BY event_date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - GoEvent</title>
  <link rel="stylesheet" href="css/style.css?v=1.1">
  <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body class="home-body">
  <header>
    <?php include 'header.php'; ?>
  </header>
  <div class="welcome-banner">
    <h1>Welcome,
      <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?>!
    </h1>
    <p>Explore the full library of Events and Seminars</p>
  </div>
  <main class="home-container">
    <div class="search-bar-container">
      <form method="GET" action="" id="search-filter-form">
        <div class="search-wrapper">
          <i class="fa fa-search search-icon"></i>
          <input type="text" name="search" placeholder="Search events" class="search-bar"
            value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
      </form>
    </div>

    <div class="content-container">
      <div class="search-filter">
        <form method="GET" action="" id="filter-form">
          <div class="filter-section">
            <h3>FILTER BY</h3>
            <h4>Category</h4>
            <?php foreach ($categories as $category): ?>
            <div class="filter-item">
              <input type="checkbox" name="category[]" value="<?php echo $category['category_id']; ?>" <?php if
                (is_array($category_filter) && in_array($category['category_id'], $category_filter)) echo 'checked' ;
                ?>>
              <label>
                <?php echo htmlspecialchars($category['category_name']); ?>
              </label>
            </div>
            <?php endforeach; ?>
            <hr class="hr">

            <div class="filter-item">
              <label for="date">Date:</label>
              <input type="text" id="date-picker" name="date" class="filter-date"
                value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
          </div>
        </form>
      </div>
      <div class="events-container">
        <div class="mobile-filter">
          <button class="filter-button" onclick="showCategoryOverlay()">Category</button>
          <button class="filter-button" onclick="showDateOverlay()">Date</button>
        </div>
        <h4 class="results">
          <?php echo count($events); ?> RESULTS
        </h4>
        <h3 class="toppicks">Top picks</h3>
        <div class="events">
          <?php if (count($events) > 0): ?>
          <?php foreach ($events as $event): ?>
          <?php 
            if (isset($event['event_date'])) {
              $event_date = new DateTime($event['event_date']);
              $formatted_date = $event_date->format('d/m/Y \a\t h:ia');
            } else {
              $formatted_date = 'Date not set';
            }
          ?>
          <div class="event-card">
            <a class="link-card" href="event.php?id=<?php echo $event['event_id']; ?>">
              <?php if ($event['event_picture']): ?>
              <div class="event-thumbnail-container">
                <img src="<?php echo htmlspecialchars($event['event_picture']); ?>" alt="Event Picture"
                  class="event-thumbnail">
              </div>
              <?php endif; ?>
              <div class="event-details">
                <h3>
                  <?php echo htmlspecialchars(ucfirst($event['event_name'])); ?>
                </h3>
                <p><i class="fa fa-calendar" aria-hidden="true"></i>
                  <?php echo htmlspecialchars($formatted_date); ?>
                </p>
                <p><i class="fa fa-tag" aria-hidden="true"></i>
                  RM
                  <?php echo number_format($event['ticket_price'], 2); ?>
                </p>
                <p class="event-description">
                  <?php echo htmlspecialchars(ucfirst($event['event_description'])); ?>
                </p>
                <a href="event.php?id=<?php echo $event['event_id']; ?>" class="event-link"><i
                    class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <p>No events available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <div id="category-filter-overlay" class="filter-overlay">
    <div class="filter-overlay-content">
      <span class="close-btn" onclick="closeCategoryOverlay()">&times;</span>
      <h3>Filter by Category</h3>
      <form method="GET" action="">
        <div class="overlay-filter-item">
          <h4>Category</h4>
          <?php foreach ($categories as $category): ?>
          <div class="filter-item">
            <input type="checkbox" name="category[]" value="<?php echo $category['category_id']; ?>" <?php if
              (is_array($category_filter) && in_array($category['category_id'], $category_filter)) echo 'checked' ; ?>>
            <label>
              <?php echo htmlspecialchars($category['category_name']); ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="overlay-filter-item">
          <button type="submit" class="apply-filters-btn">View Results</button>
        </div>
      </form>
    </div>
  </div>

  <div id="date-filter-overlay" class="filter-overlay">
    <div class="filter-overlay-content">
      <span class="close-btn" onclick="closeDateOverlay()">&times;</span>
      <h2>Filter by Date</h2>
      <form method="GET" action="">
        <div class="overlay-filter-item">
          <label for="overlay-date-picker">Date:</label>
          <input type="text" id="overlay-date-picker" name="date" class="filter-date"
            value="<?php echo htmlspecialchars($date_filter); ?>">
        </div>
        <div class="overlay-filter-item">
          <button type="submit" class="apply-filters-btn">Apply Date Filters</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function showCategoryOverlay() {
      document.getElementById('category-filter-overlay').style.display = 'block';
    }

    function closeCategoryOverlay() {
      document.getElementById('category-filter-overlay').style.display = 'none';
    }

    function showDateOverlay() {
      document.getElementById('date-filter-overlay').style.display = 'block';
    }

    function closeDateOverlay() {
      document.getElementById('date-filter-overlay').style.display = 'none';
    }

    $(document).ready(function () {
      $('#filter-form input').on('change', function () {
        $('#filter-form').submit();
      });

      flatpickr("#date-picker, #overlay-date-picker", {
        dateFormat: "Y-m-d",
        onChange: function (selectedDates, dateStr, instance) {
          $('#filter-form').submit();
        }
      });
    });
  </script>
</body>

</html>