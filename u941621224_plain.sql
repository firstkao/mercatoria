-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 28, 2026 at 03:21 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u941621224_plain`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `subject_type`, `subject_id`, `metadata`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'register', NULL, NULL, NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 13:13:09'),
(2, 1, 'view_product', NULL, NULL, '{\"product_id\":1}', '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 13:14:07'),
(3, 1, 'login', NULL, NULL, NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-25 12:38:38'),
(4, 1, 'view_product', NULL, NULL, '{\"product_id\":2}', '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-25 12:39:04'),
(5, 1, 'view_product', NULL, NULL, '{\"product_id\":1}', '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-25 12:39:09'),
(6, 1, 'login', NULL, NULL, NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 05:25:53'),
(7, 1, 'logout', NULL, NULL, NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 05:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'First Kao', 'firstkao4@gmail.com', '$2y$12$5ns2KUXisfrLbxe.uDemueW2m1LVDFW6aglj8Y2TyIcnpqmGXGs3m', NULL, '2026-09-25 07:00:02', '2026-09-25 07:00:02');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `subject_type`, `subject_id`, `metadata`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, '2026-09-26 05:22:39'),
(2, 1, 'login', NULL, NULL, NULL, '2026-09-26 05:39:45'),
(3, 1, 'update_pricing', NULL, NULL, '{\"exchange_rate\":\"2800\",\"cn_id_rate_per_kg\":\"25000\",\"btm_jkt_rate_per_kg\":\"60000\",\"margin_percent\":\"11\",\"price_rounding\":\"5000\"}', '2026-09-26 07:39:28'),
(4, 1, 'update_display', NULL, NULL, '{\"promo_bar_text\":\"Gunakan kode promo NEWWEB dan nikmati potongan harga sebesar Rp 25.000 dengan minimal pembelanjaan Rp 150.000.\"}', '2026-09-26 07:40:30'),
(5, 1, 'login', NULL, NULL, NULL, '2026-09-28 07:11:18'),
(6, 1, 'login', NULL, NULL, NULL, '2026-09-28 07:16:04'),
(7, 1, 'create_hero_slide', 'App\\Models\\HeroSlide', 1, '{\"title\":null}', '2026-09-28 07:36:20'),
(8, 1, 'update_hero_slide', 'App\\Models\\HeroSlide', 1, '{\"title\":null}', '2026-09-28 07:39:38'),
(9, 1, 'create_hero_slide', 'App\\Models\\HeroSlide', 2, '{\"title\":null}', '2026-09-28 07:41:04'),
(10, 1, 'update_hero_slide', 'App\\Models\\HeroSlide', 2, '{\"title\":null}', '2026-09-28 07:41:28'),
(11, 1, 'login', NULL, NULL, NULL, '2026-09-28 14:55:32'),
(12, 1, 'create_hero_slide', 'App\\Models\\HeroSlide', 3, '{\"title\":\"Azur Lane\"}', '2026-09-28 14:57:02'),
(13, 1, 'update_hero_slide', 'App\\Models\\HeroSlide', 1, '{\"title\":\"Aether Gazer\"}', '2026-09-28 14:57:29'),
(14, 1, 'update_hero_slide', 'App\\Models\\HeroSlide', 2, '{\"title\":\"Arknight: Endfield\"}', '2026-09-28 14:57:55'),
(15, 1, 'create_hero_slide', 'App\\Models\\HeroSlide', 4, '{\"title\":\"Duet Night Abyss\"}', '2026-09-28 15:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appeals`
--

CREATE TABLE `appeals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `identity_record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(40) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_reminders`
--

CREATE TABLE `cart_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reminder_number` tinyint(3) UNSIGNED NOT NULL,
  `item_count` smallint(5) UNSIGNED NOT NULL,
  `sent_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coin_lots`
--

CREATE TABLE `coin_lots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `source` varchar(20) NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `birthday_year` smallint(5) UNSIGNED DEFAULT NULL,
  `amount` bigint(20) NOT NULL,
  `remaining` bigint(20) NOT NULL,
  `earned_at` timestamp NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coin_spends`
--

CREATE TABLE `coin_spends` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coin_lot_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `amount` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'used',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `developers`
--

INSERT INTO `developers` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Aisno Games', 'aisno-games', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(2, 'BluePoch', 'bluepoch', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(3, 'HyperGryph', 'hypergryph', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(4, 'Kuro Games', 'kuro-games', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(5, 'Manjuu Game', 'manjuu-game', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(6, 'miHoYo', 'mihoyo', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(7, 'NetEase Games', 'netease-games', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(8, 'Papergames', 'papergames', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(9, 'Tencent Games', 'tencent-games', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(10, 'Yongshi Technology', 'yongshi-technology', '2026-09-24 10:28:09', '2026-09-24 10:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `developer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `developer_id`, `name`, `slug`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Aether Gazer', 'aether-gazer', NULL, 0, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(2, NULL, 'Arknights', 'arknights', NULL, 1, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(3, NULL, 'Arknights: Endfield', 'arknights-endfield', NULL, 2, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(4, NULL, 'Azur Lane', 'azur-lane', NULL, 3, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(5, NULL, 'Beyond The World', 'beyond-the-world', NULL, 4, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(6, NULL, 'Duet Night Abyss', 'duet-night-abyss', NULL, 5, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(7, NULL, 'Genshin Impact', 'genshin-impact', NULL, 6, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(8, NULL, 'Honkai Impact 3', 'honkai-impact-3', NULL, 7, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(9, NULL, 'Honkai: Star Rail', 'honkai-star-rail', NULL, 8, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(10, NULL, 'Light and Night', 'light-and-night', NULL, 9, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(11, NULL, 'Love and Deepspace', 'love-and-deepspace', NULL, 10, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(12, NULL, 'Lovebrush Chronicles', 'lovebrush-chronicles', NULL, 11, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(13, NULL, 'Mr Love: Queen’s Choice', 'mr-love-queens-choice', NULL, 12, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(14, NULL, 'Path to Nowhere', 'path-to-nowhere', NULL, 13, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(15, NULL, 'Punishing: Gray Raven', 'punishing-gray-raven', NULL, 14, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(16, NULL, 'Reverse:1999', 'reverse1999', NULL, 15, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(17, NULL, 'Tears of Themis', 'tears-of-themis', NULL, 16, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(18, NULL, 'Where Winds Meet', 'where-winds-meet', NULL, 17, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(19, NULL, 'Wuthering Waves', 'wuthering-waves', NULL, 18, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(20, NULL, 'Zenless Zone Zero', 'zenless-zone-zero', NULL, 19, '2026-09-24 10:28:09', '2026-09-24 10:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_mobile_path` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `title`, `subtitle`, `image_path`, `image_mobile_path`, `alt_text`, `link_url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Aether Gazer', NULL, 'hero-slides/58j4AZUOr7qDJlPHjXgqRtlcKelGmiZy2WCwbT3Y.webp', NULL, NULL, NULL, 1, 0, '2026-09-28 07:36:20', '2026-09-28 14:57:29'),
(2, 'Arknight: Endfield', NULL, 'hero-slides/cJJvH1JH2G1IPKDLjHLm2oLtGYsdni8vDZwIxp7s.webp', NULL, NULL, NULL, 1, 1, '2026-09-28 07:41:04', '2026-09-28 14:57:55'),
(3, 'Azur Lane', NULL, 'hero-slides/Chyso8ZcKjtUmZagLeKyMGYCwRuJoPuj6IN9Rjjf.webp', NULL, NULL, NULL, 1, 2, '2026-09-28 14:57:02', '2026-09-28 14:57:02'),
(4, 'Duet Night Abyss', NULL, 'hero-slides/38dLawxUqinP4UmmCOYoULql1Jew9yqp8gZru5oP.webp', NULL, NULL, NULL, 1, 3, '2026-09-28 15:21:15', '2026-09-28 15:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `identity_records`
--

CREATE TABLE `identity_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kind` varchar(20) NOT NULL,
  `value` varchar(255) NOT NULL,
  `deletion_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `identity_records`
--

INSERT INTO `identity_records` (`id`, `kind`, `value`, `deletion_count`, `blocked_at`, `created_at`, `updated_at`) VALUES
(1, 'email', 'firstkao4@gmail.com', 0, NULL, '2026-09-24 13:13:09', '2026-09-24 13:13:09'),
(2, 'whatsapp', '628159792353', 0, NULL, '2026-09-24 13:13:09', '2026-09-24 13:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketplaces`
--

CREATE TABLE `marketplaces` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `fp_fee_idr` bigint(20) UNSIGNED NOT NULL,
  `dp_fee_percent` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marketplaces`
--

INSERT INTO `marketplaces` (`id`, `name`, `fp_fee_idr`, `dp_fee_percent`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Shopee', 109, 25.00, 1, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(2, 'Toco', 1, 0.00, 1, '2026-09-24 10:28:09', '2026-09-24 10:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(16, '0001_01_01_000000_create_users_table', 1),
(17, '0001_01_01_000001_create_cache_table', 1),
(18, '0001_01_01_000002_create_jobs_table', 1),
(19, '2026_09_24_085748_create_admins_table', 1),
(20, '2026_09_24_085749_create_identity_records_table', 1),
(21, '2026_09_24_085750_create_name_blacklist_table', 1),
(22, '2026_09_24_085752_create_catalog_settings_tables', 1),
(23, '2026_09_24_085753_create_games_and_developers_tables', 1),
(24, '2026_09_24_085754_create_products_tables', 1),
(25, '2026_09_24_085756_create_cart_items_table', 1),
(26, '2026_09_24_085757_create_vouchers_table', 1),
(27, '2026_09_24_085758_create_orders_tables', 1),
(28, '2026_09_24_085759_create_coin_tables', 1),
(29, '2026_09_24_085801_create_log_tables', 1),
(30, '2026_09_24_085802_create_hero_slides_table', 1),
(31, '2026_09_24_114204_create_reseller_applications_table', 2),
(32, '2026_09_25_052924_add_sku_to_products_and_variants', 3),
(33, '2026_09_25_055942_create_pages_table', 3),
(34, '2026_09_25_000001_add_payment_review_support_tables', 4),
(35, '2026_09_26_043803_create_hero_slides_table', 5),
(36, '2026_09_26_100000_add_columns_to_hero_slides_table', 5),
(37, '2026_09_26_110000_add_customer_fields_to_users_table', 5),
(38, '2026_09_26_130000_create_contact_messages_table', 6),
(39, '2026_09_26_160000_create_user_notifications_table', 6),
(40, '2026_09_26_170000_add_user_to_vouchers_table', 6),
(41, '2026_09_26_170100_create_voucher_redemptions_table', 7),
(42, '2024_01_01_000001_create_admins_table', 8),
(43, '2024_01_01_000002_create_settings_table', 8),
(44, '2024_01_01_000003_create_developers_table', 8),
(45, '2024_01_01_000004_create_games_table', 8),
(46, '2024_01_01_000005_create_shipping_tiers_table', 8),
(47, '2024_01_01_000006_create_products_table', 8),
(48, '2024_01_01_000007_create_product_variants_table', 8),
(49, '2024_01_01_000008_create_product_images_table', 8),
(50, '2024_01_01_000009_create_marketplaces_table', 8),
(51, '2024_01_01_000010_create_vouchers_table', 8),
(52, '2024_01_01_000011_create_orders_table', 8),
(53, '2024_01_01_000012_create_order_items_table', 8),
(54, '2024_01_01_000013_create_order_status_history_table', 8),
(55, '2024_01_01_000014_create_payment_methods_table', 8),
(56, '2024_01_01_000015_create_payment_proofs_table', 8),
(57, '2024_01_01_000016_create_coin_lots_table', 8),
(58, '2024_01_01_000017_create_coin_spends_table', 8),
(59, '2024_01_01_000018_create_identity_records_table', 8),
(60, '2024_01_01_000019_create_registration_events_table', 8),
(61, '2024_01_01_000020_create_name_blacklist_table', 8),
(62, '2024_01_01_000021_create_cart_items_table', 8),
(63, '2024_01_01_000022_create_product_views_table', 8),
(64, '2024_01_01_000023_create_admin_logs_table', 8),
(65, '2024_01_01_000024_create_activity_logs_table', 8),
(66, '2024_01_01_000025_create_reseller_applications_table', 8),
(67, '2026_09_26_180000_add_referral_code_to_users_table', 8),
(68, '2026_09_26_180100_create_referrals_table', 8),
(69, '2026_09_26_190000_create_preorder_pages_table', 8),
(70, '2026_09_26_200000_add_is_featured_to_products_table', 8),
(71, '2026_09_26_210000_add_best_seller_to_products_table', 8),
(72, '2026_09_26_230000_create_cart_reminders_table', 8),
(73, '2026_09_26_235000_add_email_otp_to_users_table', 8),
(74, '2026_09_26_240000_add_customer_note_to_orders_table', 8),
(75, '2026_09_26_240000_create_order_notes_table', 8),
(76, '2026_09_27_100000_add_performance_indexes', 8),
(77, '2026_09_27_110000_make_name_nullable_on_users_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `name_blacklist`
--

CREATE TABLE `name_blacklist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_normalized` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `marketplace_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu_pembayaran',
  `payment_scheme` varchar(2) NOT NULL,
  `subtotal_idr` bigint(20) UNSIGNED NOT NULL,
  `discount_type` varchar(10) NOT NULL DEFAULT 'none',
  `discount_idr` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_idr` bigint(20) UNSIGNED NOT NULL,
  `pay_now_idr` bigint(20) UNSIGNED NOT NULL,
  `remaining_idr` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `marketplace_fee_idr` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `coin_estimate` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `pricing_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pricing_snapshot`)),
  `payment_deadline_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `refund_note` text DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_addresses`
--

CREATE TABLE `order_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name_snapshot` varchar(255) NOT NULL,
  `variant_name_snapshot` varchar(255) NOT NULL,
  `price_yuan_snapshot` decimal(12,2) NOT NULL,
  `weight_grams_snapshot` int(10) UNSIGNED NOT NULL,
  `cn_shipping_yuan_snapshot` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price_idr` bigint(20) UNSIGNED NOT NULL,
  `quantity` smallint(5) UNSIGNED NOT NULL,
  `line_total_idr` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_notes`
--

CREATE TABLE `order_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `body` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` varchar(10) NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_footer` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `is_published`, `show_in_footer`, `sort_order`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Tanya Jawab Umum', 'faq', '## Akun\r\n\r\n**Kenapa harus punya akun untuk melihat produk?**\r\nKatalog Mercatoria hanya dapat diakses oleh pengguna yang memiliki akun. Pendaftaran gratis dan hanya terbuka untuk pengguna dengan alamat dan nomor WhatsApp Indonesia.\r\n\r\n**Data apa saja yang dibutuhkan saat mendaftar?**\r\nNama asli, tanggal lahir, alamat lengkap di Indonesia, nomor WhatsApp, email, dan kata sandi. Nama harus sesuai identitas: hanya huruf, minimal dua kata, tanpa angka, emoji, atau nama karakter game/anime. Pengguna di bawah 18 tahun wajib mendapat persetujuan orang tua/wali.\r\n\r\n**Kenapa saya hanya bisa melihat 10 produk?**\r\nAkun yang belum pernah berbelanja hanya dapat membuka 10 halaman detail produk. Membuka produk yang sama lebih dari sekali tetap dihitung. Sisa kuota tampil di halaman akun dan katalog. Setelah pembayaran pertama Anda terverifikasi, batas ini hilang.\r\n\r\n**Kenapa akun saya terhapus?**\r\nAkun yang belum pernah berbelanja dihapus otomatis 30 hari setelah pendaftaran, atau jika bukti pembayaran tidak diunggah dalam 24 jam setelah checkout. Anda boleh mendaftar ulang, tetapi email dan nomor WhatsApp yang sama hanya dapat dipakai mendaftar maksimal 3 kali. Setelah itu, email dan nomor tersebut diblokir.\r\n\r\n**Akun saya diblokir atau kuota lihat produk habis. Apa yang bisa dilakukan?**\r\nHubungi kami melalui chat. Admin dapat me-reset blokir atau kuota lihat produk Anda. Setelah reset, aturan akun tetap berlaku seperti biasa.\r\n\r\n**Apakah tanggal lahir bisa diubah?**\r\nTanggal lahir dikunci setelah pendaftaran karena dipakai untuk koin dan voucher ulang tahun. Nama dan alamat dapat diubah di menu Profile.\r\n\r\n## Harga dan pembayaran\r\n\r\n**Apakah harga yang tertera sudah termasuk semua biaya?**\r\nHarga sudah termasuk biaya pengiriman ke Indonesia dan kemasan. Harga belum termasuk ongkos kirim dalam negeri serta biaya marketplace, yang dibayar di Shopee atau Toco. Harga dikunci saat checkout.\r\n\r\n**Metode pembayaran apa saja yang diterima?**\r\nTransfer bank BCA atau QRIS. Anda dapat memilih Down Payment (DP) atau Full Payment (FP).\r\n\r\n**Berapa DP-nya?**\r\nDP sebesar 50% dari total transaksi dibayar di website. Sisa 50% dibayar di Shopee atau Toco saat barang sudah tiba di gudang Indonesia.\r\n\r\n**Apa itu biaya marketplace?**\r\nPesanan dikirim melalui Shopee atau Toco, sesuai pilihan Anda di keranjang. Biaya marketplace dibayar di marketplace, bukan di website, dan nominalnya sudah terlihat saat checkout.\r\n\r\n| Marketplace | Full Payment | Down Payment |\r\n|---|---|---|\r\n| Shopee | Rp109 | Pelunasan + 25% dari nominal pelunasan |\r\n| Toco | Rp1 | Pelunasan tanpa biaya tambahan |\r\n\r\nNominal biaya dapat berubah mengikuti kebijakan marketplace. Nominal yang berlaku adalah yang tertera saat checkout.\r\n\r\n**Bagaimana cara membayar?**\r\n1. Pilih potongan (koin atau voucher), DP atau FP, dan marketplace di keranjang, lalu checkout.\r\n2. Transfer ke BCA atau QRIS sesuai nominal yang tertera.\r\n3. Unggah bukti transfer pada pesanan Anda dalam 24 jam.\r\n4. Admin memverifikasi pembayaran, dan status pesanan berubah menjadi Pembayaran Diterima.\r\n\r\n**Apa yang terjadi jika saya tidak membayar dalam 24 jam?**\r\nPesanan dibatalkan. Untuk akun yang belum pernah berbelanja, akun dan pesanannya ikut dihapus dan dihitung sebagai satu kali pendaftaran.\r\n\r\n**Bukti pembayaran saya ditolak. Bagaimana?**\r\nAnda akan menerima email penolakan, lalu dapat mengunggah ulang bukti yang benar dalam 24 jam sejak email tersebut.\r\n\r\n## Koin dan voucher\r\n\r\n**Bagaimana cara mendapatkan koin?**\r\n- 1% dari total transaksi (contoh: belanja Rp170.000 mendapat 1.700 koin)\r\n- Bonus 1.000 koin untuk pembelian pertama\r\n- 1.000 koin di hari ulang tahun\r\n\r\nKoin belanja dan bonus masuk setelah pesanan berstatus Selesai. Biaya marketplace tidak dihitung.\r\n\r\n**Bagaimana cara memakai koin?**\r\n1 koin = Rp1. Koin dapat dipakai maksimal 5% dari subtotal per transaksi. Koin hangus 1 tahun sejak diterima, dan kami akan mengingatkan 7 hari sebelumnya.\r\n\r\n**Apakah koin dan voucher bisa dipakai bersamaan?**\r\nTidak. Dalam satu transaksi, pilih salah satu: koin atau voucher.\r\n\r\n**Kapan voucher ulang tahun bisa dipakai?**\r\nMulai 3 hari sebelum sampai 3 hari setelah tanggal lahir Anda. Setelah itu voucher hangus.\r\n\r\n## Pesanan dan pengiriman\r\n\r\n**Kapan pesanan diproses?**\r\nPesanan non-presale diproses setiap tanggal 26 setiap bulan. Pesanan presale diproses paling lambat beberapa jam sebelum masa preorder berakhir.\r\n\r\n**Berapa lama pengiriman?**\r\n- **Barang sudah rilis:** toko resmi mengirim paling lambat 5 hari kerja, lalu pengiriman ke Indonesia sekitar 45 hari sejak barang dikirim dari gudang Tiongkok.\r\n- **Barang presale:** menunggu tanggal rilis dari toko resmi, yang dapat maju atau mundur. Setelah rilis, pengiriman ke Indonesia sekitar 45 hari. Rata-rata total waktu presale minimal 120 hari sejak pemesanan.\r\n\r\nEstimasi belum termasuk keterlambatan bea cukai, seperti pemeriksaan jalur merah, atau hal lain di luar kendali Mercatoria.\r\n\r\n**Bagaimana memantau pesanan?**\r\nStatus pesanan tampil di menu Order Saya, dan setiap perubahan status dikirim ke email Anda: Menunggu Pembayaran, Ditahan, Pembayaran Diterima, Sedang Diproses, Sampai WH CN, Dikirim ke Indonesia, Bea Cukai, Sampai WH Indonesia, dan Selesai.\r\n\r\n**Apakah pesanan bisa dibatalkan?**\r\nPembeli tidak dapat membatalkan pesanan. Admin dapat membatalkan pesanan sebelum masuk tahap Sedang Diproses jika stok di toko resmi kosong atau kuota pembelian minimum tidak terpenuhi. Dalam hal ini, pembayaran dikembalikan penuh oleh admin. Koin yang dipakai dikembalikan, begitu juga voucher selama masa berlakunya belum habis.\r\n\r\n**Bisakah menginapkan barang?**\r\nYa, hanya dalam dua kasus, dan wajib memberi tahu kami terlebih dahulu:\r\n- Anda sedang bepergian ke luar kota/negeri\r\n- Anda ingin checkout di tanggal kembar (contoh: 11.11) atau hari gajian\r\n\r\nMenginapkan barang untuk menunggu pesanan lain tidak diperbolehkan. Barang wajib di-checkout dalam 7 hari setelah pemberitahuan. Jika tidak, barang beserta pembayarannya menjadi milik Mercatoria.\r\n\r\n## Produk dan garansi\r\n\r\n**Apakah bisa minta dicarikan barang?**\r\nYa, hubungi kami melalui chat. Barang limited atau eksklusif sulit ditemukan dan harganya kemungkinan lebih tinggi. Kami juga dapat membantu membeli produk Tmall yang tidak ada di katalog, atau dari penjual Taobao. Untuk pembelian dari penjual Taobao, keaslian dan kondisi barang berada di luar tanggung jawab Mercatoria.\r\n\r\n**Apakah ada garansi?**\r\nSemua produk bersumber dari toko resmi di Tmall. Sebagian besar produk tidak bergaransi karena toko resmi tidak menerima pengembalian untuk cacat pabrik. Kerusakan atau kehilangan akibat kurir berada di luar tanggung jawab Mercatoria.\r\n\r\n**Apa syarat pengembalian barang?**\r\nPengembalian hanya diterima jika terjadi kesalahan pengiriman oleh Mercatoria, dengan barang masih tersegel, dan hanya diproses melalui marketplace. Pengembalian karena cacat pabrik ditolak. Pelanggan yang memaksakan pengembalian dengan alasan tersebut dapat dimasukkan ke daftar hitam.\r\n\r\n**Apakah ada diskon pembelian dalam jumlah besar?**\r\nAda. Hubungi kami untuk informasi syarat dan penawarannya.\r\n\r\n**Apakah bisa menjadi reseller?**\r\nYa. Silakan daftar di [halaman Reseller](/reseller).', 1, 1, 1, 0, '2026-09-25 06:58:25', '2026-09-25 06:58:25'),
(2, 'Reseller', 'reseller', 'Mercatoria membuka kesempatan bagi kamu yang ingin menjual kembali merchandise game dari kami.\r\n\r\n## Siapa yang bisa mendaftar\r\n\r\n- Berdomisili di Indonesia dengan nomor WhatsApp Indonesia.\r\n- Memiliki tempat berjualan, misalnya akun media sosial, toko marketplace, atau toko offline/event.\r\n- Menggunakan nama asli sesuai identitas.\r\n\r\n## Proses pendaftaran\r\n\r\n1. Isi formulir di bawah ini.\r\n2. Admin meninjau pendaftaran dan menghubungi kamu melalui WhatsApp.\r\n3. Jika disetujui, admin menjelaskan harga reseller, minimal order, dan cara pemesanan.\r\n\r\n## Ketentuan umum\r\n\r\n1. Pesanan reseller mengikuti [Syarat & Ketentuan](/syarat-dan-ketentuan) Mercatoria, termasuk sistem pre-order, pembayaran DP/FP, dan estimasi pengiriman.\r\n2. Harga reseller, minimal order, dan penawaran lain bersifat pribadi dan tidak boleh disebarkan.\r\n3. Reseller dilarang menggunakan foto atau konten Mercatoria tanpa izin.\r\n4. Mercatoria berhak menolak pendaftaran atau menghentikan kerja sama jika reseller melanggar ketentuan.\r\n5. Pendaftaran tidak menjamin persetujuan.', 1, 1, 2, 1, '2026-09-25 06:58:25', '2026-09-25 06:58:25'),
(3, 'Syarat & Ketentuan', 'syarat-dan-ketentuan', 'Berlaku sejak: Februari 2025\r\n\r\nDengan membuat akun dan menggunakan mercatoria.id, Anda menyetujui Syarat & Ketentuan ini serta [Kebijakan Privasi](/kebijakan-privasi).\r\n\r\n## 1. Tentang Mercatoria\r\n\r\nMercatoria adalah Group Order Manager (GOM) yang membantu pembelian merchandise game dari toko resmi di Tmall dan penjual Taobao untuk dikirim ke Indonesia. Sebagian besar produk dijual dengan sistem pre-order.\r\n\r\n## 2. Akun\r\n\r\n1. Pendaftaran hanya untuk pengguna dengan alamat di Indonesia dan nomor WhatsApp Indonesia (+62/08).\r\n2. Nama wajib sesuai identitas: hanya huruf, minimal dua kata, tanpa angka, emoji, atau nama karakter game/anime. Pendaftaran yang tidak memenuhi syarat akan ditolak.\r\n3. Data yang Anda berikan harus benar. Tanggal lahir tidak dapat diubah setelah pendaftaran.\r\n4. Pengguna di bawah 18 tahun wajib mendapatkan persetujuan orang tua atau wali.\r\n5. Anda bertanggung jawab menjaga kerahasiaan kata sandi dan seluruh aktivitas di akun Anda.\r\n6. Seluruh aktivitas akun dicatat sesuai Kebijakan Privasi.\r\n\r\n## 3. Akun yang belum berbelanja\r\n\r\n1. Akun yang belum memiliki pembayaran terverifikasi hanya dapat membuka **10 halaman detail produk**. Membuka produk yang sama berkali-kali tetap dihitung.\r\n2. Akun tersebut **dihapus otomatis 30 hari** setelah pendaftaran jika belum ada pembayaran terverifikasi.\r\n3. Akun dan pesanannya juga dihapus jika bukti pembayaran tidak diunggah dalam 24 jam setelah checkout.\r\n4. Email dan nomor WhatsApp yang sama hanya dapat dipakai mendaftar **maksimal 3 kali**, termasuk pendaftaran pertama. Setelah akun ketiga terhapus, email dan nomor tersebut **diblokir** dan tidak dapat digunakan untuk mendaftar maupun login.\r\n5. Akun menjadi akun pelanggan setelah pembayaran DP atau FP pertama diverifikasi. Seluruh batasan di bagian ini tidak berlaku lagi untuk akun pelanggan.\r\n6. Permintaan reset blokir atau reset kuota lihat produk dapat diajukan melalui chat dan diputuskan oleh admin. Setelah reset, ketentuan ini tetap berlaku.\r\n\r\n## 4. Harga\r\n\r\n1. Harga ditampilkan dalam Rupiah dan sudah termasuk biaya pengiriman ke Indonesia serta kemasan.\r\n2. Harga belum termasuk ongkos kirim dalam negeri dan biaya marketplace.\r\n3. Harga dapat berubah sewaktu-waktu mengikuti kurs dan biaya pengiriman. Harga yang berlaku adalah harga saat checkout.\r\n\r\n## 5. Pembayaran\r\n\r\n1. Pembayaran dilakukan melalui transfer BCA atau QRIS.\r\n2. Pilihan pembayaran:\r\n   - **Full Payment (FP):** 100% dibayar di website.\r\n   - **Down Payment (DP):** 50% dibayar di website, sisa 50% dibayar di marketplace saat barang tiba di gudang Indonesia.\r\n3. Bukti transfer wajib diunggah **dalam 24 jam** setelah checkout. Jika tidak, pesanan dibatalkan otomatis.\r\n4. Jika bukti ditolak, bukti baru wajib diunggah dalam 24 jam sejak email penolakan.\r\n5. Pesanan diproses setelah pembayaran diverifikasi admin.\r\n\r\n## 6. Marketplace dan biaya marketplace\r\n\r\n1. Pengiriman dalam negeri dilakukan melalui Shopee atau Toco sesuai pilihan Anda di keranjang.\r\n2. Biaya marketplace dibayar di marketplace. Nominal yang berlaku adalah yang tertera saat checkout. Saat ini:\r\n   - Shopee: Rp109 untuk FP; untuk DP, 25% dari nominal pelunasan.\r\n   - Toco: Rp1 untuk FP; untuk DP, tanpa biaya tambahan.\r\n3. Pesanan dinyatakan **Selesai** setelah marketplace menunjukkan pesanan telah terkirim.\r\n\r\n## 7. Pembatalan dan pengembalian dana\r\n\r\n1. Pembeli tidak dapat membatalkan pesanan.\r\n2. Mercatoria dapat membatalkan pesanan sebelum tahap Sedang Diproses, antara lain jika stok di toko resmi kosong atau kuota pembelian minimum tidak terpenuhi.\r\n3. Untuk pesanan yang dibatalkan Mercatoria, pembayaran dikembalikan penuh. Koin yang dipakai dikembalikan dengan tanggal hangus semula. Voucher dikembalikan selama masa berlakunya belum habis.\r\n\r\n## 8. Koin\r\n\r\n1. Koin diperoleh dari:\r\n   - 1% dari total transaksi, dibulatkan ke bawah\r\n   - Bonus 1.000 koin untuk pembelian pertama\r\n   - 1.000 koin saat ulang tahun\r\n2. Koin belanja dan bonus diberikan setelah pesanan berstatus Selesai. Biaya marketplace tidak dihitung.\r\n3. 1 koin bernilai Rp1 dan dapat dipakai maksimal 5% dari subtotal per transaksi.\r\n4. Koin berlaku 1 tahun sejak diterima, tidak dapat diuangkan, dan tidak dapat dipindahkan ke akun lain.\r\n5. Koin tidak dapat digabung dengan voucher dalam satu transaksi.\r\n\r\n## 9. Voucher\r\n\r\n1. Voucher tunduk pada syarat masing-masing voucher, seperti masa berlaku dan minimal belanja.\r\n2. Satu transaksi hanya dapat memakai satu voucher dan tidak dapat digabung dengan koin.\r\n3. Voucher ulang tahun berlaku dari 3 hari sebelum sampai 3 hari setelah tanggal lahir.\r\n\r\n## 10. Pengiriman dan penyimpanan barang\r\n\r\n1. Estimasi waktu pengiriman bersifat perkiraan dan dapat berubah karena jadwal rilis toko resmi, bea cukai, atau hal lain di luar kendali Mercatoria.\r\n2. Penyimpanan barang di gudang hanya diizinkan dengan pemberitahuan terlebih dahulu, untuk bepergian atau checkout di tanggal promo.\r\n3. Barang wajib di-checkout dalam 7 hari setelah pemberitahuan. Jika tidak, barang beserta pembayarannya menjadi milik Mercatoria.\r\n\r\n## 11. Garansi dan pengembalian barang\r\n\r\n1. Sebagian besar produk tidak bergaransi karena kebijakan toko resmi.\r\n2. Pengembalian hanya diterima untuk kesalahan pengiriman oleh Mercatoria, dengan barang tersegel, dan diproses melalui marketplace.\r\n3. Kerusakan atau kehilangan akibat kurir, maupun cacat pabrik, tidak dapat dikembalikan.\r\n\r\n## 12. Larangan\r\n\r\nPengguna dilarang:\r\n\r\n1. Mendaftar dengan data palsu atau nama yang tidak sesuai identitas\r\n2. Membuat akun berulang untuk menghindari batasan akun\r\n3. Menyalin, mengunduh, atau menyebarkan foto dan konten situs tanpa izin\r\n4. Mengganggu keamanan atau kinerja situs\r\n5. Memaksakan pengembalian yang tidak sesuai ketentuan\r\n\r\nPelanggaran dapat mengakibatkan pemblokiran akun dan pencantuman dalam daftar hitam Mercatoria.\r\n\r\n## 13. Perubahan ketentuan\r\n\r\nMercatoria dapat mengubah Syarat & Ketentuan ini. Perubahan penting akan diberitahukan melalui email atau pemberitahuan di situs. Pesanan yang sudah dibuat mengikuti ketentuan yang berlaku saat checkout.\r\n\r\n## 14. Kontak\r\n\r\n- Email: cs@mercatoria.id\r\n- WhatsApp: +62 812-1968-3709', 1, 1, 3, 1, '2026-09-25 06:58:25', '2026-09-25 06:58:25'),
(4, 'Kebijakan Privasi', 'kebijakan-privasi', 'Berlaku sejak: Februari 2025\r\n\r\nSitus web MERCATORIA (mercatoria.id) dimiliki dan dikelola oleh MERCATORIA, selaku pengendali data pribadi Anda sesuai Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).\r\n\r\nKebijakan ini menjelaskan data apa yang kami kumpulkan, untuk apa, berapa lama kami menyimpannya, dan hak Anda atas data tersebut. Dengan membuat akun, Anda menyatakan telah membaca dan menyetujui Kebijakan Privasi ini.\r\n\r\n## 1. Data yang kami kumpulkan\r\n\r\n**Data yang Anda berikan saat mendaftar dan berbelanja:**\r\n\r\n- Nama lengkap sesuai identitas\r\n- Tanggal lahir\r\n- Alamat lengkap di Indonesia\r\n- Nomor WhatsApp\r\n- Alamat email\r\n- Kata sandi (disimpan dalam bentuk terenkripsi; kami tidak dapat melihatnya)\r\n- Persetujuan orang tua/wali, bagi pengguna di bawah 18 tahun\r\n- Data pesanan: produk, varian, jumlah, harga, pilihan DP/FP, dan pilihan marketplace\r\n- Bukti transfer dan tujuan pembayaran (BCA atau QRIS)\r\n\r\n**Data yang tercatat otomatis saat Anda menggunakan situs:**\r\n\r\n- Aktivitas akun selama login, antara lain: login dan logout, produk yang dibuka, perubahan profil, isi keranjang, checkout, unggah bukti pembayaran, serta pemakaian koin dan voucher\r\n- Jumlah detail produk yang telah Anda buka (kuota lihat produk)\r\n- Informasi perangkat: alamat IP, jenis browser, dan waktu akses\r\n- Cookie yang diperlukan agar Anda tetap login dan keranjang tersimpan\r\n\r\n## 2. Untuk apa data digunakan\r\n\r\n- Membuat dan mengelola akun Anda\r\n- Memastikan pendaftar adalah warga dengan alamat dan nomor Indonesia yang menggunakan nama asli\r\n- Memproses pesanan, memverifikasi pembayaran, dan memindahkan pesanan ke marketplace untuk pengiriman\r\n- Mengirim email pemberitahuan: status pesanan, hasil verifikasi pembayaran, koin, dan voucher\r\n- Memberikan koin dan voucher ulang tahun berdasarkan tanggal lahir\r\n- Mencegah penyalahgunaan, termasuk pendaftaran berulang, akun palsu, dan spam\r\n- Menyusun laporan penjualan internal\r\n\r\nKami tidak menjual data pribadi Anda dan tidak menggunakannya untuk iklan pihak ketiga.\r\n\r\n## 3. Pihak ketiga yang menerima data\r\n\r\nKami hanya membagikan data sebatas yang diperlukan kepada:\r\n\r\n- **Shopee atau Toco**, sesuai pilihan Anda, untuk pelunasan, ongkos kirim, dan pengiriman pesanan\r\n- **Cloudflare**, untuk pemeriksaan keamanan (Turnstile) pada halaman login dan pendaftaran\r\n- **Penyedia hosting dan layanan email**, untuk menjalankan situs dan mengirim email pemberitahuan\r\n\r\nKami juga dapat mengungkapkan data jika diwajibkan oleh hukum atau aparat yang berwenang.\r\n\r\n## 4. Berapa lama data disimpan\r\n\r\n- **Akun yang belum pernah berbelanja** dihapus otomatis 30 hari setelah pendaftaran, atau lebih cepat jika pembayaran tidak diunggah dalam 24 jam setelah checkout. Profil, pesanan, keranjang, dan riwayat aktivitasnya ikut dihapus.\r\n- **Alamat email, nomor WhatsApp, dan jumlah pendaftaran** tetap kami simpan setelah akun dihapus. Data ini dipakai untuk membatasi pendaftaran maksimal 3 kali dan memblokir email/nomor yang melampaui batas. Data ini dihapus jika blokir di-reset atas permintaan Anda.\r\n- **Akun pelanggan** (sudah memiliki pembayaran terverifikasi) disimpan selama akun aktif.\r\n- **Riwayat aktivitas akun pelanggan** disimpan selama 12 bulan.\r\n- Jika akun pelanggan dihapus, data pribadi Anda (nama, alamat, WhatsApp, email, kata sandi) dihapus. Catatan transaksi tetap disimpan tanpa identitas Anda untuk keperluan pembukuan dan laporan.\r\n- **Koin** hangus 1 tahun sejak diterima.\r\n\r\n## 5. Pengguna di bawah 18 tahun\r\n\r\nPengguna di bawah 18 tahun wajib mendapatkan persetujuan orang tua atau wali saat mendaftar. Dengan mencentang persetujuan tersebut, pendaftar menyatakan bahwa orang tua/wali telah mengetahui dan menyetujui penggunaan data sesuai kebijakan ini.\r\n\r\n## 6. Hak Anda\r\n\r\nSesuai UU PDP, Anda berhak untuk:\r\n\r\n- Mengetahui dan meminta salinan data pribadi Anda\r\n- Memperbaiki data yang tidak akurat. Nama dan alamat dapat diubah sendiri di menu Profile; tanggal lahir hanya dapat diubah melalui permintaan kepada kami.\r\n- Meminta penghapusan akun dan data pribadi Anda\r\n- Menarik persetujuan pemrosesan data. Penarikan persetujuan berarti akun tidak dapat digunakan lagi.\r\n\r\nPermintaan dapat diajukan melalui kontak di bawah. Kami akan memverifikasi identitas Anda sebelum memproses permintaan.\r\n\r\n## 7. Keamanan\r\n\r\nKami menjaga data Anda dengan langkah administratif dan teknis yang wajar, termasuk koneksi terenkripsi (HTTPS), kata sandi terenkripsi, dan pembatasan akses ke dashboard admin. Namun, tidak ada transmisi data melalui internet yang dapat dijamin sepenuhnya aman.\r\n\r\n## 8. Perubahan kebijakan\r\n\r\nKami dapat memperbarui Kebijakan Privasi ini. Perubahan penting akan diberitahukan melalui email atau pemberitahuan di situs.\r\n\r\n## 9. Kontak\r\n\r\n- Email: cs@mercatoria.id\r\n- WhatsApp: +62 812-1968-3709', 1, 1, 4, 1, '2026-09-25 06:58:25', '2026-09-25 06:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL,
  `label` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `qris_image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED NOT NULL,
  `payment_stage` varchar(10) NOT NULL DEFAULT 'dp',
  `amount_idr` bigint(20) UNSIGNED NOT NULL,
  `proof_path` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `reject_reason` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `resubmit_deadline_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preorder_banners`
--

CREATE TABLE `preorder_banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `preorder_page_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preorder_pages`
--

CREATE TABLE `preorder_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `game_id` bigint(20) UNSIGNED DEFAULT NULL,
  `developer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shipping_tier_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tag` varchar(20) DEFAULT NULL,
  `sale_starts_at` timestamp NULL DEFAULT NULL,
  `sale_ends_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `best_seller_score` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `best_seller_rank` smallint(5) UNSIGNED DEFAULT NULL,
  `exclude_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `featured_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `game_id`, `developer_id`, `shipping_tier_id`, `name`, `slug`, `sku`, `description`, `tag`, `sale_starts_at`, `sale_ends_at`, `is_published`, `is_featured`, `best_seller_score`, `best_seller_rank`, `exclude_best_seller`, `featured_order`, `created_at`, `updated_at`) VALUES
(1, 17, 6, 6, '[CONTOH] Tears of Themis Chase Series Badge', 'contoh-tot-chase-badge', NULL, 'Produk contoh untuk tes. Hapus setelah selesai.', 'presale', '2026-09-23 12:43:45', '2026-10-01 12:43:45', 1, 0, 0, NULL, 0, 0, '2026-09-24 12:43:45', '2026-09-24 12:43:45'),
(2, 4, 5, 1, '[CONTOH] Azur Lane PVC Figure 1/7 Jade', 'contoh-al-jade', NULL, NULL, 'limited', NULL, NULL, 1, 0, 0, NULL, 0, 0, '2026-09-24 12:43:45', '2026-09-24 12:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price_yuan` decimal(12,2) NOT NULL,
  `compare_price_yuan` decimal(12,2) DEFAULT NULL,
  `weight_grams` int(10) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `name`, `sku`, `image_path`, `price_yuan`, `compare_price_yuan`, `weight_grams`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Luke', NULL, NULL, 50.00, 60.00, 500, 'available', 1, '2026-09-24 12:43:45', '2026-09-24 12:43:45'),
(2, 1, 'Artem', NULL, NULL, 50.00, NULL, 500, 'available', 2, '2026-09-24 12:43:45', '2026-09-24 12:43:45'),
(3, 1, 'Vyn', NULL, NULL, 50.00, NULL, 500, 'out_of_stock', 3, '2026-09-24 12:43:45', '2026-09-24 12:43:45'),
(4, 1, 'Marius', NULL, NULL, 50.00, NULL, 500, 'available', 4, '2026-09-24 12:43:45', '2026-09-24 12:43:45'),
(5, 2, 'Standard', NULL, NULL, 899.00, NULL, 1800, 'out_of_stock', 0, '2026-09-24 12:43:45', '2026-09-24 12:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

CREATE TABLE `product_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_views`
--

INSERT INTO `product_views` (`id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 1, 1, '2026-09-24 13:14:07'),
(2, 1, 2, '2026-09-25 12:39:04'),
(3, 1, 1, '2026-09-25 12:39:09');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referee_id` bigint(20) UNSIGNED NOT NULL,
  `referral_code` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `referrer_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `referee_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_events`
--

CREATE TABLE `registration_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identity_record_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registration_events`
--

INSERT INTO `registration_events` (`id`, `identity_record_id`, `event`, `created_at`) VALUES
(1, 1, 'registered', '2026-09-24 13:13:09'),
(2, 2, 'registered', '2026-09-24 13:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `reseller_applications`
--

CREATE TABLE `reseller_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `sales_channel` varchar(30) NOT NULL,
  `store_link` varchar(255) DEFAULT NULL,
  `monthly_estimate` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('bgUQQjE6pPrYCXYgfBDorPjs4cBitrb8XDFRiOiW', NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJadFdqd0Vrb1lqVHdRbWZJOE9pRUdnNUlkQ21ERVVucWhBYTQ1VFhsIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9wbGFpbi5tZXJjYXRvcmlhLmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF9hZG1pbiI6ImI4MjlmZGZiNWIwNzU0ODhlZGQxZjQ5YmFkMzUzNzFiNWExNTllNzhjYzEzMGQ2OGNlZWQ0YjY0NTZlM2VjNjkifQ==', 1790608880),
('EmimBijPfsWu53nLGDhfSPFdElzOYSDWBBLVNJfU', NULL, '46.17.174.173', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:98.0) Gecko/20100101 Firefox/98.0', 'eyJfdG9rZW4iOiJwazNVS3F6ZVV1ZkhHTURuajFYMnVZOXo1MjlUT3pVdnBndldleHF5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9wbGFpbi5tZXJjYXRvcmlhLmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1790582150),
('icFpIwuI7cnYo6UFQKIyhW2zuHR2BsOHXxuIYQ4M', NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI5d2lTbkc3eFNKcmx6TmNINW9QdkhjbGRGTlF3NWYxOUZwR0d6YnUxIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cHM6XC9cL3BsYWluLm1lcmNhdG9yaWEuaWQiLCJyb3V0ZSI6ImhvbWUifSwibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF9hZG1pbiI6ImI4MjlmZGZiNWIwNzU0ODhlZGQxZjQ5YmFkMzUzNzFiNWExNTllNzhjYzEzMGQ2OGNlZWQ0YjY0NTZlM2VjNjkiLCJ1cmwiOnsiaW50ZW5kZWQiOiJodHRwczpcL1wvcGxhaW4ubWVyY2F0b3JpYS5pZFwva2F0YWxvZyJ9fQ==', 1790592470),
('r3W2IbSmWBgnPN4nid7KUwySPp6vFAIyl79LthWJ', NULL, '2a02:4780:6:c0de::8', 'Go-http-client/2.0', 'eyJfdG9rZW4iOiJ4WG5TWUZqUmNYSnVVeFJZcDNDZ1hHZWh3eFhveEhaR2gyTFRmZ1d5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9wbGFpbi5tZXJjYXRvcmlhLmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1790582039),
('REVGhuNfg0Hf3Aok9h1P7UixrvxKsFRqPvzkcMAm', NULL, '182.253.48.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0', 'eyJfdG9rZW4iOiJmQ3JkZnV1b3BCZWlUcG9WZm91aXlWYjB5akhvZDFUOHFxU1o5a2lUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9wbGFpbi5tZXJjYXRvcmlhLmlkIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1790578389);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('best_seller_limit', '8', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('best_seller_min_sales', '1', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('best_seller_period_days', '365', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('birthday_coin', '1000', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('birthday_voucher_min', '100000', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('birthday_voucher_nominal', '20000', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('birthday_voucher_valid_days', '3', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('birthday_voucher_window_days', '3', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('btm_jkt_rate_per_kg', '60000', '2026-09-24 10:28:09', '2026-09-26 07:39:28'),
('cart_reminder_1_hours', '4', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('cart_reminder_2_hours', '24', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('cn_id_rate_per_kg', '25000', '2026-09-24 10:28:09', '2026-09-26 07:39:28'),
('coin_earn_percent', '1', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('coin_expiry_months', '12', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('coin_expiry_reminder_days', '7', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('coin_max_use_percent', '5', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('contact_whatsapp', '+6281219683709', '2026-09-28 08:33:29', '2026-09-28 08:33:29'),
('customer_activity_retention_months', '12', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('customer_bonus_coin', '1000', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('dp_percent', '50', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('email_verification_enabled', '1', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('exchange_rate', '2800', '2026-09-24 10:28:09', '2026-09-26 07:39:28'),
('footer_copyright', '© 2022 MERCATORIA', '2026-09-28 06:39:23', '2026-09-28 06:40:52'),
('footer_powered_by', 'Powered by MERCATORIA', '2026-09-28 06:39:23', '2026-09-28 06:39:23'),
('footer_shopee_url', 'https://shopee.co.id/mercatoria', '2026-09-28 06:39:23', '2026-09-28 06:40:52'),
('footer_tiktok_shop_url', '', '2026-09-28 10:39:41', '2026-09-28 10:39:41'),
('footer_toco_url', 'https://toco.id/store/mercatoria', '2026-09-28 06:39:23', '2026-09-28 06:40:52'),
('footer_tokopedia_url', '', '2026-09-28 10:39:41', '2026-09-28 10:39:41'),
('maintenance_bypass_ips', '', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('maintenance_enabled', '0', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('maintenance_message', 'Kami sedang melakukan pemeliharaan. Coba lagi sebentar lagi.', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('margin_percent', '11', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('payment_deadline_hours', '24', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('price_rounding', '5000', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('promo_bar_text', 'Gunakan kode promo NEWWEB dan nikmati potongan harga sebesar Rp 25.000 dengan minimal pembelanjaan Rp 150.000.', '2026-09-24 10:28:09', '2026-09-26 07:40:30'),
('referral_reward_referee', '2000', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('referral_reward_referrer', '5000', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('registration_limit', '3', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('social_facebook', 'https://facebook.com/mercatoria.id', '2026-09-28 08:33:29', '2026-09-28 08:33:29'),
('social_instagram', 'https://instagram.com/mercatoria_id', '2026-09-28 08:33:29', '2026-09-28 08:33:29'),
('social_thread', 'https://www.threads.com/@mercatoria_id', '2026-09-28 08:33:29', '2026-09-28 08:33:29'),
('social_x', 'https://x.com/mercatoria_id2', '2026-09-28 08:33:29', '2026-09-28 08:33:29'),
('spammer_ttl_days', '30', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('view_quota', '10', '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
('wa_template_completed', 'Halo {nama}, pesanan #{order_number} sudah selesai. Terima kasih sudah berbelanja di MERCATORIA! 🎉', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_template_payment_received', 'Halo {nama}, pembayaran pesanan #{order_number} sudah kami terima. Terima kasih! Pesanan segera diproses.', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_template_payment_reminder', 'Halo {nama}, pesanan #{order_number} masih menunggu pembayaran. Batas waktu {deadline}. Yuk selesaikan ya 🙏', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_template_processing', 'Halo {nama}, pesanan #{order_number} sedang kami proses. Update selanjutnya akan kami infokan ya.', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_template_shipped', 'Halo {nama}, pesanan #{order_number} sudah dalam perjalanan ke Indonesia. Estimasi tiba ~45 hari setelah keluar dari gudang China.', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_widget_enabled', '1', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_widget_greeting', 'Halo, saya mau tanya tentang produk di MERCATORIA.', '2026-09-26 16:59:25', '2026-09-26 16:59:25'),
('wa_widget_label', 'Chat CS', '2026-09-26 16:59:25', '2026-09-26 16:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_tiers`
--

CREATE TABLE `shipping_tiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `fee_yuan` decimal(12,2) NOT NULL,
  `min_purchase_yuan` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_tiers`
--

INSERT INTO `shipping_tiers` (`id`, `code`, `fee_yuan`, `min_purchase_yuan`, `created_at`, `updated_at`) VALUES
(1, 'T8', 0.00, 0.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(2, 'T7', 10.00, 50.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(3, 'T6', 8.00, 60.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(4, 'T5', 5.00, 90.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(5, 'T4', 7.00, 100.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(6, 'T3', 10.00, 100.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(7, 'T2', 7.00, 200.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(8, 'T1', 8.00, 200.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09'),
(9, 'T0', 10.00, 200.00, '2026-09-24 10:28:09', '2026-09-24 10:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `email_otp` varchar(6) DEFAULT NULL,
  `email_otp_expires_at` timestamp NULL DEFAULT NULL,
  `email_otp_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `password` varchar(255) DEFAULT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'spammer',
  `view_quota_used` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `parental_consent` tinyint(1) NOT NULL DEFAULT 0,
  `tnc_accepted_at` timestamp NOT NULL,
  `privacy_accepted_at` timestamp NOT NULL,
  `registered_at` timestamp NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `became_customer_at` timestamp NULL DEFAULT NULL,
  `anonymized_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `birth_date`, `province`, `city`, `district`, `postal_code`, `street_address`, `whatsapp`, `email`, `email_verified_at`, `email_otp`, `email_otp_expires_at`, `email_otp_attempts`, `password`, `referral_code`, `role`, `view_quota_used`, `parental_consent`, `tnc_accepted_at`, `privacy_accepted_at`, `registered_at`, `expires_at`, `became_customer_at`, `anonymized_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'First Kao', '1996-11-04', 'DKI Jakarta', 'Jakarta', 'Jakarta', '11111', 'Jalan Kenangan', '628159792353', 'firstkao4@gmail.com', NULL, NULL, NULL, 0, '$2y$12$PXa1vaMJ5vgv4m.kZsvj.eI4/7SM3vHlcWg5F0nIR3cxBMl9hVuSW', NULL, 'spammer', 3, 0, '2026-09-24 13:13:09', '2026-09-24 13:13:09', '2026-09-24 13:13:09', '2026-10-24 13:13:09', NULL, NULL, NULL, '2026-09-24 13:13:09', '2026-09-25 12:39:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(30) NOT NULL DEFAULT 'bell',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `discount_type` varchar(10) NOT NULL,
  `value` bigint(20) UNSIGNED NOT NULL,
  `max_discount_idr` bigint(20) UNSIGNED DEFAULT NULL,
  `min_purchase_idr` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `is_birthday` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `per_user_limit` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_personal` tinyint(1) NOT NULL DEFAULT 0,
  `auto_type` varchar(30) DEFAULT NULL,
  `auto_year` smallint(5) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voucher_redemptions`
--

CREATE TABLE `voucher_redemptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'used',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `activity_logs_created_at_index` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_logs_admin_id_foreign` (`admin_id`),
  ADD KEY `admin_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `admin_logs_created_at_index` (`created_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notifications_order_id_foreign` (`order_id`),
  ADD KEY `admin_notifications_read_at_created_at_index` (`read_at`,`created_at`);

--
-- Indexes for table `appeals`
--
ALTER TABLE `appeals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appeals_user_id_status_index` (`user_id`,`status`),
  ADD KEY `appeals_identity_record_id_index` (`identity_record_id`),
  ADD KEY `appeals_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_user_id_product_variant_id_unique` (`user_id`,`product_variant_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `cart_reminders`
--
ALTER TABLE `cart_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_reminders_user_id_sent_at_index` (`user_id`,`sent_at`);

--
-- Indexes for table `coin_lots`
--
ALTER TABLE `coin_lots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coin_lots_user_id_birthday_year_unique` (`user_id`,`birthday_year`),
  ADD UNIQUE KEY `coin_lots_order_id_source_unique` (`order_id`,`source`),
  ADD KEY `coin_lots_user_id_expires_at_index` (`user_id`,`expires_at`);

--
-- Indexes for table `coin_spends`
--
ALTER TABLE `coin_spends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coin_spends_coin_lot_id_foreign` (`coin_lot_id`),
  ADD KEY `coin_spends_order_id_foreign` (`order_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_messages_user_id_foreign` (`user_id`),
  ADD KEY `contact_messages_read_at_index` (`read_at`),
  ADD KEY `contact_messages_created_at_index` (`created_at`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `developers_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `games_slug_unique` (`slug`),
  ADD KEY `games_developer_id_foreign` (`developer_id`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `identity_records`
--
ALTER TABLE `identity_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identity_records_kind_value_unique` (`kind`,`value`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketplaces`
--
ALTER TABLE `marketplaces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `marketplaces_name_unique` (`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `name_blacklist`
--
ALTER TABLE `name_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_blacklist_name_normalized_unique` (`name_normalized`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_marketplace_id_foreign` (`marketplace_id`),
  ADD KEY `orders_status_payment_deadline_at_index` (`status`,`payment_deadline_at`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_addresses_order_id_unique` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `order_items_order_id_index` (`order_id`);

--
-- Indexes for table `order_notes`
--
ALTER TABLE `order_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_notes_admin_id_foreign` (`admin_id`),
  ADD KEY `order_notes_order_id_created_at_index` (`order_id`,`created_at`),
  ADD KEY `order_notes_order_id_is_pinned_index` (`order_id`,`is_pinned`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_status_history_order_id_foreign` (`order_id`),
  ADD KEY `order_status_history_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_proofs_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `payment_proofs_status_uploaded_at_index` (`status`,`uploaded_at`),
  ADD KEY `payment_proofs_order_status_index` (`order_id`,`status`);

--
-- Indexes for table `preorder_banners`
--
ALTER TABLE `preorder_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `preorder_banners_preorder_page_id_is_active_sort_order_index` (`preorder_page_id`,`is_active`,`sort_order`);

--
-- Indexes for table `preorder_pages`
--
ALTER TABLE `preorder_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `preorder_pages_slug_unique` (`slug`),
  ADD KEY `preorder_pages_is_active_index` (`is_active`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_game_id_foreign` (`game_id`),
  ADD KEY `products_developer_id_foreign` (`developer_id`),
  ADD KEY `products_shipping_tier_id_foreign` (`shipping_tier_id`),
  ADD KEY `products_is_published_created_at_index` (`is_published`,`created_at`),
  ADD KEY `products_best_seller_rank_is_published_index` (`best_seller_rank`,`is_published`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_product_id_index` (`product_id`);

--
-- Indexes for table `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_views_user_id_foreign` (`user_id`),
  ADD KEY `product_views_product_id_foreign` (`product_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referrals_referee_id_unique` (`referee_id`),
  ADD KEY `referrals_referrer_id_status_index` (`referrer_id`,`status`),
  ADD KEY `referrals_status_index` (`status`);

--
-- Indexes for table `registration_events`
--
ALTER TABLE `registration_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_events_identity_record_id_foreign` (`identity_record_id`);

--
-- Indexes for table `reseller_applications`
--
ALTER TABLE `reseller_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reseller_applications_user_id_foreign` (`user_id`),
  ADD KEY `reseller_applications_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `shipping_tiers`
--
ALTER TABLE `shipping_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipping_tiers_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_whatsapp_unique` (`whatsapp`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD KEY `users_role_expires_at_index` (`role`,`expires_at`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  ADD KEY `user_notifications_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_code_unique` (`code`),
  ADD KEY `vouchers_user_id_foreign` (`user_id`);

--
-- Indexes for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_redemptions_order_id_unique` (`order_id`),
  ADD KEY `voucher_redemptions_voucher_id_foreign` (`voucher_id`),
  ADD KEY `voucher_redemptions_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appeals`
--
ALTER TABLE `appeals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_reminders`
--
ALTER TABLE `cart_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coin_lots`
--
ALTER TABLE `coin_lots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coin_spends`
--
ALTER TABLE `coin_spends`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `identity_records`
--
ALTER TABLE `identity_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketplaces`
--
ALTER TABLE `marketplaces`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `name_blacklist`
--
ALTER TABLE `name_blacklist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_notes`
--
ALTER TABLE `order_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preorder_banners`
--
ALTER TABLE `preorder_banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preorder_pages`
--
ALTER TABLE `preorder_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_events`
--
ALTER TABLE `registration_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reseller_applications`
--
ALTER TABLE `reseller_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_tiers`
--
ALTER TABLE `shipping_tiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appeals`
--
ALTER TABLE `appeals`
  ADD CONSTRAINT `appeals_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appeals_identity_record_id_foreign` FOREIGN KEY (`identity_record_id`) REFERENCES `identity_records` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appeals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_reminders`
--
ALTER TABLE `cart_reminders`
  ADD CONSTRAINT `cart_reminders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coin_lots`
--
ALTER TABLE `coin_lots`
  ADD CONSTRAINT `coin_lots_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coin_lots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coin_spends`
--
ALTER TABLE `coin_spends`
  ADD CONSTRAINT `coin_spends_coin_lot_id_foreign` FOREIGN KEY (`coin_lot_id`) REFERENCES `coin_lots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coin_spends_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_developer_id_foreign` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_marketplace_id_foreign` FOREIGN KEY (`marketplace_id`) REFERENCES `marketplaces` (`id`),
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD CONSTRAINT `order_addresses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_notes`
--
ALTER TABLE `order_notes`
  ADD CONSTRAINT `order_notes_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_notes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `payment_proofs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `preorder_banners`
--
ALTER TABLE `preorder_banners`
  ADD CONSTRAINT `preorder_banners_preorder_page_id_foreign` FOREIGN KEY (`preorder_page_id`) REFERENCES `preorder_pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_developer_id_foreign` FOREIGN KEY (`developer_id`) REFERENCES `developers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_game_id_foreign` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_shipping_tier_id_foreign` FOREIGN KEY (`shipping_tier_id`) REFERENCES `shipping_tiers` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_views`
--
ALTER TABLE `product_views`
  ADD CONSTRAINT `product_views_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_referee_id_foreign` FOREIGN KEY (`referee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registration_events`
--
ALTER TABLE `registration_events`
  ADD CONSTRAINT `registration_events_identity_record_id_foreign` FOREIGN KEY (`identity_record_id`) REFERENCES `identity_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reseller_applications`
--
ALTER TABLE `reseller_applications`
  ADD CONSTRAINT `reseller_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  ADD CONSTRAINT `voucher_redemptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voucher_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voucher_redemptions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
