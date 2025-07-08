-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 08, 2025 at 02:40 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `confusion_matrix`
--

-- --------------------------------------------------------

--
-- Table structure for table `confusion_matrix`
--

CREATE TABLE `confusion_matrix` (
  `id` int NOT NULL,
  `dataset_id` int DEFAULT NULL,
  `class_name` varchar(255) NOT NULL,
  `true_positive` int DEFAULT '0',
  `false_positive` int DEFAULT '0',
  `true_negative` int DEFAULT '0',
  `false_negative` int DEFAULT '0',
  `precision_val` float DEFAULT NULL,
  `recall_val` float DEFAULT NULL,
  `f1_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `confusion_matrix`
--

INSERT INTO `confusion_matrix` (`id`, `dataset_id`, `class_name`, `true_positive`, `false_positive`, `true_negative`, `false_negative`, `precision_val`, `recall_val`, `f1_score`) VALUES
(10, 7, 'Layak', 1, 48, 5, 0, 0.0204082, 1, 0.04),
(11, 7, 'Tidak Layak', 5, 0, 1, 48, 1, 0.0943396, 0.172414),
(18, 11, 'Layak', 1, 34, 26, 2, 0.0285714, 0.333333, 0.0526316),
(19, 11, 'Tidak Layak', 26, 2, 1, 34, 0.928571, 0.433333, 0.590909),
(20, 12, 'Layak', 6, 13, 48, 1, 0.315789, 0.857143, 0.461538),
(21, 12, 'Tidak Layak', 48, 1, 6, 13, 0.979592, 0.786885, 0.872727),
(22, 13, 'Layak', 5, 22, 39, 3, 0.185185, 0.625, 0.285714),
(23, 13, 'Tidak Layak', 39, 3, 5, 22, 0.928571, 0.639344, 0.757282);

-- --------------------------------------------------------

--
-- Table structure for table `dataset`
--

CREATE TABLE `dataset` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `accuracy` float DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dataset`
--

INSERT INTO `dataset` (`id`, `name`, `accuracy`, `created_at`) VALUES
(7, 'rt1', 11.1111, '2025-07-07 23:00:39'),
(11, 'rt2', 42.8571, '2025-07-08 08:53:30'),
(12, 'rt3', 79.4118, '2025-07-08 09:30:46'),
(13, 'rt4', 63.7681, '2025-07-08 09:38:29');

-- --------------------------------------------------------

--
-- Table structure for table `raw_data`
--

CREATE TABLE `raw_data` (
  `id` int NOT NULL,
  `dataset_id` int DEFAULT NULL,
  `data_id` varchar(50) DEFAULT NULL,
  `nama_alternatif` varchar(255) DEFAULT NULL,
  `nilai_vektor_v` float DEFAULT NULL,
  `kelayakan_sistem` varchar(100) DEFAULT NULL,
  `kelayakan_aktual` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `raw_data`
--

INSERT INTO `raw_data` (`id`, `dataset_id`, `data_id`, `nama_alternatif`, `nilai_vektor_v`, `kelayakan_sistem`, `kelayakan_aktual`) VALUES
(112, 7, '1', 'Absa', 0.0306, 'Layak', 'Tidak Layak'),
(113, 7, '2', 'Mohammad Hasan B', 0.0239, 'Layak', 'Tidak Layak'),
(114, 7, '3', 'Biya', 0.0224, 'Layak', 'Layak'),
(115, 7, '4', 'Salma', 0.0219, 'Layak', 'Tidak Layak'),
(116, 7, '5', 'Robiatul Ahmad Muzakir', 0.0216, 'Layak', 'Tidak Layak'),
(117, 7, '6', 'Eko Wahyudi Heru Sutopo', 0.0206, 'Layak', 'Tidak Layak'),
(118, 7, '7', 'Andi Suhartono', 0.0206, 'Layak', 'Tidak Layak'),
(119, 7, '8', 'Syaiful Hakam', 0.0206, 'Layak', 'Tidak Layak'),
(120, 7, '9', 'Iskandar', 0.0206, 'Layak', 'Tidak Layak'),
(121, 7, '10', 'Ahsanul Roziqi', 0.0206, 'Layak', 'Tidak Layak'),
(122, 7, '11', 'Jufriadi', 0.0206, 'Layak', 'Tidak Layak'),
(123, 7, '12', 'Suryadi', 0.0206, 'Layak', 'Tidak Layak'),
(124, 7, '13', 'Moh. Khatib', 0.0206, 'Layak', 'Tidak Layak'),
(125, 7, '14', 'Herman', 0.0206, 'Layak', 'Tidak Layak'),
(126, 7, '15', 'Syamsuri', 0.0206, 'Layak', 'Tidak Layak'),
(127, 7, '16', 'Moh. Jufriadi', 0.0206, 'Layak', 'Tidak Layak'),
(128, 7, '17', 'Ahmad Nurul Fuad', 0.0189, 'Layak', 'Tidak Layak'),
(129, 7, '18', 'Zaini', 0.0189, 'Layak', 'Tidak Layak'),
(130, 7, '19', 'Rajae', 0.0189, 'Layak', 'Tidak Layak'),
(131, 7, '20', 'Samiyaturrahmah', 0.0189, 'Layak', 'Tidak Layak'),
(132, 7, '21', 'Tayyib', 0.0189, 'Layak', 'Tidak Layak'),
(133, 7, '22', 'Subyan', 0.0189, 'Layak', 'Tidak Layak'),
(134, 7, '23', 'Ahyar', 0.0189, 'Layak', 'Tidak Layak'),
(135, 7, '24', 'Asmat', 0.0189, 'Layak', 'Tidak Layak'),
(136, 7, '25', 'Husen Rahman', 0.0189, 'Layak', 'Tidak Layak'),
(137, 7, '26', 'Moh. Imam', 0.0189, 'Layak', 'Tidak Layak'),
(138, 7, '27', 'Mistar', 0.0189, 'Layak', 'Tidak Layak'),
(139, 7, '28', 'Moh. Shodik', 0.0189, 'Layak', 'Tidak Layak'),
(140, 7, '29', 'Senna', 0.0189, 'Layak', 'Tidak Layak'),
(141, 7, '30', 'Hesam', 0.0189, 'Layak', 'Tidak Layak'),
(142, 7, '31', 'Thala\'al Badri', 0.0189, 'Layak', 'Tidak Layak'),
(143, 7, '32', 'Haryanto', 0.0187, 'Layak', 'Tidak Layak'),
(144, 7, '33', 'Ach. Roziqi', 0.0187, 'Layak', 'Tidak Layak'),
(145, 7, '34', 'Fidiyanto', 0.0184, 'Layak', 'Tidak Layak'),
(146, 7, '35', 'Abd. Latif', 0.0171, 'Layak', 'Tidak Layak'),
(147, 7, '36', 'Dasuki', 0.0171, 'Layak', 'Tidak Layak'),
(148, 7, '37', 'Moh. Fadli', 0.0171, 'Layak', 'Tidak Layak'),
(149, 7, '38', 'Sujalmah', 0.0168, 'Layak', 'Tidak Layak'),
(150, 7, '39', 'Muhrap', 0.0168, 'Layak', 'Tidak Layak'),
(151, 7, '40', 'Edi Gusmito', 0.0168, 'Layak', 'Tidak Layak'),
(152, 7, '41', 'Ach. Sauki', 0.0168, 'Layak', 'Tidak Layak'),
(153, 7, '42', 'Miskadi', 0.0168, 'Layak', 'Tidak Layak'),
(154, 7, '43', 'Samsuddin', 0.0166, 'Layak', 'Tidak Layak'),
(155, 7, '44', 'Aswi', 0.0164, 'Layak', 'Tidak Layak'),
(156, 7, '45', 'Holis Ready', 0.0164, 'Layak', 'Tidak Layak'),
(157, 7, '46', 'Moh. Zaini', 0.0154, 'Layak', 'Tidak Layak'),
(158, 7, '47', 'Hendri Hariyanto', 0.0154, 'Layak', 'Tidak Layak'),
(159, 7, '48', 'Abdul Hannan', 0.0153, 'Layak', 'Tidak Layak'),
(160, 7, '49', 'Buchari Raidy', 0.0151, 'Layak', 'Tidak Layak'),
(161, 7, '50', 'H. Miftahol Arifin. S.Pd', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(162, 7, '51', 'Sa\'ada', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(163, 7, '52', 'Hartatik', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(164, 7, '53', 'Radina', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(165, 7, '54', 'Zaini', 0.012, 'Tidak Layak', 'Tidak Layak'),
(177, 11, '1', 'Ida Mariyana', 0.026, 'Layak', 'Tidak Layak'),
(178, 11, '2', 'Essa', 0.026, 'Layak', 'Tidak Layak'),
(179, 11, '3', 'Hartatik', 0.0231, 'Layak', 'Tidak Layak'),
(180, 11, '4', 'Juhairiyah', 0.0231, 'Layak', 'Tidak Layak'),
(181, 11, '5', 'Satuna', 0.019, 'Layak', 'Tidak Layak'),
(182, 11, '6', 'Mutmainnah', 0.019, 'Layak', 'Tidak Layak'),
(183, 11, '7', 'B. Juma\'ati', 0.019, 'Layak', 'Tidak Layak'),
(184, 11, '8', 'Sa\'rani', 0.019, 'Layak', 'Tidak Layak'),
(185, 11, '9', 'Erfa', 0.019, 'Layak', 'Tidak Layak'),
(186, 11, '10', 'Syaiful Anwar', 0.0175, 'Layak', 'Tidak Layak'),
(187, 11, '11', 'Nuril Fahrisi', 0.0175, 'Layak', 'Tidak Layak'),
(188, 11, '12', 'Farham Riza Umami', 0.0175, 'Layak', 'Tidak Layak'),
(189, 11, '13', 'Ach. Syamhadi', 0.0175, 'Layak', 'Tidak Layak'),
(190, 11, '14', 'Mansyur Edy Chandra', 0.0175, 'Layak', 'Tidak Layak'),
(191, 11, '15', 'Suaidi', 0.0175, 'Layak', 'Tidak Layak'),
(192, 11, '16', 'Joni Iskandar', 0.0175, 'Layak', 'Layak'),
(193, 11, '17', 'Suhri', 0.0175, 'Layak', 'Tidak Layak'),
(194, 11, '18', 'Moh. Ramdan', 0.0175, 'Layak', 'Tidak Layak'),
(195, 11, '19', 'Sulastri', 0.0175, 'Layak', 'Tidak Layak'),
(196, 11, '20', 'Muhammad Ali Mansur', 0.0175, 'Layak', 'Tidak Layak'),
(197, 11, '21', 'Syaiful Hasan', 0.016, 'Layak', 'Tidak Layak'),
(198, 11, '22', 'Ach. Jaelani', 0.016, 'Layak', 'Tidak Layak'),
(199, 11, '23', 'Diredjo', 0.016, 'Layak', 'Tidak Layak'),
(200, 11, '24', 'Moh. Mahmudi', 0.016, 'Layak', 'Tidak Layak'),
(201, 11, '25', 'Jailani', 0.016, 'Layak', 'Tidak Layak'),
(202, 11, '26', 'Muhammad', 0.016, 'Layak', 'Tidak Layak'),
(203, 11, '27', 'Satima', 0.016, 'Layak', 'Tidak Layak'),
(204, 11, '28', 'Asri', 0.016, 'Layak', 'Tidak Layak'),
(205, 11, '29', 'Busahri', 0.016, 'Layak', 'Tidak Layak'),
(206, 11, '30', 'Amir', 0.016, 'Layak', 'Tidak Layak'),
(207, 11, '31', 'Muhammad Imam Halili', 0.016, 'Layak', 'Tidak Layak'),
(208, 11, '32', 'Ach. Hairi', 0.016, 'Layak', 'Tidak Layak'),
(209, 11, '33', 'Hatib', 0.0156, 'Layak', 'Tidak Layak'),
(210, 11, '34', 'Asmu', 0.0156, 'Layak', 'Tidak Layak'),
(211, 11, '35', 'Ach. Fauzan', 0.0151, 'Layak', 'Tidak Layak'),
(212, 11, '36', 'Siti Munati Rudin', 0.015, 'Tidak Layak', 'Tidak Layak'),
(213, 11, '37', 'Samiani', 0.015, 'Tidak Layak', 'Tidak Layak'),
(214, 11, '38', 'Rahmani', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(215, 11, '39', 'Moh. Yahya', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(216, 11, '40', 'Samsuri', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(217, 11, '41', 'Hasiyah', 0.0143, 'Tidak Layak', 'Layak'),
(218, 11, '42', 'Iskandar', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(219, 11, '43', 'Emmat', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(220, 11, '44', 'Sa\'iman', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(221, 11, '45', 'Iksan', 0.0143, 'Tidak Layak', 'Tidak Layak'),
(222, 11, '46', 'Miftahol Arifin', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(223, 11, '47', 'Kafrawi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(224, 11, '48', 'Sugianto', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(225, 11, '49', 'Morkawi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(226, 11, '50', 'Ach. Warits', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(227, 11, '51', 'Maulidur Rofiqi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(228, 11, '52', 'Jumardi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(229, 11, '53', 'Sowadi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(230, 11, '54', 'Moh. Rusdi', 0.0139, 'Tidak Layak', 'Tidak Layak'),
(231, 11, '55', 'Mohammad Rasidi', 0.013, 'Tidak Layak', 'Tidak Layak'),
(232, 11, '56', 'Abu Ya\'kup', 0.0123, 'Tidak Layak', 'Tidak Layak'),
(233, 11, '57', 'Mat Alwi', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(234, 11, '58', 'Moh Fandi', 0.0117, 'Tidak Layak', 'Layak'),
(235, 11, '59', 'Amina', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(236, 11, '60', 'Sittina', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(237, 11, '61', 'Kastina', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(238, 11, '62', 'Ahmat Su Aidi', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(239, 11, '63', 'Jamiya', 0.0117, 'Tidak Layak', 'Tidak Layak'),
(240, 12, '1', 'Supriati', 0.024, 'Layak', 'Tidak Layak'),
(241, 12, '2', 'Melli Hidayati', 0.0214, 'Layak', 'Tidak Layak'),
(242, 12, '3', 'Sundari', 0.0194, 'Layak', 'Layak'),
(243, 12, '4', 'Munasiha', 0.0176, 'Layak', 'Layak'),
(244, 12, '5', 'Alma', 0.0176, 'Layak', 'Tidak Layak'),
(245, 12, '6', 'Rabiatun', 0.0176, 'Layak', 'Layak'),
(246, 12, '7', 'Rukmini', 0.0176, 'Layak', 'Tidak Layak'),
(247, 12, '8', 'Erni', 0.0176, 'Layak', 'Tidak Layak'),
(248, 12, '9', 'Sukar', 0.0176, 'Layak', 'Tidak Layak'),
(249, 12, '10', 'Hermanto', 0.0162, 'Layak', 'Tidak Layak'),
(250, 12, '11', 'Moh. Hafid', 0.0162, 'Layak', 'Tidak Layak'),
(251, 12, '12', 'Ach. Fauzan', 0.0162, 'Layak', 'Tidak Layak'),
(252, 12, '13', 'Mohlish', 0.0162, 'Layak', 'Tidak Layak'),
(253, 12, '14', 'Rachmad Juhari', 0.0162, 'Layak', 'Tidak Layak'),
(254, 12, '15', 'Yohan Lesmana', 0.0162, 'Layak', 'Tidak Layak'),
(255, 12, '16', 'Muhammad Syawal Habibie', 0.0162, 'Layak', 'Layak'),
(256, 12, '17', 'Edi Darsono', 0.0162, 'Layak', 'Layak'),
(257, 12, '18', 'Ruwaida', 0.0159, 'Layak', 'Tidak Layak'),
(258, 12, '19', 'Juwa', 0.0159, 'Layak', 'Layak'),
(259, 12, '20', 'Sakrawi', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(260, 12, '21', 'Hosni Tamrin', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(261, 12, '22', 'Marsa\'ie', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(262, 12, '23', 'Sya\'ron Riadi', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(263, 12, '24', 'Rustam', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(264, 12, '25', 'Hidayat', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(265, 12, '26', 'Sutrisno', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(266, 12, '27', 'Surtiatun', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(267, 12, '28', 'Roni Hidayat', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(268, 12, '29', 'Moh. Essun', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(269, 12, '30', 'Munip', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(270, 12, '31', 'Budi Hartono', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(271, 12, '32', 'Samhari', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(272, 12, '33', 'A. Rahem', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(273, 12, '34', 'Syafiudin', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(274, 12, '35', 'Ach. Riyadi', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(275, 12, '36', 'Fathor Rahman', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(276, 12, '37', 'Moh. Rofiqi', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(277, 12, '38', 'Samsudin', 0.0148, 'Tidak Layak', 'Tidak Layak'),
(278, 12, '39', 'Moh. Saifus Sinal', 0.0145, 'Tidak Layak', 'Tidak Layak'),
(279, 12, '40', 'Muhammad Suki', 0.0145, 'Tidak Layak', 'Tidak Layak'),
(280, 12, '41', 'Moh. Fajrul Falah', 0.014, 'Tidak Layak', 'Tidak Layak'),
(281, 12, '42', 'Saiful Huda M', 0.014, 'Tidak Layak', 'Tidak Layak'),
(282, 12, '43', 'Puramin', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(283, 12, '44', 'Sarbini', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(284, 12, '45', 'Misto', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(285, 12, '46', 'Abd. Hayyi', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(286, 12, '47', 'Juti', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(287, 12, '48', 'Juma\'a', 0.0132, 'Tidak Layak', 'Layak'),
(288, 12, '49', 'Asmar', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(289, 12, '50', 'Hermanto', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(290, 12, '51', 'Hasim', 0.0132, 'Tidak Layak', 'Tidak Layak'),
(291, 12, '52', 'Zainuddin', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(292, 12, '53', 'Farid Helmi', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(293, 12, '54', 'Yanto', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(294, 12, '55', 'Fadhlan Fathani', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(295, 12, '56', 'Wahdi', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(296, 12, '57', 'Moh. Noor Fajri Efendi', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(297, 12, '58', 'Sarbini', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(298, 12, '59', 'Ahmat Wafi Riyanto', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(299, 12, '60', 'H. Tayyib', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(300, 12, '61', 'Supriadi', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(301, 12, '62', 'Yudi Dwi Kurniawan', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(302, 12, '63', 'Yasit Rido\'i', 0.0128, 'Tidak Layak', 'Tidak Layak'),
(303, 12, '64', 'Lilik Halida', 0.0125, 'Tidak Layak', 'Tidak Layak'),
(304, 12, '65', 'Asep Irawan', 0.0121, 'Tidak Layak', 'Tidak Layak'),
(305, 12, '66', 'Norholis', 0.0121, 'Tidak Layak', 'Tidak Layak'),
(306, 12, '67', 'Nur Chalis Habibullah', 0.0121, 'Tidak Layak', 'Tidak Layak'),
(307, 12, '68', 'Yatemi', 0.0088, 'Tidak Layak', 'Tidak Layak'),
(308, 13, '1', 'Sutali', 0.0251, 'Layak', 'Tidak Layak'),
(309, 13, '2', 'Rafi\'i', 0.0251, 'Layak', 'Layak'),
(310, 13, '3', 'Atmina', 0.0183, 'Layak', 'Tidak Layak'),
(311, 13, '4', 'Sunahwa', 0.0183, 'Layak', 'Layak'),
(312, 13, '5', 'Maimuna', 0.0183, 'Layak', 'Tidak Layak'),
(313, 13, '6', 'Muhawa', 0.0183, 'Layak', 'Tidak Layak'),
(314, 13, '7', 'Zainuddin', 0.0183, 'Layak', 'Tidak Layak'),
(315, 13, '8', 'H. Misnawi', 0.017, 'Layak', 'Tidak Layak'),
(316, 13, '9', 'Ruddi', 0.017, 'Layak', 'Tidak Layak'),
(317, 13, '10', 'Saheme', 0.0169, 'Layak', 'Tidak Layak'),
(318, 13, '11', 'M. Rasul', 0.0155, 'Layak', 'Tidak Layak'),
(319, 13, '12', 'Zainuddin', 0.0155, 'Layak', 'Tidak Layak'),
(320, 13, '13', 'Mawi', 0.0155, 'Layak', 'Tidak Layak'),
(321, 13, '14', 'Afsir', 0.0155, 'Layak', 'Tidak Layak'),
(322, 13, '15', 'Halik', 0.0155, 'Layak', 'Layak'),
(323, 13, '16', 'Fathorrahman', 0.0155, 'Layak', 'Tidak Layak'),
(324, 13, '17', 'Tajab', 0.0155, 'Layak', 'Layak'),
(325, 13, '18', 'Abu Thalib', 0.0155, 'Layak', 'Layak'),
(326, 13, '19', 'Farid Harja', 0.0155, 'Layak', 'Tidak Layak'),
(327, 13, '20', 'Suwama', 0.0155, 'Layak', 'Tidak Layak'),
(328, 13, '21', 'Sadili', 0.0155, 'Layak', 'Tidak Layak'),
(329, 13, '22', 'Rohani', 0.0155, 'Layak', 'Tidak Layak'),
(330, 13, '23', 'Syaful Bahri', 0.0155, 'Layak', 'Tidak Layak'),
(331, 13, '24', 'Sa\'it Efendi', 0.0155, 'Layak', 'Tidak Layak'),
(332, 13, '25', 'Emmang', 0.0155, 'Layak', 'Tidak Layak'),
(333, 13, '26', 'Suwandi', 0.015, 'Layak', 'Tidak Layak'),
(334, 13, '27', 'Minhajul Qowing', 0.015, 'Layak', 'Tidak Layak'),
(335, 13, '28', 'Halili', 0.0146, 'Tidak Layak', 'Tidak Layak'),
(336, 13, '29', 'Suyanto', 0.0146, 'Tidak Layak', 'Tidak Layak'),
(337, 13, '30', 'Mohammad Saleh Rusdi', 0.0146, 'Tidak Layak', 'Tidak Layak'),
(338, 13, '31', 'Tayyib', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(339, 13, '32', 'Ahmad', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(340, 13, '33', 'Sinal', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(341, 13, '34', 'Buji', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(342, 13, '35', 'Amin', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(343, 13, '36', 'Sudarsono Abdillah', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(344, 13, '37', 'Ainurrahman', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(345, 13, '38', 'Mahwan', 0.0138, 'Tidak Layak', 'Tidak Layak'),
(346, 13, '39', 'As\'at', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(347, 13, '40', 'Susiyati', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(348, 13, '41', 'Suda\'i', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(349, 13, '42', 'Syaiful Mas\'ud', 0.0134, 'Tidak Layak', 'Layak'),
(350, 13, '43', 'Achmad Zainuri', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(351, 13, '44', 'Zainal Efendi', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(352, 13, '45', 'H. Ali Wafa', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(353, 13, '46', 'Parto', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(354, 13, '47', 'Edi Sugiarto Anwari', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(355, 13, '48', 'Ribuddin', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(356, 13, '49', 'Muhammad Hatim', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(357, 13, '50', 'Sahor', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(358, 13, '51', 'Ahyar', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(359, 13, '52', 'Saruji', 0.0134, 'Tidak Layak', 'Tidak Layak'),
(360, 13, '53', 'Ribut Sadar Mupakat', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(361, 13, '54', 'Ferriyanto', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(362, 13, '55', 'Zainal Abidin', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(363, 13, '56', 'Hambali', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(364, 13, '57', 'Siswandi', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(365, 13, '58', 'Yendra Rudi Hartono', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(366, 13, '59', 'Moh. Ramli', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(367, 13, '60', 'Erfan Effendi, Ns', 0.0126, 'Tidak Layak', 'Tidak Layak'),
(368, 13, '61', 'Muallam', 0.0119, 'Tidak Layak', 'Tidak Layak'),
(369, 13, '62', 'Manis', 0.0119, 'Tidak Layak', 'Layak'),
(370, 13, '63', 'Errud', 0.0119, 'Tidak Layak', 'Tidak Layak'),
(371, 13, '64', 'Jumraini', 0.0113, 'Tidak Layak', 'Tidak Layak'),
(372, 13, '65', 'Hani', 0.0113, 'Tidak Layak', 'Tidak Layak'),
(373, 13, '66', 'Sukiya', 0.0113, 'Tidak Layak', 'Tidak Layak'),
(374, 13, '67', 'Ibrahim', 0.0113, 'Tidak Layak', 'Tidak Layak'),
(375, 13, '68', 'Rohama', 0.0113, 'Tidak Layak', 'Tidak Layak'),
(376, 13, '69', 'Farida', 0.0098, 'Tidak Layak', 'Layak');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `confusion_matrix`
--
ALTER TABLE `confusion_matrix`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_confusion_matrix_dataset_id` (`dataset_id`);

--
-- Indexes for table `dataset`
--
ALTER TABLE `dataset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raw_data`
--
ALTER TABLE `raw_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dataset_id` (`dataset_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `confusion_matrix`
--
ALTER TABLE `confusion_matrix`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `dataset`
--
ALTER TABLE `dataset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `raw_data`
--
ALTER TABLE `raw_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=377;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `confusion_matrix`
--
ALTER TABLE `confusion_matrix`
  ADD CONSTRAINT `fk_confusion_matrix_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `dataset` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `raw_data`
--
ALTER TABLE `raw_data`
  ADD CONSTRAINT `raw_data_ibfk_1` FOREIGN KEY (`dataset_id`) REFERENCES `dataset` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
