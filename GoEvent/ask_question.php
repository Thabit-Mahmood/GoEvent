<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $question_text = trim($_POST['question_text']);

    if (!empty($question_text)) {
        $stmt = $conn->prepare("INSERT INTO user_questions (user_id, question_text) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $question_text);
        if ($stmt->execute()) {
            header("Location: faq.php?success=1");
            exit();
        } else {
            $error = "An error occurred while submitting your question. Please try again.";
        }
    } else {
        $error = "Please enter your question.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Question - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="ask-question-container">
        <h1>Ask a Question</h1>
        <section class="ask-question-section">
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="ask_question.php">
                <textarea name="question_text" required></textarea>
                <button type="submit">Submit Question</button>
            </form>
        </section>
    </main>
</body>

</html>
