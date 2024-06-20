-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 20, 2024 at 11:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goevent`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `announcement_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `user_id`, `announcement_text`, `created_at`) VALUES
(47, 3, 'We are going to update the platform', '2024-06-20 21:02:54'),
(48, 3, 'We have ne events coming up', '2024-06-20 21:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `booked_events`
--

CREATE TABLE `booked_events` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `card_number_last4` varchar(4) NOT NULL,
  `encrypted_card_number` text NOT NULL,
  `hashed_expiry_date` varchar(64) NOT NULL,
  `hashed_security_code` varchar(64) NOT NULL,
  `hashed_cardholder_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booked_events`
--

INSERT INTO `booked_events` (`booking_id`, `user_id`, `event_id`, `quantity`, `total_price`, `booking_date`, `card_number_last4`, `encrypted_card_number`, `hashed_expiry_date`, `hashed_security_code`, `hashed_cardholder_name`) VALUES
(27, 2, 1, 1, 15.00, '2024-06-20 09:49:51', '2456', 'dlVk9AHzjsyUeeIcCcMsy/WL7Eoi2nJ49+gM1MOG22k=', 'b8b0c9d117c29ed80c6c6ebeec81699fa5b331303af1f4e1142faaa6dbb25600', 'dc8cdedfa211d0007f291a8434924c451ba8b6d50743f012f0d922332bd29647', '414bbbdf58f286c5e496d7c803710cd31aa258606333b503d787a3c556fa778f'),
(28, 2, 1, 2, 30.00, '2024-06-20 11:47:18', '3233', 'G23K6II+wvJNr1tGEU24l0qN6n49HoGFMFGTyCDqW+E=', '3212c57b73ff0360aeb888fd87b7d91bf90beb71ec725329799c59c93dcb046b', '9ffa28301a0a6a12ead2f2b9b6dad7ec0cb2748aa0b621c7759b35b765708815', '414bbbdf58f286c5e496d7c803710cd31aa258606333b503d787a3c556fa778f'),
(29, 2, 8, 5, 400.00, '2024-06-20 12:04:48', '4567', 'MCVb4ZvubpPZNE3LVKYLg6nznS48J972+xqXeSgabN8=', '5695c924a8199dacc64a319f422a6995e2882ba86be8df9a46fc1c31eddcf2dd', '517e2417530b9baf4a6acc0e2148912773ac52d44ec0623c255c04af0bca8a40', 'c8fbc784db990a494fe1be22f0090a6cb38cdec89ca276353589f2e3e3abc579'),
(30, 2, 8, 1, 80.00, '2024-06-20 17:25:09', '4321', 've3QwG+pEapCUewK33kUCvetcZZKwCHsZnVjnCTlP/M=', '9d3384898f67ee88639428eb086afed19c6a8587bd958328820ad9cd32501f9c', '535ca632f6b9ec4f143a67c7a5d3dc506fe71164c8df8bf71312a4273583a868', '55356581b086d0b83c79a70a386591c34de920b47516763ed4bb323b185d412f'),
(31, 25, 1, 1, 15.00, '2024-06-20 21:21:08', '7678', 'tPM/DIHNyo+LcJgIXtKz/Ghe8eGw+J+NbL2JNSU6ejc=', '7d1e466d46296d11be1a5e7be5c8c556015ac4e6a05567a3e8d9bd07280385c0', 'e3f6451fa55b4c5147630cbc0fde357e3e55f05b87f3c621829d8d34ca0d9a52', '56000642761e5c778a3dbe1fe7a660658b08b0fe64fcb6cea7ee8c26e9987074');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_picture` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `available_tickets` int(11) NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL,
  `pending_approval` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `organizer_id`, `category_id`, `event_name`, `event_description`, `event_picture`, `event_date`, `available_tickets`, `ticket_price`, `pending_approval`, `created_at`) VALUES
(1, 1, 5, 'EDC Annual Gathering', 'The Entrepreneurship Development Center 5th yearly gathering', 'uploads/WhatsApp-Image-2023-10-06-at-17.36.20_69280cfd-1024x768.jpg', '2024-06-29 15:00:00', 20, 15.00, 0, '2024-06-20 07:02:19'),
(3, 1, 1, 'Cultural night', 'Come and learn about all of the different cultures from MMU students', 'uploads\\images.jpeg', '2024-10-13 10:00:00', 200, 25.00, 0, '2024-06-20 16:00:00'),
(8, 1, 1, 'Career Connect', 'Grow your career with career connect', 'uploads\\Career-Fair-Poster-Social-Media-Feed-1024x1024.png', '2024-07-05 14:00:00', 0, 80.00, 0, '2024-06-20 12:01:36');

-- --------------------------------------------------------

--
-- Table structure for table `event_categories`
--

CREATE TABLE `event_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_categories`
--

INSERT INTO `event_categories` (`category_id`, `category_name`) VALUES
(1, 'Academic and Professional Development'),
(4, 'Arts and Entertainment'),
(5, 'Clubs and Organizations'),
(6, 'Community Service and Volunteering'),
(7, 'Health and Wellness'),
(8, 'Networking and Alumni Events'),
(10, 'Political and Social Awareness'),
(2, 'Social and Cultural Events'),
(3, 'Sports and Recreational Activities'),
(9, 'Technology and Innovation');

-- --------------------------------------------------------

--
-- Table structure for table `event_reviews`
--

CREATE TABLE `event_reviews` (
  `review_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_reviews`
--

INSERT INTO `event_reviews` (`review_id`, `event_id`, `user_id`, `review_text`, `rating`, `created_at`) VALUES
(15, 1, 2, 'It was really great and informative', 5, '2024-06-20 10:00:33'),
(16, 1, 2, 'it was great', 5, '2024-06-20 11:47:56'),
(17, 8, 2, 'IT was really benificial', 4, '2024-06-20 12:05:27'),
(18, 1, 25, 'It was very helpful, and informative', 5, '2024-06-20 21:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `faq_id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`faq_id`, `question_id`, `answer_text`, `answered_at`) VALUES
(5, 5, 'It is an event Management System', '2024-06-20 08:01:42'),
(8, 6, 'Go to the registration page', '2024-06-20 20:39:00'),
(9, 7, 'Go to www.goevent.com/announcements', '2024-06-20 21:30:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notification_text` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notification_text`, `is_read`, `created_at`, `link`) VALUES
(33, 2, 'Your question has been answered. Check the FAQ section.', 1, '2024-06-20 20:38:14', 'faq.php'),
(34, 2, 'Your question has been answered. Check the FAQ section.', 1, '2024-06-20 20:39:00', 'faq.php'),
(36, 25, 'Your question has been answered. Check the FAQ section.', 0, '2024-06-20 21:30:28', 'faq.php'),
(37, 1, 'Your event \'Cultural night\' has been approved.', 1, '2024-06-20 21:34:16', 'manage_events.php'),
(38, 2, 'A new event \'Cultural night\' is now available.', 0, '2024-06-20 21:34:16', 'event.php?id=3'),
(39, 25, 'A new event \'Cultural night\' is now available.', 0, '2024-06-20 21:34:16', 'event.php?id=3');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('regular','organizer','admin') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `user_type`, `profile_picture`, `created_at`) VALUES
(1, 'Marwah', '$2y$10$OrJJWLbSrgtWELvPC1Yu4u/QTtxt9joteCl/rLI3SjcRmHPfJD5Su', 'Marwah@gmail.com', 'organizer', NULL, '2024-06-13 19:47:45'),
(2, 'Thabit252', '$2y$10$rBSqMBtp0hnM1r8rJwOEIunsupraX/0RHUUNhd0Ar4zDkUNMBcRam', 'thabit252@gmail.com', 'regular', 'profile_pictures/20240127_163649.jpg', '2024-06-13 19:00:00'),
(3, 'Admin', '$2y$10$OrJJWLbSrgtWELvPC1Yu4u/QTtxt9joteCl/rLI3SjcRmHPfJD5Su', 'admin@gmail.com', 'admin', NULL, '2024-06-13 19:47:45'),
(25, 'Ibrahim', '$2y$10$U6NgxMSbBz2D1TXxH1cRqueIRa9/Egg6umn9GaigW5HJyzqoI2pEq', 'thabit@gmail.com', 'regular', 'profile_pictures/20240127_163649.jpg', '2024-06-20 21:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_questions`
--

CREATE TABLE `user_questions` (
  `question_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_questions`
--

INSERT INTO `user_questions` (`question_id`, `user_id`, `question_text`, `submitted_at`) VALUES
(5, 2, 'What is GoEvent?', '2024-06-20 08:01:14'),
(6, 2, 'How to sign up to GoEvent?', '2024-06-20 11:48:19'),
(7, 25, 'How to see the announcements?', '2024-06-20 21:23:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `booked_events`
--
ALTER TABLE `booked_events`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `event_categories`
--
ALTER TABLE `event_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `event_reviews`
--
ALTER TABLE `event_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`faq_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_questions`
--
ALTER TABLE `user_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `booked_events`
--
ALTER TABLE `booked_events`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `event_categories`
--
ALTER TABLE `event_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_reviews`
--
ALTER TABLE `event_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_questions`
--
ALTER TABLE `user_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `booked_events`
--
ALTER TABLE `booked_events`
  ADD CONSTRAINT `booked_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booked_events_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reviews`
--
ALTER TABLE `event_reviews`
  ADD CONSTRAINT `event_reviews_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `faq`
--
ALTER TABLE `faq`
  ADD CONSTRAINT `faq_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `user_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_questions`
--
ALTER TABLE `user_questions`
  ADD CONSTRAINT `user_questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
