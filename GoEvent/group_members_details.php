<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group member's details - GoEvent</title>
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .student-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ddd;
            padding: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .events-table th, .events-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .events-table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }

        .events-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .events-table tr:hover {
            background-color: #f1f1f1;
        }

        .events-table th i {
            margin-left: 5px;
            color: #888;
        }

        .contact-link {
            color: #3498db;
            text-decoration: none;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

        figure {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
        }

        figcaption {
            margin-top: 5px;
            font-size: 0.9em;
            color: #555;
        }

        .welcome {
            text-align: center;
            margin: 20px 0;
        }

        .welcome h1 {
            font-size: 2.5em;
            color: #333;
        }

        .organizer-section {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .organizer-section table {
            width: 100%;
        }
    </style>
</head>

<body class="manage-events-body">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main class="organizer-event-container">
        <div class="welcome">
            <h1 class="myevents-heading">Group member's details</h1>
        </div>
        <section class="organizer-section">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Name <i class="fa fa-user"></i></th>
                        <th>Student ID <i class="fa fa-id-badge"></i></th>
                        <th>Section <i class="fa fa-building"></i></th>
                        <th>Photo <i class="fa fa-image"></i></th>
                        <th>Contact <i class="fa fa-envelope"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>AL-HELALI, MARWAH ZAID MOHAMMED</td>
                        <td>1211307415</td>
                        <td>TC1L</td>
                        <td>
                            <figure>
                                <img src="images\Marwah.jpg" alt="Photo of Marwah" class="student-photo">
                                <figcaption>Marwah</figcaption>
                            </figure>
                        </td>
                        <td><a href="mailto:1211307415@student.mmu.edu.my" class="contact-link">1211307415@student.mmu.edu.my</a></td>
                    </tr>
                    <tr>
                        <td>AMIERLYN BINTI AZMAN</td>
                        <td>1201101750</td>
                        <td>TC1L</td>
                        <td>
                            <figure>
                                <img src="images\Amierlyn.jpg" alt="Photo of Amierlyn" class="student-photo">
                                <figcaption>Amierlyn</figcaption>
                            </figure>
                        </td>
                        <td><a href="mailto:1201101750@student.mmu.edu.my" class="contact-link">1201101750@student.mmu.edu.my</a></td>
                    </tr>
                    <tr>
                        <td>THABIT MAHMOOD THABIT AHMED IBRAHIM</td>
                        <td>1211305813</td>
                        <td>TC1L</td>
                        <td>
                            <figure>
                                <img src="images\Thabit.jpg" alt="Photo of Thabit" class="student-photo">
                                <figcaption>Thabit</figcaption>
                            </figure>
                        </td>
                        <td><a href="mailto:1211305813@student.mmu.edu.my" class="contact-link">1211305813@student.mmu.edu.my</a></td>
                    </tr>
                    <tr>
                        <td>ZAMAN, SYED NOOR UZ</td>
                        <td>1211304183</td>
                        <td>TC1L</td>
                        <td>
                            <figure>
                                <img src="images\Zaman.jpg" alt="Photo of Zaman" class="student-photo">
                                <figcaption>Zaman</figcaption>
                            </figure>
                        </td>
                        <td><a href="mailto:1211304183@student.mmu.edu.my" class="contact-link">1211304183@student.mmu.edu.my</a></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
