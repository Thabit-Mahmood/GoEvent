<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    // If the user is not logged in or is not an organizer, redirect to the login page
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Validate the event ID

if (!$event_id) {
    header("Location: organizerpanel.php");
    exit();
}

// Fetch event details from the database
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    // If the event is not found or not owned by the organizer, redirect to the organizer panel
    header("Location: organizerpanel.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
    $event_description = filter_input(INPUT_POST, 'event_description', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $ticket_price = filter_input(INPUT_POST, 'ticket_price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    // Handle file upload
    $event_picture = $event['event_picture'];
    if ($_FILES['event_picture']['name']) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["event_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["event_picture"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["event_picture"]["size"] > 500000) {
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $error = "Sorry, your file was not uploaded.";
        // If everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["event_picture"]["tmp_name"], $target_file)) {
                $event_picture = $target_file;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (!$error) {
        // Update the event in the database
        $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_description = ?, event_date = ?, ticket_price = ?, category_id = ?, event_picture = ? WHERE event_id = ? AND organizer_id = ?");
        $stmt->bind_param("sssdisii", $event_name, $event_description, $event_date, $ticket_price, $category_id, $event_picture, $event_id, $organizer_id);

        if ($stmt->execute()) {
            $success = 'Event updated successfully!';
            header("Location: organizerpanel.php");
            exit();
        } else {
            $error = 'Error updating event. Please try again.';
        }
    }
}

// Fetch categories from the database
$stmt = $conn->prepare("SELECT category_id, category_name FROM event_categories");
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="create-event-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <h1 class="myevents-heading">Edit Event</h1>
    <div class="create-event-container">
        <?php if ($error) : ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($success) : ?>
            <p class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form action="edit_event.php?id=<?php echo $event_id; ?>" method="POST" enctype="multipart/form-data" class="register-container">
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="text" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event['event_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label class="input-placeholder" for="event_name">Event Name</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="text" id="event_description" name="event_description" value="<?php echo htmlspecialchars($event['event_description'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label class="input-placeholder" for="event_description">Event Description</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="number" id="ticket_price" name="ticket_price" value="<?php echo htmlspecialchars($event['ticket_price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <label class="input-placeholder" for="ticket_price">Ticket Price</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="datetime-local" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php if ($category['category_id'] == $event['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="input-placeholder" for="category_id">Category</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="file" id="event_picture" name="event_picture">
                    <label class="input-placeholder" for="event_picture">Event Picture</label>
                </div>
            </div>
            <?php if ($event['event_picture']): ?>
                    <img src="<?php echo htmlspecialchars($event['event_picture'], ENT_QUOTES, 'UTF-8'); ?>" alt="Event Picture" class="event-thumbnail">
                <?php endif; ?>
            <button type="submit" class="create-account-btn">Update Event</button>
        </form>
    </div>
</body>

</html>
