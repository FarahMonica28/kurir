-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 25, 2025 at 07:54 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lrvl_kurir`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kurir`
--

CREATE TABLE `kurir` (
  `kurir_id` bigint UNSIGNED NOT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kurir`
--

INSERT INTO `kurir` (`kurir_id`, `status`, `created_at`, `updated_at`, `rating`, `user_id`) VALUES
(24, 'nonaktif', NULL, NULL, 0, 18),
(25, 'aktif', NULL, NULL, NULL, 20);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_12_12_072543_create_permission_tables', 1),
(6, '2023_12_31_064553_create_settings_table', 1),
(7, '2025_03_25_045408_create_kurir_table', 2),
(8, '2025_03_27_030902_add_password_and_rating_to_kurir_table', 3),
(9, '2025_03_27_021656_create_pengirimans_table', 4),
(10, '2025_03_27_071304_create_pengiriman_table', 5),
(11, '2025_04_10_084723_create_tracking_logs_table', 6),
(12, '2025_04_11_020549_add_no_resi_to_pengiriman_table', 7),
(13, '2025_04_15_044827_create_pengirimans_table', 8),
(14, '2025_04_15_045255_add_masalah_to_pengirimans_table', 9),
(15, '2025_04_17_033210_create_transaksi_table', 10),
(16, '2025_04_23_102831_create_pengguna_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', '1'),
(2, 'App\\Models\\User', '18'),
(3, 'App\\Models\\User', '19'),
(2, 'App\\Models\\User', '20'),
(3, 'App\\Models\\User', '21'),
(3, 'App\\Models\\User', '22');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`pengguna_id`, `user_id`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 21, 'alamat', '2025-04-23 04:15:06', '2025-04-23 04:15:06'),
(2, 22, 'alamat', '2025-04-25 01:29:32', '2025-04-25 01:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id` bigint UNSIGNED NOT NULL,
  `kurir_id` bigint UNSIGNED NOT NULL,
  `paket` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('dikemas','dikirim','diterima') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dikemas',
  `Tanggal_dibuat` date DEFAULT NULL,
  `tanggal_pengiriman` date DEFAULT NULL,
  `tanggal_penerimaan` date DEFAULT NULL,
  `penerima` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `biaya` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `no_resi` varchar(123) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengiriman`
--

INSERT INTO `pengiriman` (`id`, `kurir_id`, `paket`, `status`, `Tanggal_dibuat`, `tanggal_pengiriman`, `tanggal_penerimaan`, `penerima`, `alamat`, `biaya`, `created_at`, `updated_at`, `no_resi`) VALUES
(14, 24, 'buku', 'dikirim', '2025-04-07', '2025-04-09', '2025-04-13', 'ica', 'GCA', 20000, NULL, NULL, 'ASDF123'),
(15, 24, 'tas', 'dikemas', '2025-04-08', '2025-04-09', '2025-04-16', 'grizel', 'kebraon', 20000, NULL, NULL, 'ABC123');

-- --------------------------------------------------------

--
-- Table structure for table `pengirimans`
--

CREATE TABLE `pengirimans` (
  `id` bigint UNSIGNED NOT NULL,
  `no_resi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penerima` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_tujuan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('belum_diambil','diambil','sedang_dikirim','terkirim','gagal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_diambil',
  `masalah` text COLLATE utf8mb4_unicode_ci,
  `waktu_masalah` timestamp NULL DEFAULT NULL,
  `waktu_ambil` timestamp NULL DEFAULT NULL,
  `waktu_kirim` timestamp NULL DEFAULT NULL,
  `kurir_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengirimans`
--

INSERT INTO `pengirimans` (`id`, `no_resi`, `penerima`, `alamat_tujuan`, `status`, `masalah`, `waktu_masalah`, `waktu_ambil`, `waktu_kirim`, `kurir_id`, `created_at`, `updated_at`) VALUES
(1, '123456', 'farah', 'bringin', 'belum_diambil', NULL, NULL, NULL, NULL, 20, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(2, 'master', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(3, 'master-user', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(4, 'master-role', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(5, 'website', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(6, 'setting', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(7, 'kurir', 'api', NULL, NULL),
(9, 'akun', 'api', NULL, NULL),
(11, 'pengiriman', 'api', NULL, NULL),
(12, 'tracking', 'api', '2025-04-10 08:28:36', '2025-04-10 08:28:36'),
(13, 'dashboardk', 'api', NULL, NULL),
(15, 'pengirimans', 'api', NULL, NULL),
(16, 'rute', 'api', NULL, NULL),
(17, 'transaksi', 'api', NULL, NULL),
(18, 'pengguna', 'api', NULL, NULL),
(19, 'trans', 'api', NULL, NULL),
(20, 'riwayat', 'api', NULL, NULL),
(21, 'riwayatt', 'api', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `full_name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator', 'api', '2025-03-24 20:33:43', '2025-03-24 20:33:43'),
(2, 'kurir', 'kurir', 'api', '2025-03-26 00:02:56', '2025-04-09 00:10:45'),
(3, 'pengguna', 'pengguna', 'api', '2025-04-08 00:20:53', '2025-04-08 00:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(11, 1),
(15, 1),
(18, 1),
(1, 2),
(5, 2),
(9, 2),
(19, 2),
(20, 2),
(13, 3),
(17, 3),
(21, 3);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `app` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bg_auth` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dinas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pemerintah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `uuid`, `app`, `description`, `logo`, `banner`, `bg_auth`, `dinas`, `pemerintah`, `alamat`, `telepon`, `email`, `created_at`, `updated_at`) VALUES
(1, '3de3faaf-a8f5-4e12-9fb6-9338ab6c29d6', 'e-SAKIP DLH', 'Aplikasi e-SAKIP Dinas Lingkungan Hidup', '/storage/setting/IbGq9uFOSpfWCNKPooHFJLDLlP4Q1PviAXS2K9S0.jpg', '/media/misc/banner.jpg', '/storage/setting/iyaxKHeEFEk6IouQIdZHcKjs1LKOhvg2Mk2GdlCz.jpg', 'Dinas Lingkungan Hidup', 'Pemerintah Provinsi Jawa Timur', 'Farah', '082229545032', 'frhmph.2807@gmail.com', '2025-03-24 20:33:46', '2025-03-25 21:17:14');

-- --------------------------------------------------------

--
-- Table structure for table `tracking_log`
--

CREATE TABLE `tracking_log` (
  `id` bigint UNSIGNED NOT NULL,
  `pengiriman_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `waktu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` bigint UNSIGNED NOT NULL,
  `pengirim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penerima` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_penerima` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_asal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alamat_tujuan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nama_barang` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `berat_barang` int DEFAULT NULL,
  `biaya` int DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `waktu_penjemputan` datetime DEFAULT NULL,
  `waktu_proses` datetime DEFAULT NULL,
  `waktu_terkirim` datetime DEFAULT NULL,
  `status` enum('Belum Terkirim','Penjemputan Barang','Sedang Dikirim','Terkirim') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penilaian` int DEFAULT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `kurir_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `pengirim`, `penerima`, `no_hp_penerima`, `alamat_asal`, `alamat_tujuan`, `nama_barang`, `berat_barang`, `biaya`, `waktu`, `waktu_penjemputan`, `waktu_proses`, `waktu_terkirim`, `status`, `penilaian`, `komentar`, `kurir_id`, `created_at`, `updated_at`) VALUES
(15, 'fara', 'grizel', '081231365656', 'bringin', 'kebraon', 'tas', 1, 10000, '2025-04-24 08:24:29', '2025-04-24 08:31:49', '2025-04-24 08:45:22', '2025-04-24 08:45:53', 'Terkirim', 90, NULL, NULL, NULL, NULL),
(16, 'grizel', 'grizelda', '09876543', 'tandes', 'kebraon', 'buku', 1, 10000, '2025-04-24 09:08:25', '2025-04-24 09:10:14', '2025-04-24 09:30:48', '2025-04-24 09:31:20', 'Terkirim', 80, 'dfgsdfxg', NULL, NULL, NULL),
(17, 'grizel', 'sarah', '09876543', 'tandes', 'bringin', 'buku', 8, 80000, '2025-04-24 09:44:13', '2025-04-24 09:51:20', '2025-04-24 09:51:35', '2025-04-24 09:51:41', 'Terkirim', 90, 'baik', NULL, NULL, NULL),
(18, 'grizel', 'grizelda', '081231365656', 'sawo', 'bringin', 'tas', 2, 20000, '2025-04-24 10:21:10', '2025-04-24 10:25:16', '2025-04-24 14:54:22', NULL, 'Sedang Dikirim', NULL, NULL, NULL, NULL, NULL),
(22, 'indah', 'farah', '09876543', 'rajawali', 'bringin', 'buku', 5, 50000, '2025-04-25 13:12:01', '2025-04-25 13:24:43', '2025-04-25 13:28:22', NULL, 'Sedang Dikirim', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `phone`, `photo`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'bb3af772-0329-4f97-b9cb-c36c939c3192', 'Admin', 'admin@gmail.com', '08123456789', NULL, '$2y$12$jNjBsTQ7x81d1pJNAk4FRerP0XgZRBKsXCBDkx4fC.gx6THwPAvHO', NULL, '2025-03-24 20:33:46', '2025-03-24 20:33:46'),
(18, 'df822a89-ecf6-4462-92f6-a15138fc2816', 'farahmon', 'farah@gmail.com', '12345678', 'photo/MTVz3bNat8UQYeNjVHlwhpVLWlok3sV3IhUzfc9g.png', '$2y$12$S6HFd8PN/k.zqjhPqL0j5ePqvzbJVq/QJjDQ4Kb9n5TGBp7Jv8msi', NULL, '2025-04-08 21:16:01', '2025-04-24 04:11:09'),
(20, 'c1002a42-cd3b-4e4b-b954-42399c5a121f', 'ica', 'ica@gmail.com', '1234', 'photo/WdonI3q4OUauIXUz5vhfJwhxwFvlmmlEfgIC8LUB.jpg', '$2y$12$hhqAKmCgl5mSDgJUE/ElZ.7nwuN7REevXUiwTwzU3RAVy45hdSSa2', NULL, '2025-04-09 20:12:56', '2025-04-09 20:46:55'),
(21, '3adce087-56b3-4590-8717-7926540a2891', 'Ega', 'egaa@gmail.com', '23423445565', 'photo/Vr3XS026E9qrhUffM9pGhxQ8tICfaFHU3Mbo5EEd.png', '$2y$12$96nRxDgA9ZwidOUxLvr9kuBpIu7YQ.oxgpW8//7xICLkwb5a.DsOO', NULL, '2025-04-23 04:15:05', '2025-04-23 04:29:01'),
(22, '4f66de1e-ea85-4846-b8fa-5626dc934c8c', 'farah', 'farahmon@gmail.com', '09876543', 'photo/QmKKPUOSuEOYPYn2rNS7sGX05kZP4N0W7xz2Fmjt.jpg', '$2y$12$Rz0Oh6uNaaDFKS2VgowwW.WeCSmu0x0ALCwU0G23BPlf4bZMt5M1S', NULL, '2025-04-25 01:29:31', '2025-04-25 01:29:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `kurir`
--
ALTER TABLE `kurir`
  ADD PRIMARY KEY (`kurir_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`pengguna_id`),
  ADD KEY `user` (`user_id`);

--
-- Indexes for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_resi` (`no_resi`),
  ADD KEY `pengiriman_kurir_id_foreign` (`kurir_id`);

--
-- Indexes for table `pengirimans`
--
ALTER TABLE `pengirimans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengirimans_no_resi_unique` (`no_resi`),
  ADD KEY `pengirimans_kurir_id_foreign` (`kurir_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_uuid_unique` (`uuid`);

--
-- Indexes for table `tracking_log`
--
ALTER TABLE `tracking_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tracking_log_pengiriman_id_foreign` (`pengiriman_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kurir_id` (`kurir_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kurir`
--
ALTER TABLE `kurir`
  MODIFY `kurir_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `pengguna_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pengirimans`
--
ALTER TABLE `pengirimans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tracking_log`
--
ALTER TABLE `tracking_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kurir`
--
ALTER TABLE `kurir`
  ADD CONSTRAINT `kurir` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_kurir_id_foreign` FOREIGN KEY (`kurir_id`) REFERENCES `kurir` (`kurir_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengirimans`
--
ALTER TABLE `pengirimans`
  ADD CONSTRAINT `pengirimans_kurir_id_foreign` FOREIGN KEY (`kurir_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tracking_log`
--
ALTER TABLE `tracking_log`
  ADD CONSTRAINT `tracking_log_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_kurir_id` FOREIGN KEY (`kurir_id`) REFERENCES `kurir` (`kurir_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
