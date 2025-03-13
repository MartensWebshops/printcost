-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 04:46 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `printcost`
--

-- --------------------------------------------------------

--
-- Table structure for table `costs`
--

CREATE TABLE `costs` (
  `id` int(11) NOT NULL,
  `cost_type` varchar(50) NOT NULL,
  `cost_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `costs`
--

INSERT INTO `costs` (`id`, `cost_type`, `cost_value`, `date_updated`) VALUES
(1, 'Arbeid', '20.00', '2025-03-13 11:41:10'),
(2, 'Voorbereiding', '0.00', '2025-03-13 15:07:46'),
(3, 'Nabehandeling', '0.00', '2025-03-13 11:41:34'),
(4, 'Energie', '0.00', '2025-03-13 11:42:52'),
(5, 'Elektriciteit', '0.27', '2025-03-13 15:41:44'),
(6, 'Printers', '0.00', '2025-03-13 11:43:03'),
(7, 'Onderhoud', '0.00', '2025-03-13 15:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `filaments`
--

CREATE TABLE `filaments` (
  `id` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `weight` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date_added` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `filaments`
--

INSERT INTO `filaments` (`id`, `brand`, `name`, `type`, `color`, `weight`, `price`, `date_added`) VALUES
(21, 'Prusa', 'PLA', 'TPU', 'Black', 1000, '0.03', '2025-01-15'),
(22, 'eSun', 'PETG', 'PLA', 'Red', 1000, '0.04', '2025-01-20'),
(23, 'Hatchbox', 'ABS', '', 'Blue', 1000, '0.04', '2025-02-01'),
(24, '3D Solutech', 'PLA', '', 'Green', 1000, '0.03', '2025-02-10'),
(25, 'Overture', 'TPU', '', 'Transparent', 500, '0.06', '2025-02-15'),
(26, 'Filamentum', 'Nylon', '', 'White', 750, '0.08', '2025-02-20'),
(27, 'ColorFabb', 'PLA', '', 'Yellow', 1000, '0.05', '2025-03-01'),
(28, 'Polymaker', 'PC', '', 'Clear', 1000, '0.05', '2025-03-05'),
(29, 'MatterHackers', 'PETG', 'PLA', 'Blue', 1000, '0.04', '2025-03-07'),
(30, 'AMZ3D', 'ASA', '', 'Grey', 1000, '0.04', '2025-03-07');

-- --------------------------------------------------------

--
-- Table structure for table `productprintcosts`
--

CREATE TABLE `productprintcosts` (
  `id` int(11) NOT NULL,
  `artikelnaam` varchar(255) NOT NULL,
  `aangemaakt` date DEFAULT curdate(),
  `gewicht` int(11) NOT NULL,
  `printprijs` decimal(10,2) NOT NULL,
  `verkoopprijs` decimal(10,2) NOT NULL,
  `printtijd` int(11) NOT NULL,
  `idnummer2` varchar(3) NOT NULL,
  `idnummer3` varchar(3) NOT NULL,
  `idnummer4` varchar(3) NOT NULL,
  `idnummer5` varchar(3) NOT NULL,
  `idnummer6` varchar(3) NOT NULL,
  `idnummer7` varchar(3) NOT NULL,
  `idnummer8` varchar(3) NOT NULL,
  `orderaantal` varchar(1) NOT NULL,
  `aantal_afwijkend` varchar(1) NOT NULL,
  `geconstateerde_afwijking` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `productprintcosts`
--

INSERT INTO `productprintcosts` (`id`, `artikelnaam`, `aangemaakt`, `gewicht`, `printprijs`, `verkoopprijs`, `printtijd`, `idnummer2`, `idnummer3`, `idnummer4`, `idnummer5`, `idnummer6`, `idnummer7`, `idnummer8`, `orderaantal`, `aantal_afwijkend`, `geconstateerde_afwijking`) VALUES
(2, 'Bas Martens', '2025-01-28', 0, '0.00', '4.00', 111, '333', '555', '777', '222', '444', '666', '888', '8', '2', 'gvcn cvbvb'),
(17, '', '2025-03-05', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(18, '', '2025-03-05', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(20, '', '2025-03-05', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(21, '', '2025-03-05', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(22, '', '2025-03-05', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(33, '', '2025-03-06', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(34, '', '2025-03-06', 0, '0.00', '0.00', 0, '', '', '', '', '', '', '', '', '', ''),
(35, 'Bas Martens', '2025-03-06', 0, '0.00', '4.00', 1, '005', '002', '006', '003', '007', '004', '008', '8', '0', 'Bla'),
(36, 'Tim van der Heijden', '2025-03-06', 0, '0.00', '5.00', 9, '013', '010', '014', '011', '015', '012', '016', '8', '0', 'Bla'),
(42, 'Test Artikel Naam', '2025-03-06', 100, '2.50', '7.25', 100, '', '', '', '', '', '', '', '0', '0', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `costs`
--
ALTER TABLE `costs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cost_type` (`cost_type`);

--
-- Indexes for table `filaments`
--
ALTER TABLE `filaments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productprintcosts`
--
ALTER TABLE `productprintcosts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `costs`
--
ALTER TABLE `costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `filaments`
--
ALTER TABLE `filaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `productprintcosts`
--
ALTER TABLE `productprintcosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
