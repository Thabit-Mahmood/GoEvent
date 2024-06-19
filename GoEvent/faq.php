<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'regular') {
    header("Location: login.php");
    exit();
}

// Fetch FAQs
$stmt = $conn->prepare("SELECT q.question_text, f.answer_text FROM faq f JOIN user_questions q ON f.question_id = q.question_id");
$stmt->execute();
$result = $stmt->get_result();
$faqs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="faq-container">
        <h1>Frequently Asked Questions</h1>
        <section class="faq-section">
            <?php if (count($faqs) > 0): ?>
                <ul class="faq-list">
                    <?php foreach ($faqs as $faq): ?>
                        <li>
                            <strong>Q: <?php echo htmlspecialchars($faq['question_text']); ?></strong>
                            <p>A: <?php echo htmlspecialchars($faq['answer_text']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No FAQs available.</p>
            <?php endif; ?>
        </section>
        <section class="faq-section">
            <h2>Ask a Question</h2>
            <form method="POST" action="ask_question.php">
                <textarea name="question_text" required></textarea>
                <button type="submit">Submit Question</button>
            </form>
        </section>
    </main>
</body>

</html>
