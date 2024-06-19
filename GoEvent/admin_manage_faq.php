<?php
session_start();
require 'db.php'; // Include the database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch unanswered and answered questions
$stmt = $conn->prepare("SELECT q.question_id, q.question_text, q.submitted_at, u.username AS asked_by FROM user_questions q JOIN users u ON q.user_id = u.user_id WHERE q.question_id NOT IN (SELECT question_id FROM faq)");
$stmt->execute();
$result = $stmt->get_result();
$unanswered_questions = $result->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT f.faq_id, f.answer_text, f.answered_at, q.question_text, u.username AS asked_by FROM faq f JOIN user_questions q ON f.question_id = q.question_id JOIN users u ON q.user_id = u.user_id");
$stmt->execute();
$result = $stmt->get_result();
$answered_questions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQ - GoEvent</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="admin-container">
        <h1>Manage FAQ</h1>
        <section class="admin-section">
            <h2>Unanswered Questions</h2>
            <?php if (count($unanswered_questions) > 0): ?>
                <table class="questions-table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Asked By</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unanswered_questions as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($question['asked_by']); ?></td>
                                <td><?php echo htmlspecialchars($question['submitted_at']); ?></td>
                                <td>
                                    <form method="POST" action="admin_answer_faq.php" style="display:inline;">
                                        <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                        <textarea name="answer_text" required></textarea>
                                        <button type="submit">Answer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No unanswered questions available.</p>
            <?php endif; ?>
        </section>
        <section class="admin-section">
            <h2>Answered Questions</h2>
            <?php if (count($answered_questions) > 0): ?>
                <table class="questions-table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Asked By</th>
                            <th>Answered At</th>
                            <th>Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($answered_questions as $faq): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faq['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($faq['asked_by']); ?></td>
                                <td><?php echo htmlspecialchars($faq['answered_at']); ?></td>
                                <td><?php echo htmlspecialchars($faq['answer_text']); ?></td>
                                <td>
                                    <form method="POST" action="admin_edit_faq.php" style="display:inline;">
                                        <input type="hidden" name="faq_id" value="<?php echo $faq['faq_id']; ?>">
                                        <textarea name="answer_text" required><?php echo htmlspecialchars($faq['answer_text']); ?></textarea>
                                        <button type="submit">Edit</button>
                                    </form>
                                    <form method="POST" action="admin_delete_faq.php" style="display:inline;">
                                        <input type="hidden" name="faq_id" value="<?php echo $faq['faq_id']; ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No answered questions available.</p>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
