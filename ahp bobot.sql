-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2026 at 03:00 AM
-- Server version: 8.0.46
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kurasi`
--

-- --------------------------------------------------------

--
-- Dumping data for table `ahp_sesi` (tabel induk, diisi PERTAMA)
--

INSERT INTO `ahp_sesi` (`id_ahp_sesi`, `nama_sesi`, `tanggal_sesi`, `lambda_max`, `ci`, `cr`, `status_aktif`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
(1, 'Penilaian Bobot 2026-05-25 01:36', '2026-05-25', '9.3205', '0.0401', '0.0276', 1, 1, '2026-05-25 01:35:51', '2026-05-24 18:36:07');

-- --------------------------------------------------------

--
-- Dumping data for table `ahp_bobot` (tabel anak, diisi SETELAH ahp_sesi)
--

INSERT INTO `ahp_bobot` (`id_ahp_bobot`, `id_ahp_sesi`, `id_kriteria`, `bobot_prioritas`) VALUES
(1, 1, 1, '0.078997'),
(2, 1, 2, '0.036435'),
(3, 1, 3, '0.078997'),
(4, 1, 4, '0.217325'),
(5, 1, 5, '0.060812'),
(6, 1, 6, '0.185001'),
(7, 1, 7, '0.104835'),
(8, 1, 8, '0.032395'),
(9, 1, 9, '0.205203');

-- --------------------------------------------------------

--
-- Dumping data for table `ahp_perbandingan` (tabel anak, diisi SETELAH ahp_sesi)
--

INSERT INTO `ahp_perbandingan` (`id_ahp_perbandingan`, `id_ahp_sesi`, `kriteria_1_id`, `kriteria_2_id`, `nilai_perbandingan`) VALUES
(1, 1, 1, 2, '3.0000'),
(2, 1, 1, 3, '1.0000'),
(3, 1, 1, 4, '0.3333'),
(4, 1, 1, 5, '1.0000'),
(5, 1, 1, 6, '0.3333'),
(6, 1, 1, 7, '1.0000'),
(7, 1, 1, 8, '3.0000'),
(8, 1, 1, 9, '0.3333'),
(9, 1, 2, 3, '0.3333'),
(10, 1, 2, 4, '0.2000'),
(11, 1, 2, 5, '1.0000'),
(12, 1, 2, 6, '0.2000'),
(13, 1, 2, 7, '0.3333'),
(14, 1, 2, 8, '1.0000'),
(15, 1, 2, 9, '0.2000'),
(16, 1, 3, 4, '0.3333'),
(17, 1, 3, 5, '1.0000'),
(18, 1, 3, 6, '0.3333'),
(19, 1, 3, 7, '1.0000'),
(20, 1, 3, 8, '3.0000'),
(21, 1, 3, 9, '0.3333'),
(22, 1, 4, 5, '5.0000'),
(23, 1, 4, 6, '1.0000'),
(24, 1, 4, 7, '3.0000'),
(25, 1, 4, 8, '5.0000'),
(26, 1, 4, 9, '1.0000'),
(27, 1, 5, 6, '0.3333'),
(28, 1, 5, 7, '0.3333'),
(29, 1, 5, 8, '3.0000'),
(30, 1, 5, 9, '0.3333'),
(31, 1, 6, 7, '1.0000'),
(32, 1, 6, 8, '5.0000'),
(33, 1, 6, 9, '1.0000'),
(34, 1, 7, 8, '3.0000'),
(35, 1, 7, 9, '0.3333'),
(36, 1, 8, 9, '0.2000');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;