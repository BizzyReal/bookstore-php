-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Apr 2026 pada 05.53
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `category_id`, `title`, `author`, `description`, `price`, `stock`, `cover_image`, `created_at`) VALUES
(1, 17, 'Laskar Pelangi', 'Andrea Hirata', 'Kisah inspiratif tentang perjuangan anak-anak Belitung', '85000.00', 1, '1775788019_69d85ff3ae84a.jpg', '2026-04-07 10:30:57'),
(2, 17, 'Sapiens', 'Yuval Noah Harari', 'Sejarah singkat umat manusia', '120000.00', 2, '1775788094_69d8603ec6f83.jpg', '2026-04-07 10:30:57'),
(3, 4, 'Clean Code', 'Robert C. Martin', 'Panduan menulis kode yang bersih dan terstruktur', '150000.00', 4, '1775788142_69d8606e53e85.jpg', '2026-04-07 10:30:57'),
(4, 8, 'hujan', 'tere liye', 'buku terbagus', '120000.00', 9, '1775787988_69d85fd4cca4f.jpg', '2026-04-08 10:56:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(3, 'Pendidikan', '2026-04-07 10:30:57'),
(4, 'Teknologi', '2026-04-07 10:30:57'),
(7, 'Komik', '2026-04-08 01:48:11'),
(8, 'Novel', '2026-04-08 01:48:11'),
(9, 'Sejarah', '2026-04-08 01:48:11'),
(11, 'Sains', '2026-04-08 01:48:11'),
(13, 'Kesehatan', '2026-04-08 01:48:11'),
(15, 'Agama', '2026-04-08 01:48:11'),
(16, 'Bisnis', '2026-04-09 18:28:59'),
(17, 'Self Improvement', '2026-04-09 18:28:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT 1,
  `last_message` text DEFAULT NULL,
  `last_message_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `admin_id`, `last_message`, `last_message_time`, `created_at`) VALUES
(1, 7, 1, 'iya kenapa kak', '2026-04-09 17:20:57', '2026-04-09 16:52:56'),
(2, 8, 1, 'oke makasih', '2026-04-09 19:12:57', '2026-04-09 18:00:22'),
(3, 9, 1, '2 hari biasanya kak kalau ke papua', '2026-04-09 20:04:11', '2026-04-09 20:03:19'),
(4, 10, 1, 'iyaa benar saya mau cod sesuai dengan alamat yang saya berikan', '2026-04-10 03:34:59', '2026-04-10 00:41:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 7, 1, 'halo min', 1, '2026-04-09 16:53:07'),
(2, 1, 1, 7, 'iya kenapa kak', 1, '2026-04-09 16:53:35'),
(3, 1, 1, 7, 'iya kenapa kak', 1, '2026-04-09 17:20:57'),
(4, 2, 1, 8, 'halo kak buku sedang dikirim ya', 1, '2026-04-09 18:00:57'),
(5, 2, 8, 1, 'oke makasih', 1, '2026-04-09 19:12:57'),
(6, 3, 9, 1, 'kira kira kapan sampe min?', 1, '2026-04-09 20:03:29'),
(7, 3, 1, 9, '2 hari biasanya kak kalau ke papua', 1, '2026-04-09 20:04:11'),
(8, 4, 10, 1, 'haii', 1, '2026-04-10 00:41:29'),
(9, 4, 1, 10, 'apakah benar mau cod?', 1, '2026-04-10 00:42:26'),
(10, 4, 10, 1, 'iyaa benar saya mau cod sesuai dengan alamat yang saya berikan', 0, '2026-04-10 03:34:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','delivered','cancelled') DEFAULT 'pending',
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `order_type` enum('online','offline') NOT NULL DEFAULT 'online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_amount`, `payment_method`, `payment_status`, `payment_proof`, `status`, `address`, `notes`, `order_type`) VALUES
(1, 2, '2026-04-07 11:48:08', '205000.00', 'cod', 'pending', NULL, 'pending', 'ada deh', '', 'online'),
(2, 2, '2026-04-07 22:32:29', '85000.00', 'cod', 'pending', NULL, 'pending', '', NULL, 'online'),
(3, 2, '2026-04-07 22:41:59', '85000.00', 'cod', 'pending', NULL, 'delivered', '', NULL, 'offline'),
(4, 3, '2026-04-08 04:37:30', '170000.00', 'cod', 'pending', NULL, 'processing', 'di sana', 'iya', 'online'),
(5, 4, '2026-04-08 10:06:42', '355000.00', 'cod', 'pending', NULL, 'delivered', 'di rumah', 'cepetan ya', 'online'),
(6, 5, '2026-04-08 10:09:03', '85000.00', 'cod', 'pending', NULL, 'delivered', '', NULL, 'offline'),
(7, 6, '2026-04-08 10:48:58', '120000.00', 'cod', 'pending', NULL, 'delivered', 'jalan kalimalang', 'cepet', 'online'),
(8, 7, '2026-04-09 17:32:17', '85000.00', 'cod', 'pending', NULL, 'pending', 'disitu', 'iyaa', 'online'),
(9, 8, '2026-04-09 17:44:30', '85000.00', 'transfer', 'paid', 'uploads/payments/1775756670_69d7e57e200e7.jpeg', 'processing', 'biasa', 'betul', 'online'),
(10, 9, '2026-04-09 20:01:04', '150000.00', 'transfer', 'paid', 'uploads/payments/1775764864_69d80580a387e.jpeg', 'processing', 'rumah', 'yang penting aman', 'online'),
(11, 10, '2026-04-10 00:40:07', '150000.00', 'cod', 'pending', NULL, 'delivered', '', NULL, 'offline'),
(12, 11, '2026-04-10 02:56:34', '135000.00', 'cod', 'pending', NULL, 'pending', 'contoh', 'contoh', 'online');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, '85000.00'),
(2, 1, 2, 1, '120000.00'),
(3, 2, 1, 1, '85000.00'),
(4, 3, 1, 1, '85000.00'),
(5, 4, 1, 2, '85000.00'),
(6, 5, 1, 1, '85000.00'),
(7, 5, 2, 1, '120000.00'),
(8, 5, 3, 1, '150000.00'),
(9, 6, 1, 1, '85000.00'),
(10, 7, 2, 1, '120000.00'),
(11, 8, 1, 1, '85000.00'),
(12, 9, 1, 1, '85000.00'),
(13, 10, 3, 1, '150000.00'),
(14, 11, 3, 1, '150000.00'),
(15, 12, 4, 1, '120000.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `book_id`, `user_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(2, 4, 8, 5, 'serem', 'approved', '2026-04-09 18:36:03'),
(3, 3, 9, 4, 'endingnya gantung', 'pending', '2026-04-09 20:04:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_settings`
--

CREATE TABLE `shipping_settings` (
  `id` int(11) NOT NULL,
  `flat_rate` int(11) NOT NULL DEFAULT 15000,
  `free_shipping_min` int(11) NOT NULL DEFAULT 150000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shipping_settings`
--

INSERT INTO `shipping_settings` (`id`, `flat_rate`, `free_shipping_min`) VALUES
(1, 15000, 150000),
(2, 15000, 150000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@bookstore.com', '$2a$12$Fqyn/1KqXjDwUExa6yEhD.t8iPTqX6ZsFhMhkikUVOMFVtKM/rsvy', 'admin', '2026-04-07 10:30:57'),
(2, 'Bizzy', 'bizzy@gmail.com', '$2y$10$63fRYpX/q1CMgJydA1WKlO/4y8ZuFcC9wg17fEPPiNHv3q5syBUXy', 'user', '2026-04-07 11:46:46'),
(3, 'yoi', 'yoi@gmail.com', '$2y$10$8oCuWOHTKkuPTgtbmF8G0OvQz18BODjGJD6VfrYnLIBAkNG6RqWc2', 'user', '2026-04-08 02:40:16'),
(4, 'epan', 'epan@gmail.com', '$2y$10$XdNjVv2tDKKqC1NPvtmfeeCjtqB67KUAXIDr5W36chryC1pN6Cuc2', 'user', '2026-04-08 10:04:50'),
(5, 'epan 2', 'contoh@gmail.com', '$2y$10$2FxioYL63x008xxV/nAbcOT7w3qbS.ePnG5Ue0VGrEW.giIcqgbVW', 'user', '2026-04-08 10:08:42'),
(6, 'celo', 'celo@gmail.com', '$2y$10$KN0hFP/4xKG4pRRQqAEVeOhrNaF2u9ME7D0gp9kRwbBCSpi4cVO8K', 'user', '2026-04-08 10:47:51'),
(7, 'iya', 'iya@gmail.com', '$2y$10$PUNqfCtBKBPxSctvm83EfOLEHKzji18eyEtMimqCuQbDXE5Nz7CE.', 'user', '2026-04-09 16:47:30'),
(8, 'aku', 'aku@gmail.com', '$2y$10$4OcOPd8s4V6hYemUiYNRDei/4VV00sGsK1cwb6vy9i9FaGcYMISJ2', 'user', '2026-04-09 17:34:26'),
(9, 'kita', 'kita@gmail.com', '$2y$10$n0Y55RPHNhvPh9Lz0mZ5lObC1RAaD9m1IWXHDOmN5vLndV9T3Ewn6', 'user', '2026-04-09 19:50:18'),
(10, 'randu', 'randu@gmail.com', '$2y$10$IA9Drk65zPnBcfFfFhk1nuB2p31ZnQd3z9RlPrd1WoJP7Z5b5vfI6', 'user', '2026-04-10 00:29:55'),
(11, 'user', 'user@gmail.com', '$2y$10$x7zXfESQwDoo1tKvKic7E.WB86z/zPbkAJzegDqBnioMx2/oQ.Jty', 'user', '2026-04-10 02:53:44');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_admin` (`user_id`,`admin_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `shipping_settings`
--
ALTER TABLE `shipping_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `shipping_settings`
--
ALTER TABLE `shipping_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
