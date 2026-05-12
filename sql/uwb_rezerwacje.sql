-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Maj 12, 2026 at 06:51 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uwb_rezerwacje`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_at` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `status` enum('PLANOWANE','ZAMKNIĘTE','ANULOWANE') NOT NULL DEFAULT 'PLANOWANE',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `start_at`, `duration_minutes`, `total_seats`, `status`, `created_by`, `created_at`) VALUES
(1, 'Wykład inauguracyjny', 'Uroczyste rozpoczęcie semestru.', '2026-05-21 10:00:00', 120, 90, 'PLANOWANE', 1, '2026-05-04 09:31:52'),
(2, 'Konferencja AI', 'Wydarzenie poświęcone sztucznej inteligencji.', '2026-05-22 09:00:00', 180, 80, 'PLANOWANE', 1, '2026-05-04 09:31:52'),
(3, 'Gala absolwentów', 'Ceremonia zakończenia roku.', '2026-05-25 17:00:00', 150, 150, 'PLANOWANE', 1, '2026-05-04 09:31:52'),
(4, 'Dzień otwarty', 'Prezentacja uczelni dla kandydatów.', '2026-05-11 11:00:00', 240, 100, 'ZAMKNIĘTE', 1, '2026-05-04 09:31:52'),
(5, 'Spotkanie organizacyjne', 'Wewnętrzne spotkanie koordynacyjne.', '2026-05-01 08:00:00', 60, 40, 'ANULOWANE', 1, '2026-05-04 09:31:52'),
(22, 'Międzynarodowa Interdyscyplinarna Studencko-Doktorancka Konferencja Naukowa pt. MŁODE POGRANICZE EUROPY: ŚRODOWISKO, SPOŁECZEŃSTWO, GOSPODARKA', 'Celem konferencji jest stworzenie przestrzeni spotkania młodych badaczy zainteresowanych problematyką pogranicza Europy oraz wymiany pomysłów, doświadczeń i wyników badań dotyczących wyzwań środowiskowych, społecznych i gospodarczych współczesnej Europy. Wydarzenie ma wspierać rozwój naukowy studentów i doktorantów oraz inspirować do podejmowania wspólnych inicjatyw badawczych.', '2026-05-15 22:30:00', 24, 90, 'PLANOWANE', 1, '2026-05-12 16:37:48');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `occupied_seats`
--

CREATE TABLE `occupied_seats` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `seat_number` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('AKTYWNA','ANULOWANA') NOT NULL DEFAULT 'AKTYWNA',
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `occupied_seats`
--

INSERT INTO `occupied_seats` (`id`, `event_id`, `seat_number`, `user_id`, `status`, `reserved_at`) VALUES
(44, 3, 3, 1, 'AKTYWNA', '2026-05-09 14:12:35'),
(67, 2, 36, 2, 'AKTYWNA', '2026-05-09 14:48:15'),
(68, 2, 37, 2, 'AKTYWNA', '2026-05-09 14:48:15'),
(69, 2, 53, 2, 'AKTYWNA', '2026-05-09 14:48:15'),
(70, 2, 54, 2, 'AKTYWNA', '2026-05-09 14:48:15'),
(71, 2, 55, 2, 'AKTYWNA', '2026-05-09 14:48:15'),
(72, 3, 38, 2, 'AKTYWNA', '2026-05-09 14:48:19'),
(73, 3, 55, 2, 'AKTYWNA', '2026-05-09 14:48:19'),
(74, 3, 72, 2, 'AKTYWNA', '2026-05-09 14:48:19'),
(113, 1, 19, 5, 'AKTYWNA', '2026-05-10 18:58:47'),
(218, 1, 1, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(219, 1, 2, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(220, 1, 3, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(221, 1, 4, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(222, 1, 8, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(223, 1, 10, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(224, 1, 11, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(225, 1, 12, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(226, 1, 13, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(227, 1, 14, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(228, 1, 15, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(229, 1, 17, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(230, 1, 18, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(231, 1, 23, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(232, 1, 24, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(233, 1, 25, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(234, 1, 26, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(235, 1, 27, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(236, 1, 30, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(237, 1, 31, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(238, 1, 35, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(239, 1, 36, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(240, 1, 40, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(241, 1, 41, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(242, 1, 42, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(243, 1, 44, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(244, 1, 45, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(245, 1, 47, 3, 'AKTYWNA', '2026-05-12 08:29:11'),
(262, 1, 7, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(263, 1, 20, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(264, 1, 21, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(265, 1, 37, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(266, 1, 38, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(267, 1, 55, 2, 'AKTYWNA', '2026-05-12 16:30:26'),
(271, 1, 5, 1, 'AKTYWNA', '2026-05-12 16:31:21'),
(272, 1, 6, 1, 'AKTYWNA', '2026-05-12 16:31:21'),
(273, 1, 53, 1, 'AKTYWNA', '2026-05-12 16:31:21'),
(274, 1, 54, 1, 'AKTYWNA', '2026-05-12 16:31:21');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'ADMINISTRATOR'),
(3, 'GOŚĆ'),
(2, 'UŻYTKOWNIK');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `password_hash`, `created_at`, `last_activity`) VALUES
(1, 1, 'Admin', 'UWB', 'admin@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', '2026-05-12 19:49:24'),
(2, 2, 'Jan', 'Kowalski', 'user1@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', '2026-05-12 19:30:23'),
(3, 2, 'Anna', 'Nowak', 'user2@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(4, 2, 'Piotr', 'Wiśniewski', 'user3@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(5, 2, 'Kasia', 'Mazur', 'user4@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(6, 2, 'Marek', 'Lis', 'user5@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(7, 2, 'Ola', 'Wójcik', 'user6@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(8, 2, 'Tomek', 'Lewandowski', 'user7@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(9, 2, 'Ewa', 'Kamińska', 'user8@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(10, 2, 'Paweł', 'Zieliński', 'user9@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(11, 2, 'Monika', 'Sikora', 'user10@uwb.local', '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz12345', '2026-05-04 09:31:52', NULL),
(13, 1, 'Fabian', 'zaw', 'fz90275@test', '$2y$10$uu.kgprHN1yUUvhJbLjmKOKHOUmRl.ioVlsUCkwgaN4kWxsKArmFi', '2026-05-10 13:02:23', NULL);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_events_user` (`created_by`);

--
-- Indeksy dla tabeli `occupied_seats`
--
ALTER TABLE `occupied_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_seat` (`event_id`,`seat_number`),
  ADD KEY `fk_occupied_seats_user` (`user_id`);

--
-- Indeksy dla tabeli `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `occupied_seats`
--
ALTER TABLE `occupied_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=275;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `occupied_seats`
--
ALTER TABLE `occupied_seats`
  ADD CONSTRAINT `fk_occupied_seats_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `fk_occupied_seats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
