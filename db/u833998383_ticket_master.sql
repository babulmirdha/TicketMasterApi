-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 09:18 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u833998383_ticket_master`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `artist_name` varchar(255) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `section` int(11) NOT NULL,
  `row` int(11) NOT NULL,
  `seat` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `ticket_type` varchar(255) NOT NULL,
  `level` varchar(255) NOT NULL,
  `total_tickets` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) NOT NULL,
  `create_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_events`
--

INSERT INTO `tbl_events` (`id`, `user_id`, `artist_name`, `event_name`, `section`, `row`, `seat`, `date`, `location`, `time`, `ticket_type`, `level`, `total_tickets`, `image`, `create_at`) VALUES
(2, 1, 'Coldplay', 'Eras Tour', 132, 7, 2, 'Sat, Dec 20', 'Hard Rock Stadium', '8pm', 'Verfied Fan offer', 'LOWER LEVEL', 3, '/uploads/events/img_24a3e8cce9.jpg', 1752179320);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tickets`
--

CREATE TABLE `tbl_tickets` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `seat` int(11) NOT NULL,
  `create_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_tickets`
--

INSERT INTO `tbl_tickets` (`id`, `event_id`, `seat`, `create_at`) VALUES
(1, 2, 2, 1752179320),
(2, 2, 3, 1752179320),
(3, 2, 4, 1752179320);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `name`, `email`, `password`, `status`) VALUES
(1, 'Arslan', 'arslan@gmail.com', '$2y$10$66QirmDRwZx4PW1QU6qmJOza8A5X2LfyWjTGB0HMWerrD6Ji7exue', '1'),
(2, 'Arslan Ali', 'arslan12@gmail.com', '$2y$10$lv0lPAHVah6FeWKUTKgKOOa4ASxv3nT28BdooN6JCDONzZpb/dNFi', '1'),
(3, 'pankaj', 'pankaj23@gmail.com', '$2y$10$aAT92XWbQ95h53HI8ir5luT7ELBX6A/VuYSyxXRUzHHYvS5y6WAda', '1'),
(4, 'pankaj', 'pankaj237@gmail.com', '$2y$10$KSusxdXUHIm./q8q.lMIbe1t9yCukpMrVNWzdJ3qDUrpQ9rAebK1.', '1'),
(5, 'Issac', 'hostme676@gmail.com', '$2y$10$wEX5Nma2oNPWRFEGXADWVOJ3K.kUVisS9XZE0RXo0zSfVo3zXk/zm', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_tickets`
--
ALTER TABLE `tbl_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_tickets`
--
ALTER TABLE `tbl_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
