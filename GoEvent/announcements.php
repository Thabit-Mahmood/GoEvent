<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // If the user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

$error = '';
$success = '';

// Handle form submission for adding or editing announcements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announcement_text'])) {
    $announcement_text = $_POST['announcement_text'];
    $announcement_id = isset($_POST['announcement_id']) ? $_POST['announcement_id'] : null;

    if ($announcement_id) {
        // Edit existing announcement
        $stmt = $conn->prepare("UPDATE announcements SET announcement_text = ? WHERE announcement_id = ?");
        $stmt->bind_param("si", $announcement_text, $announcement_id);
        if ($stmt->execute()) {
            $success = "Announcement updated successfully!";
        } else {
            $error = "Error updating announcement. Please try again.";
        }
    } else {
        // Add new announcement
        $stmt = $conn->prepare("INSERT INTO announcements (user_id, announcement_text) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $announcement_text);
        if ($stmt->execute()) {
            $success = "Announcement added successfully!";
        } else {
            $error = "Error adding announcement. Please try again.";
        }
    }
    // Redirect to clear POST data
    header("Location: announcements.php");
    exit();
}

// Handle deletion of announcements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_announcement_id'])) {
    $delete_announcement_id = $_POST['delete_announcement_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
    $stmt->bind_param("i", $delete_announcement_id);
    if ($stmt->execute()) {
        $success = "Announcement deleted successfully!";
    } else {
        $error = "Error deleting announcement. Please try again.";
    }
    // Redirect to clear POST data
    header("Location: announcements.php");
    exit();
}

// Fetch all announcements
$stmt = $conn->prepare("SELECT a.announcement_id, a.announcement_text, a.created_at, u.username FROM announcements a JOIN users u ON a.user_id = u.user_id ORDER BY a.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .manage-events-body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .organizer-event-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }

        .organizer-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .error {
            color: #d9534f;
            background-color: #f2dede;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .success {
            color: #5cb85c;
            background-color: #dff0d8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .announcement-list {
            list-style: none;
            padding: 0;
        }

        .announcement-item {
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .announcement-item p {
            margin: 0;
        }

        .announcement-item small {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }

        .btn {
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 5px;
            font-size: 0.9rem;
        }

        .btn-edit {
            background-color: #007bff;
            color: #fff;
        }

        .btn-delete {
            background-color: #dc3545;
            color: #fff;
        }

        .overlay-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #announcement-form {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .input-wrapper {
            margin-bottom: 15px;
        }

        .input-group textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            resize: vertical;
            font-size: 1rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .create-account-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>

<body class="manage-events-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="organizer-event-container">
        <div class="welcome">
            <h1 class="myevents-heading">Announcements</h1>
        </div>
        <section class="organizer-section">
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <?php if ($user_type === 'admin'): ?>
                <button class="overlay-button" onclick="showForm()"><i class="fa fa-plus"></i></button>
                <div id="announcement-form" style="display: none;">
                    <h2>Add / Edit Announcement</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="announcement_id" id="announcement_id">
                        <div class="input-wrapper">
                            <div class="input-group">
                                <textarea id="announcement_text" name="announcement_text" required></textarea>
                                <label class="input-placeholder" for="announcement_text">Announcement Text</label>
                            </div>
                        </div>
                        <button type="submit" class="create-account-btn">Save Announcement</button>
                    </form>
                </div>
            <?php endif; ?>
            <ul class="announcement-list">
                <?php foreach ($announcements as $announcement): ?>
                    <li class="announcement-item">
                        <p><?php echo htmlspecialchars($announcement['announcement_text']); ?></p>
                        <small>By <?php echo htmlspecialchars($announcement['username']); ?> on <?php echo htmlspecialchars($announcement['created_at']); ?></small>
                        <?php if ($user_type === 'admin'): ?>
                            <div class="action-buttons">
                                <button class="btn btn-edit" onclick="editAnnouncement('<?php echo $announcement['announcement_id']; ?>', '<?php echo htmlspecialchars($announcement['announcement_text']); ?>')"><i class="fa fa-edit"></i> Edit</button>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="delete_announcement_id" value="<?php echo $announcement['announcement_id']; ?>">
                                    <button type="submit" class="btn btn-delete"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
    <script>
        function showForm() {
            document.getElementById('announcement-form').style.display = 'block';
        }

        function editAnnouncement(id, text) {
            document.getElementById('announcement_id').value = id;
            document.getElementById('announcement_text').value = text;
            showForm();
        }
    </script>
</body>

</html>
