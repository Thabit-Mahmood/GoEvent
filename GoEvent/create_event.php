<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$error = '';

// Fetch categories from the database
$stmt = $conn->prepare("SELECT category_id, category_name FROM event_categories");
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $event_description = $_POST['event_description'];
    $event_date = $_POST['event_date'];
    $ticket_price = $_POST['ticket_price'];
    $category_id = $_POST['category_id'];
    $organizer_id = $_SESSION['user_id'];
    
    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["event_picture"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is an actual image or fake image
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

            // Insert the event into the database
            $stmt = $conn->prepare("INSERT INTO events (organizer_id, event_name, event_description, event_date, ticket_price, category_id, event_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdss", $organizer_id, $event_name, $event_description, $event_date, $ticket_price, $category_id, $event_picture);

            if ($stmt->execute()) {
                header("Location: organizerpanel.php");
                exit();
            } else {
                $error = 'Error creating event. Please try again.';
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="create-event-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <h1 class="myevents-heading">Create Event</h1>
    <div class="create-event-container">
        <?php if ($error) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="create_event.php" method="POST" enctype="multipart/form-data" class="register-container">
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="text" id="event_name" name="event_name" required>
                    <label class="input-placeholder" for="event_name">Event Name</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="text" id="event_description" name="event_description" required>
                    <label class="input-placeholder" for="event_description">Event Description</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="number" id="ticket_price" name="ticket_price" required>
                    <label class="input-placeholder" for="ticket_price">Ticket Price</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="datetime-local" id="event_date" name="event_date" required>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <select id="category_id" name="category_id" required>
                        <option value=""></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="input-placeholder" for="category_id">Category</label>
                </div>
            </div>
            <div class="input-wrapper">
                <div class="input-group">
                    <input type="file" id="event_picture" name="event_picture" required>
                    <label class="input-placeholder" for="event_picture">Event Picture</label>
                </div>
            </div>
            <button type="submit" class="create-account-btn">Create Event</button>
        </form>
    </div>
</body>

</html>
