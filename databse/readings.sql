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
-- Table structure for table `readings`
--

CREATE TABLE `readings` (
  `read_id` int(11) NOT NULL,
  `read_user` varchar(245) NOT NULL,
  `read_value` varchar(245) NOT NULL,
  `read_cons` varchar(245) NOT NULL,
  `read_month` varchar(245) NOT NULL,
  `read_amount` varchar(245) NOT NULL,
  `read_file` varchar(245) NOT NULL,
  `read_status` varchar(245) NOT NULL,
  `read_date` varchar(245) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `readings`
--

INSERT INTO `readings` (`read_id`, `read_user`, `read_value`, `read_cons`, `read_month`, `read_amount`, `read_file`, `read_status`, `read_date`) VALUES
(1, 'Shater2 ', '187', '4', 'Chaitra 2080 ', '50', 'meterphoto/1058928084.jpg', 'Paid', '2024-05-02 19:02:49'),
(2, 'Shater3', '4117', '33', 'Baisakh 2081', '400', 'meterphoto/1420097450.jpg', 'Paid', '2024-06-09 09:51:52'),
(3, 'Shater1 ', '760', '38', 'Jestha 2081', '460', 'meterphoto/1778270153.jpg', 'Paid', '2024-06-25 17:49:36'),
(7, 'Shater3', '4192', '75', 'Jestha 2081', '900', 'meterphoto/959796750.jpg', 'Paid', '2024-07-11 07:38:26'),
(8, 'Shater2', '197', '10', 'Ashad 2081', '120', 'meterphoto/1207387940.jpg', 'Paid', '2024-07-24 18:30:16'),
(9, 'Shater3', '54', '54', 'Ashad 2081', '645', 'meterphoto/446664005.jpg', 'Paid', '2024-08-11 15:45:00'),
(10, 'Shater1', '800', '40', 'Shrawan 2081', '480', 'meterphoto/1762239621.jpg', 'Paid', '2024-08-27 11:50:50'),
(13, 'Shater3', '92', '38', 'Shrawan 2081', '455', 'meterphoto/755360778.jpg', 'Paid', '2024-09-10 08:37:55'),
(14, 'Shater2', '206', '9', 'Bhadra 2081', '110', 'meterphoto/297967020.jpg', 'Paid', '2024-09-23 18:11:43'),
(15, 'Shater1', '833', '33', 'Ashwin 2081', '395', 'meterphoto/135974304.jpg', 'Paid', '2024-10-24 10:59:44'),
(16, 'Shater2', '215', '9', 'Ashwin 2081', '105', 'meterphoto/767955277.jpg', 'Paid', '2024-10-28 18:58:32'),
(17, 'Shater3', '154', '62', 'Ashwin 2081', '745', 'meterphoto/353457151.jpg', 'Paid', '2024-11-09 08:23:06'),
(18, 'Shater1', '848', '15', 'Kartik 2081', '180', 'meterphoto/312125342.jpg', 'Paid', '2024-11-25 15:52:40'),
(19, 'Shater3', '173', '19', 'Kartik 2081', '230', 'meterphoto/1343692718.jpg', 'Paid', '2024-12-08 18:07:53'),
(20, 'Shater2', '221', '6', 'Mangsir 2081', '75', 'meterphoto/1212214260.jpg', 'Paid', '2025-01-05 15:52:48'),
(21, 'Shater3', '186', '13', 'Mangsir 2081', '155', 'meterphoto/1105163418.jpg', 'Paid', '2025-01-07 19:34:02'),
(22, 'Shater1', '888', '40', 'Poush 2081', '480', 'meterphoto/2048675869.jpg', 'Paid', '2025-01-24 13:07:53'),
(23, 'Shater3', '203', '17', 'Magh 2081', '205', 'meterphoto/1616947049.jpg', 'Paid', '2025-02-06 10:42:48'),
(24, 'Shater1', '911', '23', 'Magh 2081', '275', 'meterphoto/1284971920.jpg', 'Paid', '2025-02-25 08:29:50'),
(25, 'Shater3', '221', '18', 'Falgun 2081', '215', 'meterphoto/1359704218.jpg', 'Paid', '2025-03-08 17:28:22'),
(26, 'Shater1', '928', '17', 'Falgun 2081', '205', 'meterphoto/1716315751.jpg', 'Paid', '2025-03-23 09:53:46'),
(36, 'Shater1', '966', '38', 'Jestha 2082', '455', 'meterphoto/134468456.jpg', 'Unpaid', '2025-05-29 09:38:56'),
(37, 'Shater2', '223', '2', 'Jestha 2082', '20', 'meterphoto/194582946.jpg', 'Unpaid', '2025-05-29 18:41:57'),
(39, 'test', '263', '3', 'Shrawan 2082', '35', 'meterphoto/766692842.jpeg', 'Unpaid', '2025-05-29 20:32:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `readings`
--
ALTER TABLE `readings`
  ADD PRIMARY KEY (`read_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `readings`
--
ALTER TABLE `readings`
  MODIFY `read_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
