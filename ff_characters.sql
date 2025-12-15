-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 20, 2025 at 03:32 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ff_characters`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_characters`
--

CREATE TABLE `t_characters` (
  `character_id` int NOT NULL,
  `character_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  `health` tinyint NOT NULL,
  `defense` tinyint NOT NULL,
  `strength` tinyint NOT NULL,
  `magic` tinyint NOT NULL,
  `speed` tinyint NOT NULL,
  `support` tinyint NOT NULL,
  `portrait_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `t_characters`
--

INSERT INTO `t_characters` (`character_id`, `character_name`, `game_name`, `role_id`, `health`, `defense`, `strength`, `magic`, `speed`, `support`, `portrait_image`) VALUES
(1, 'Warrior of Light (Tank)', 'FF1', 1, 7, 7, 6, 2, 4, 3, 'wol_tank.png'),
(2, 'Guy', 'FF2', 1, 8, 7, 7, 1, 3, 2, 'guy.png'),
(3, 'Cecil', 'FF4', 1, 8, 8, 7, 3, 3, 4, 'cecil.png'),
(4, 'Galuf', 'FF5', 1, 7, 7, 6, 3, 3, 4, 'galuf.png'),
(5, 'Umaro', 'FF6', 1, 9, 8, 8, 1, 2, 1, 'umaro.png'),
(6, 'Steiner', 'FF9', 1, 9, 9, 8, 1, 2, 2, 'steiner.png'),
(7, 'Auron', 'FF10', 1, 8, 9, 7, 2, 3, 3, 'auron.png'),
(8, 'Kimahri', 'FF10', 1, 7, 7, 6, 2, 4, 3, 'kimahri.png'),
(9, 'Basch', 'FF12', 1, 8, 8, 7, 2, 3, 3, 'basch.png'),
(10, 'Snow', 'FF13', 1, 8, 8, 7, 2, 4, 3, 'snow.png'),
(11, 'Fang', 'FF13', 1, 7, 9, 7, 2, 4, 3, 'fang.png'),
(12, 'Gladiolus', 'FF15', 1, 8, 8, 7, 1, 3, 3, 'gladiolus.png'),
(13, 'Clive', 'FF16', 1, 8, 7, 7, 3, 5, 3, 'clive.png'),
(14, 'Cyan', 'FF6', 1, 8, 7, 7, 2, 3, 2, 'cyan.png'),
(15, 'Warrior of Light (Melee)', 'FF1', 2, 7, 6, 7, 2, 5, 2, 'wol_melee.png'),
(16, 'Firion', 'FF2', 2, 6, 5, 6, 3, 5, 3, 'firion.png'),
(17, 'Leon', 'FF2', 2, 6, 6, 6, 2, 4, 2, 'leon.png'),
(18, 'Luneth', 'FF3', 2, 6, 5, 6, 3, 5, 3, 'luneth.png'),
(19, 'Ingus', 'FF3', 2, 6, 6, 6, 3, 4, 3, 'ingus.png'),
(20, 'Bartz', 'FF5', 2, 6, 5, 6, 3, 6, 3, 'bartz.png'),
(21, 'Faris', 'FF5', 2, 6, 5, 6, 3, 6, 3, 'faris.png'),
(22, 'Locke', 'FF6', 2, 5, 4, 6, 3, 7, 4, 'locke.png'),
(23, 'Sabin', 'FF6', 2, 7, 6, 8, 2, 4, 3, 'sabin.png'),
(24, 'Shadow', 'FF6', 2, 5, 4, 7, 3, 7, 4, 'shadow.png'),
(25, 'Cid (FF4)', 'FF4', 2, 7, 6, 7, 2, 4, 3, 'cid_ff4.png'),
(26, 'Cloud', 'FF7', 2, 6, 6, 8, 3, 6, 4, 'cloud.png'),
(27, 'Tifa', 'FF7', 2, 5, 4, 8, 2, 7, 3, 'tifa.png'),
(28, 'Red XIII', 'FF7', 2, 6, 5, 6, 3, 5, 3, 'red13.png'),
(29, 'Cid (FF7)', 'FF7', 2, 6, 6, 7, 2, 5, 3, 'cid_ff7.png'),
(30, 'Squall', 'FF8', 2, 6, 5, 8, 3, 6, 4, 'squall.png'),
(31, 'Zell', 'FF8', 2, 5, 4, 8, 2, 7, 3, 'zell.png'),
(32, 'Zidane', 'FF9', 2, 6, 5, 7, 3, 8, 4, 'zidane.png'),
(33, 'Freya', 'FF9', 2, 6, 6, 7, 3, 6, 3, 'freya.png'),
(34, 'Amarant', 'FF9', 2, 7, 5, 7, 2, 5, 3, 'amarant.png'),
(35, 'Tidus', 'FF10', 2, 6, 5, 7, 3, 9, 4, 'tidus.png'),
(36, 'Paine', 'FFX-2', 2, 6, 5, 7, 3, 6, 3, 'paine.png'),
(37, 'Vaan', 'FF12', 2, 5, 4, 6, 3, 7, 4, 'vaan.png'),
(38, 'Lightning', 'FF13', 2, 6, 5, 7, 3, 8, 4, 'lightning.png'),
(39, 'Noel', 'FF13-2', 2, 6, 5, 7, 3, 7, 4, 'noel.png'),
(40, 'Noctis', 'FF15', 2, 6, 5, 7, 4, 7, 4, 'noctis.png'),
(41, 'Maria', 'FF2', 3, 5, 4, 5, 4, 6, 3, 'maria.png'),
(42, 'Edgar', 'FF6', 3, 6, 5, 6, 4, 5, 4, 'edgar.png'),
(43, 'Setzer', 'FF6', 3, 6, 4, 5, 3, 5, 4, 'setzer.png'),
(44, 'Barret', 'FF7', 3, 7, 6, 6, 2, 4, 3, 'barret.png'),
(45, 'Yuffie', 'FF7', 3, 5, 4, 6, 3, 8, 3, 'yuffie.png'),
(46, 'Vincent', 'FF7', 3, 5, 4, 6, 4, 6, 3, 'vincent.png'),
(47, 'Irvine', 'FF8', 3, 5, 4, 6, 2, 6, 3, 'irvine.png'),
(48, 'Wakka', 'FF10', 3, 6, 5, 6, 3, 6, 4, 'wakka.png'),
(49, 'Balthier', 'FF12', 3, 6, 5, 6, 3, 6, 4, 'balthier.png'),
(50, 'Fran', 'FF12', 3, 5, 4, 6, 3, 7, 4, 'fran.png'),
(51, 'Prompto', 'FF15', 3, 5, 4, 6, 3, 7, 4, 'prompto.png'),
(52, 'Sazh', 'FF13', 3, 6, 5, 5, 3, 5, 6, 'sazh.png'),
(53, 'Warrior of Light (Black Mage)', 'FF1', 4, 4, 3, 2, 7, 4, 3, 'wol_blackmage.png'),
(54, 'Arc', 'FF3', 4, 4, 3, 2, 7, 4, 3, 'arc.png'),
(55, 'Rydia (Adult)', 'FF4', 4, 5, 4, 3, 8, 5, 4, 'rydia.png'),
(56, 'Palom', 'FF4', 4, 4, 3, 2, 8, 4, 3, 'palom.png'),
(57, 'Krile', 'FF5', 4, 4, 3, 2, 7, 4, 4, 'krile.png'),
(58, 'Terra', 'FF6', 4, 6, 4, 4, 8, 5, 4, 'terra.png'),
(59, 'Strago', 'FF6', 4, 4, 3, 2, 7, 3, 3, 'strago.png'),
(60, 'Relm', 'FF6', 4, 4, 3, 2, 7, 4, 3, 'relm.png'),
(61, 'Vivi', 'FF9', 4, 3, 2, 1, 9, 4, 4, 'vivi.png'),
(62, 'Lulu', 'FF10', 4, 4, 3, 2, 9, 4, 3, 'lulu.png'),
(63, 'Ashe', 'FF12', 4, 5, 4, 3, 7, 5, 4, 'ashe.png'),
(64, 'Rinoa', 'FF8', 4, 5, 4, 3, 7, 6, 4, 'rinoa.png'),
(65, 'Shantotto', 'FF11', 4, 4, 3, 2, 8, 5, 4, 'shantotto.png'),
(66, 'Hope (Caster Mode)', 'FF13', 4, 4, 3, 3, 8, 5, 6, 'hope_caster.png'),
(67, 'Serah (Magic Focus)', 'FF13-2', 4, 5, 4, 3, 7, 6, 6, 'serah_magic.png'),
(68, 'Warrior of Light (White Mage)', 'FF1', 5, 5, 4, 2, 6, 4, 6, 'wol_whitemage.png'),
(69, 'Refia', 'FF3', 5, 5, 4, 2, 6, 4, 6, 'refia.png'),
(70, 'Rosa', 'FF4', 5, 4, 3, 2, 7, 4, 7, 'rosa.png'),
(71, 'Porom', 'FF4', 5, 4, 3, 2, 7, 4, 6, 'porom.png'),
(72, 'Fusoya', 'FF4', 5, 4, 3, 2, 7, 4, 7, 'fusoya.png'),
(73, 'Lenna', 'FF5', 5, 5, 4, 2, 6, 4, 6, 'lenna.png'),
(74, 'Garnet', 'FF9', 5, 5, 4, 2, 7, 4, 6, 'garnet.png'),
(75, 'Eiko', 'FF9', 5, 4, 3, 2, 7, 5, 6, 'eiko.png'),
(76, 'Aerith', 'FF7', 5, 4, 3, 1, 8, 4, 7, 'aerith.png'),
(77, 'Selphie', 'FF8', 5, 5, 4, 2, 6, 6, 6, 'selphie.png'),
(78, 'Yuna', 'FF10', 5, 5, 4, 2, 7, 5, 8, 'yuna.png'),
(79, 'Penelo', 'FF12', 5, 5, 4, 2, 6, 6, 6, 'penelo.png'),
(80, 'Vanille', 'FF13', 5, 5, 4, 2, 7, 5, 7, 'vanille.png'),
(81, 'Serah (Healer Mode)', 'FF13-2', 5, 5, 4, 2, 6, 6, 7, 'serah_healer.png'),
(82, 'Edward', 'FF4', 6, 4, 3, 2, 4, 5, 7, 'edward.png'),
(83, 'Tellah', 'FF4', 6, 4, 3, 2, 6, 4, 6, 'tellah.png'),
(84, 'Celes', 'FF6', 6, 6, 5, 5, 6, 5, 7, 'celes.png'),
(85, 'Gau', 'FF6', 6, 5, 4, 5, 4, 6, 6, 'gau.png'),
(86, 'Mog', 'FF6', 6, 5, 4, 4, 4, 6, 6, 'mog.png'),
(87, 'Gogo', 'FF6', 6, 5, 4, 5, 5, 5, 7, 'gogo.png'),
(88, 'Cait Sith', 'FF7', 6, 5, 4, 4, 4, 4, 7, 'caitsith.png'),
(89, 'Quistis', 'FF8', 6, 5, 4, 4, 5, 5, 7, 'quistis.png'),
(90, 'Quina', 'FF9', 6, 6, 5, 5, 5, 4, 7, 'quina.png'),
(91, 'Rikku', 'FF10', 6, 5, 4, 4, 4, 7, 9, 'rikku.png'),
(92, 'Hope', 'FF13', 6, 4, 3, 3, 6, 5, 8, 'hope.png'),
(93, 'Serah', 'FF13-2', 6, 5, 4, 3, 6, 6, 7, 'serah.png'),
(94, 'Ignis', 'FF15', 6, 6, 5, 4, 5, 6, 8, 'ignis.png');

-- --------------------------------------------------------

--
-- Table structure for table `t_roles`
--

CREATE TABLE `t_roles` (
  `role_id` int NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `t_roles`
--

INSERT INTO `t_roles` (`role_id`, `role_name`) VALUES
(1, 'Tank'),
(2, 'Melee Physical'),
(3, 'Ranged Physical'),
(4, 'Black Mage'),
(5, 'White Mage'),
(6, 'Support');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_characters`
--
ALTER TABLE `t_characters`
  ADD PRIMARY KEY (`character_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `t_roles`
--
ALTER TABLE `t_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_characters`
--
ALTER TABLE `t_characters`
  MODIFY `character_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `t_roles`
--
ALTER TABLE `t_roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_characters`
--
ALTER TABLE `t_characters`
  ADD CONSTRAINT `t_characters_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `t_roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
