-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql106.epizy.com
-- Generation Time: May 29, 2025 at 05:04 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epiz_26586500_roshan`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(245) NOT NULL,
  `user_contact` varchar(245) NOT NULL,
  `user_rate` int(3) NOT NULL,
  `user_lreading` varchar(245) NOT NULL,
  `user_lphoto` varchar(60) NOT NULL,
  `remainder` varchar(10) NOT NULL,
  `user_ledit` varchar(245) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_contact`, `user_rate`, `user_lreading`, `user_lphoto`, `remainder`, `user_ledit`) VALUES
(1, 'Shater1', 'sl486975@gmail.com', 12, '966', 'meterphoto/134468456.jpg', '2', '2025-05-29 09:38:56'),
(2, 'Shater2', 'kabitachinnal1@gmail.com', 12, '223', 'meterphoto/194582946.jpg', '4', '2025-05-29 18:41:57'),
(3, 'Shater3', 'lekumarnepali@gmail.com', 12, '221', 'meterphoto/1359704218.jpg', '2', '2025-03-08 17:28:22'),
(7, 'test', 'roshansutihar@gmail.com', 12, '263', 'meterphoto/766692842.jpeg', '1', '2025-05-29 20:32:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
