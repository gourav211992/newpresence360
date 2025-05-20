-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2025 at 06:38 AM
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
-- Database: `erp_presence_360`
--

-- --------------------------------------------------------

--
-- Table structure for table `erp_production_levels`
--

CREATE TABLE `erp_production_levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `level` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(291) DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_production_routes`
--

CREATE TABLE `erp_production_routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(291) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_pr_details`
--

CREATE TABLE `erp_pr_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `production_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pr_parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `level` bigint(20) UNSIGNED DEFAULT NULL,
  `station_id` bigint(20) UNSIGNED DEFAULT NULL,
  `consumption` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_pr_parent_details`
--

CREATE TABLE `erp_pr_parent_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `production_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `level` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `erp_production_levels`
--
ALTER TABLE `erp_production_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erp_production_routes`
--
ALTER TABLE `erp_production_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erp_pr_details`
--
ALTER TABLE `erp_pr_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erp_pr_parent_details`
--
ALTER TABLE `erp_pr_parent_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `erp_production_levels`
--
ALTER TABLE `erp_production_levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_production_routes`
--
ALTER TABLE `erp_production_routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_pr_details`
--
ALTER TABLE `erp_pr_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_pr_parent_details`
--
ALTER TABLE `erp_pr_parent_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
