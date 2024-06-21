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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="faq-body"> 
    <header>
        <?php include 'header.php'; ?>
    </header>
    <h1 class="myevents-heading">Frequently Asked Questions</h1>
    <main class="faq-container">
        <section class="faq-section">
            <?php if (count($faqs) > 0): ?>
                <div class="faq-list">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-item">
                            <div class="faq-question" onclick="toggleAnswer(this)">
                                <span><?php echo htmlspecialchars($faq['question_text']); ?></span>
                                <span class="toggle-icon">+</span>
                            </div>
                            <div class="faq-answer">
                                <p><?php echo htmlspecialchars($faq['answer_text']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-faq">No FAQs available.</p>
            <?php endif; ?>
        </section>
        <section class="faq-section ask-question-section">
            <h2 class="ask-question-title">Ask a Question</h2>
            <form method="POST" action="ask_question.php" class="ask-question-form">
                <textarea name="question_text" placeholder="Type your question here..." required></textarea>
                <button type="submit">Submit Question</button>
            </form>
        </section>
    </main>

    <script>
        function toggleAnswer(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('.toggle-icon');
            if (answer.style.display === 'block') {
                answer.style.display = 'none';
                icon.textContent = '+';
            } else {
                answer.style.display = 'block';
                icon.textContent = '×';
            }
        }
    </script>
</body>

</html>
