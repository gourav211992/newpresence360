-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 20, 2025 at 12:23 PM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finance_erpp30`
--

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_attributes`
--

CREATE TABLE `erp_gate_entry_attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_attribute_id` bigint UNSIGNED DEFAULT NULL COMMENT 'use tbl erp_item_attributes',
  `attr_name` bigint UNSIGNED DEFAULT NULL,
  `attr_value` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_attributes_history`
--

CREATE TABLE `erp_gate_entry_attributes_history` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_attribute_id` bigint UNSIGNED DEFAULT NULL COMMENT 'use tbl erp_item_attributes',
  `attr_name` bigint UNSIGNED DEFAULT NULL,
  `attr_value` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_details`
--

CREATE TABLE `erp_gate_entry_details` (
  `id` bigint UNSIGNED NOT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_order_item_id` bigint UNSIGNED DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_id` bigint UNSIGNED DEFAULT NULL,
  `hsn_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uom_id` bigint UNSIGNED DEFAULT NULL,
  `uom_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `store_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_qty` decimal(15,6) DEFAULT '0.000000',
  `receipt_qty` decimal(15,6) DEFAULT '0.000000',
  `accepted_qty` decimal(15,6) DEFAULT '0.000000',
  `rejected_qty` decimal(15,6) DEFAULT '0.000000',
  `mrn_qty` decimal(15,6) DEFAULT '0.000000',
  `inventory_uom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_uom_id` bigint UNSIGNED DEFAULT NULL,
  `inventory_uom_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_uom_qty` decimal(15,6) DEFAULT '0.000000',
  `rate` decimal(15,6) DEFAULT '0.000000',
  `basic_value` decimal(15,6) DEFAULT '0.000000',
  `discount_percentage` decimal(15,6) DEFAULT '0.000000',
  `discount_amount` decimal(15,6) DEFAULT '0.000000',
  `header_discount_amount` decimal(15,6) DEFAULT '0.000000',
  `net_value` decimal(15,6) DEFAULT '0.000000',
  `tax_value` decimal(15,6) DEFAULT '0.000000',
  `taxable_amount` decimal(15,6) DEFAULT '0.000000',
  `item_exp_amount` decimal(15,6) DEFAULT '0.000000',
  `header_exp_amount` decimal(15,6) DEFAULT '0.000000',
  `total_item_amount` decimal(15,6) DEFAULT '0.000000',
  `remark` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_details_history`
--

CREATE TABLE `erp_gate_entry_details_history` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_order_item_id` bigint UNSIGNED DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_id` bigint UNSIGNED DEFAULT NULL,
  `hsn_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uom_id` bigint UNSIGNED DEFAULT NULL,
  `uom_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `store_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_qty` decimal(15,6) DEFAULT '0.000000',
  `receipt_qty` decimal(15,6) DEFAULT '0.000000',
  `accepted_qty` decimal(15,6) DEFAULT '0.000000',
  `rejected_qty` decimal(15,6) DEFAULT '0.000000',
  `mrn_qty` decimal(15,6) DEFAULT '0.000000',
  `inventory_uom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_uom_id` bigint UNSIGNED DEFAULT NULL,
  `inventory_uom_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_uom_qty` decimal(15,6) DEFAULT '0.000000',
  `rate` decimal(15,6) DEFAULT '0.000000',
  `basic_value` decimal(15,6) DEFAULT '0.000000',
  `discount_percentage` decimal(15,6) DEFAULT '0.000000',
  `discount_amount` decimal(15,6) DEFAULT '0.000000',
  `header_discount_amount` decimal(15,6) DEFAULT '0.000000',
  `net_value` decimal(15,6) DEFAULT '0.000000',
  `tax_value` decimal(15,6) DEFAULT '0.000000',
  `taxable_amount` decimal(15,6) DEFAULT '0.000000',
  `item_exp_amount` decimal(15,6) DEFAULT '0.000000',
  `header_exp_amount` decimal(15,6) DEFAULT '0.000000',
  `total_item_amount` decimal(15,6) DEFAULT '0.000000',
  `remark` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_headers`
--

CREATE TABLE `erp_gate_entry_headers` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED DEFAULT NULL,
  `group_id` bigint UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_order_id` bigint UNSIGNED DEFAULT NULL,
  `series_id` bigint UNSIGNED DEFAULT NULL,
  `book_id` bigint UNSIGNED DEFAULT NULL,
  `book_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_number_type` enum('Auto','Manually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manually',
  `doc_reset_pattern` enum('Never','Yearly','Quarterly','Monthly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_no` int DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `customer_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_date` date DEFAULT NULL,
  `document_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revision_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revision_date` date DEFAULT NULL,
  `approval_level` int NOT NULL DEFAULT '1',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_entry_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_entry_date` date DEFAULT NULL,
  `supplier_invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_invoice_date` date DEFAULT NULL,
  `eway_bill_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consignment_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transporter_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_term_id` bigint UNSIGNED DEFAULT NULL,
  `payment_term_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_currency_id` bigint UNSIGNED DEFAULT NULL,
  `org_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `comp_currency_id` bigint UNSIGNED DEFAULT NULL,
  `comp_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comp_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `group_currency_id` bigint UNSIGNED DEFAULT NULL,
  `group_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `sub_total` decimal(15,6) DEFAULT NULL,
  `total_item_amount` decimal(15,6) DEFAULT NULL,
  `item_discount` decimal(15,6) DEFAULT NULL,
  `header_discount` decimal(15,6) DEFAULT NULL,
  `total_discount` decimal(15,6) DEFAULT NULL,
  `gst` decimal(15,6) DEFAULT NULL,
  `gst_details` json DEFAULT NULL,
  `taxable_amount` decimal(15,6) DEFAULT NULL,
  `total_taxes` decimal(15,6) DEFAULT NULL,
  `total_after_tax_amount` decimal(15,6) DEFAULT NULL,
  `expense_amount` decimal(15,6) DEFAULT NULL,
  `total_amount` decimal(15,6) DEFAULT NULL,
  `final_remark` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_headers_history`
--

CREATE TABLE `erp_gate_entry_headers_history` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `organization_id` bigint UNSIGNED DEFAULT NULL,
  `group_id` bigint UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_order_id` bigint UNSIGNED DEFAULT NULL,
  `series_id` bigint UNSIGNED DEFAULT NULL,
  `book_id` bigint UNSIGNED DEFAULT NULL,
  `book_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_number_type` enum('Auto','Manually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manually',
  `doc_reset_pattern` enum('Never','Yearly','Quarterly','Monthly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_no` int DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `customer_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_date` date DEFAULT NULL,
  `document_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revision_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revision_date` date DEFAULT NULL,
  `approval_level` int NOT NULL DEFAULT '1',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_entry_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_entry_date` date DEFAULT NULL,
  `supplier_invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_invoice_date` date DEFAULT NULL,
  `eway_bill_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consignment_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transporter_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_term_id` bigint UNSIGNED DEFAULT NULL,
  `payment_term_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_currency_id` bigint UNSIGNED DEFAULT NULL,
  `org_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `comp_currency_id` bigint UNSIGNED DEFAULT NULL,
  `comp_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comp_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `group_currency_id` bigint UNSIGNED DEFAULT NULL,
  `group_currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_currency_exg_rate` decimal(15,6) DEFAULT NULL,
  `sub_total` decimal(15,6) DEFAULT NULL,
  `total_item_amount` decimal(15,6) DEFAULT NULL,
  `item_discount` decimal(15,6) DEFAULT NULL,
  `header_discount` decimal(15,6) DEFAULT NULL,
  `total_discount` decimal(15,6) DEFAULT NULL,
  `gst` decimal(15,6) DEFAULT NULL,
  `gst_details` json DEFAULT NULL,
  `taxable_amount` decimal(15,6) DEFAULT NULL,
  `total_taxes` decimal(15,6) DEFAULT NULL,
  `total_after_tax_amount` decimal(15,6) DEFAULT NULL,
  `expense_amount` decimal(15,6) DEFAULT NULL,
  `total_amount` decimal(15,6) DEFAULT NULL,
  `final_remark` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_item_locations`
--

CREATE TABLE `erp_gate_entry_item_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `rack_id` bigint UNSIGNED DEFAULT NULL,
  `shelf_id` bigint UNSIGNED DEFAULT NULL,
  `bin_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` decimal(15,6) DEFAULT NULL,
  `inventory_uom_qty` decimal(15,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_item_locations_history`
--

CREATE TABLE `erp_gate_entry_item_locations_history` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `rack_id` bigint UNSIGNED DEFAULT NULL,
  `shelf_id` bigint UNSIGNED DEFAULT NULL,
  `bin_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` decimal(15,6) DEFAULT NULL,
  `inventory_uom_qty` decimal(15,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_media`
--

CREATE TABLE `erp_gate_entry_media` (
  `id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_ted`
--

CREATE TABLE `erp_gate_entry_ted` (
  `id` bigint UNSIGNED NOT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `ted_id` bigint UNSIGNED DEFAULT NULL,
  `ted_type` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_level` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `book_code` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_name` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_code` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assesment_amount` decimal(10,2) DEFAULT NULL,
  `ted_percentage` decimal(10,2) DEFAULT NULL,
  `ted_amount` decimal(10,2) DEFAULT NULL,
  `applicability_type` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_gate_entry_ted_history`
--

CREATE TABLE `erp_gate_entry_ted_history` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `header_id` bigint UNSIGNED DEFAULT NULL,
  `detail_id` bigint UNSIGNED DEFAULT NULL,
  `ted_id` bigint UNSIGNED DEFAULT NULL,
  `ted_type` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_level` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `book_code` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_name` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ted_code` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assesment_amount` decimal(10,2) DEFAULT NULL,
  `ted_percentage` decimal(10,2) DEFAULT NULL,
  `ted_amount` decimal(10,2) DEFAULT NULL,
  `applicability_type` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `erp_gate_entry_attributes`
--
ALTER TABLE `erp_gate_entry_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_attributes_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_attributes_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_attributes_history`
--
ALTER TABLE `erp_gate_entry_attributes_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_attributes_history_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_attributes_history_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_details`
--
ALTER TABLE `erp_gate_entry_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_details_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_details_history`
--
ALTER TABLE `erp_gate_entry_details_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_details_history_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_headers`
--
ALTER TABLE `erp_gate_entry_headers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_headers_status_index` (`status`);

--
-- Indexes for table `erp_gate_entry_headers_history`
--
ALTER TABLE `erp_gate_entry_headers_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_headers_history_status_index` (`status`);

--
-- Indexes for table `erp_gate_entry_item_locations`
--
ALTER TABLE `erp_gate_entry_item_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_item_locations_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_item_locations_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_item_locations_history`
--
ALTER TABLE `erp_gate_entry_item_locations_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_item_locations_history_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_item_locations_history_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_media`
--
ALTER TABLE `erp_gate_entry_media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `erp_gate_entry_media_uuid_unique` (`uuid`),
  ADD KEY `erp_gate_entry_media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `erp_gate_entry_media_order_column_index` (`order_column`);

--
-- Indexes for table `erp_gate_entry_ted`
--
ALTER TABLE `erp_gate_entry_ted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_ted_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_ted_header_id_foreign` (`header_id`);

--
-- Indexes for table `erp_gate_entry_ted_history`
--
ALTER TABLE `erp_gate_entry_ted_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `erp_gate_entry_ted_history_detail_id_foreign` (`detail_id`),
  ADD KEY `erp_gate_entry_ted_history_header_id_foreign` (`header_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `erp_gate_entry_attributes`
--
ALTER TABLE `erp_gate_entry_attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_attributes_history`
--
ALTER TABLE `erp_gate_entry_attributes_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_details`
--
ALTER TABLE `erp_gate_entry_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_details_history`
--
ALTER TABLE `erp_gate_entry_details_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_headers`
--
ALTER TABLE `erp_gate_entry_headers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_headers_history`
--
ALTER TABLE `erp_gate_entry_headers_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_item_locations`
--
ALTER TABLE `erp_gate_entry_item_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_item_locations_history`
--
ALTER TABLE `erp_gate_entry_item_locations_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_media`
--
ALTER TABLE `erp_gate_entry_media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_ted`
--
ALTER TABLE `erp_gate_entry_ted`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_gate_entry_ted_history`
--
ALTER TABLE `erp_gate_entry_ted_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `erp_gate_entry_attributes`
--
ALTER TABLE `erp_gate_entry_attributes`
  ADD CONSTRAINT `erp_gate_entry_attributes_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_attributes_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_attributes_history`
--
ALTER TABLE `erp_gate_entry_attributes_history`
  ADD CONSTRAINT `erp_gate_entry_attributes_history_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details_history` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_attributes_history_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_details`
--
ALTER TABLE `erp_gate_entry_details`
  ADD CONSTRAINT `erp_gate_entry_details_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_details_history`
--
ALTER TABLE `erp_gate_entry_details_history`
  ADD CONSTRAINT `erp_gate_entry_details_history_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_item_locations`
--
ALTER TABLE `erp_gate_entry_item_locations`
  ADD CONSTRAINT `erp_gate_entry_item_locations_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_item_locations_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_item_locations_history`
--
ALTER TABLE `erp_gate_entry_item_locations_history`
  ADD CONSTRAINT `erp_gate_entry_item_locations_history_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details_history` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_item_locations_history_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_ted`
--
ALTER TABLE `erp_gate_entry_ted`
  ADD CONSTRAINT `erp_gate_entry_ted_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_ted_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_gate_entry_ted_history`
--
ALTER TABLE `erp_gate_entry_ted_history`
  ADD CONSTRAINT `erp_gate_entry_ted_history_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `erp_gate_entry_details_history` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `erp_gate_entry_ted_history_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `erp_gate_entry_headers_history` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
