-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Giu 25, 2025 alle 14:30
-- Versione del server: 8.0.36
-- Versione PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_testwebsitefkb`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `announcements`
--

CREATE TABLE `announcements` (
  `Id_announcement` int NOT NULL,
  `title` varchar(512) COLLATE utf8mb4_general_ci NOT NULL,
  `content` varchar(2048) COLLATE utf8mb4_general_ci NOT NULL,
  `pubblication_date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `banned_ipaddresses`
--

CREATE TABLE `banned_ipaddresses` (
  `Ip_addressHash` varchar(512) COLLATE utf8mb4_general_ci NOT NULL,
  `Id_banned` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `banned_users`
--

CREATE TABLE `banned_users` (
  `Id_ban` int NOT NULL,
  `Id_banned` int DEFAULT NULL,
  `reason` varchar(512) COLLATE utf8mb4_general_ci NOT NULL,
  `bannedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expireAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ban_appeals`
--

CREATE TABLE `ban_appeals` (
  `Id_banAppeal` int NOT NULL,
  `Id_user` int NOT NULL,
  `appealText` varchar(1024) COLLATE utf8mb4_general_ci NOT NULL,
  `appealDateTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `ban_reason` varchar(1024) COLLATE utf8mb4_general_ci NOT NULL,
  `actTaked` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `comments`
--

CREATE TABLE `comments` (
  `Id_comment` int NOT NULL,
  `Id_user` int NOT NULL,
  `Id_post` int NOT NULL,
  `contenuto` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `feedbacks`
--

CREATE TABLE `feedbacks` (
  `Id_feedback` int NOT NULL,
  `Id_user` int NOT NULL,
  `message` varchar(1024) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `followers`
--

CREATE TABLE `followers` (
  `Id_following` int NOT NULL,
  `Id_user` int NOT NULL,
  `followedAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `likes`
--

CREATE TABLE `likes` (
  `Id_post` int NOT NULL,
  `Id_user` int NOT NULL,
  `leftAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifications`
--

CREATE TABLE `notifications` (
  `Id_notification` int NOT NULL,
  `Id_user` int NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `content` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `readed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `posts`
--

CREATE TABLE `posts` (
  `Id_post` int NOT NULL,
  `Id_pubblisher` int NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attachedFile_path` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tags` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pubblication_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `isVideo` tinyint(1) DEFAULT NULL,
  `isPublic` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `reportedposts`
--

CREATE TABLE `reportedposts` (
  `Id_reporter` int NOT NULL,
  `Id_repPost` int NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `reportedusers`
--

CREATE TABLE `reportedusers` (
  `Id_reporter` int NOT NULL,
  `Id_repUs` int NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessions`
--

CREATE TABLE `sessions` (
  `Id_session` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `Id_user` int NOT NULL,
  `data_start` datetime DEFAULT NULL,
  `data_end` datetime DEFAULT NULL,
  `Ip_address` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `Id_user` int NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `surname` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(12) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Error',
  `email` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic_path` varchar(128) COLLATE utf8mb4_general_ci DEFAULT 'assets\\default\\default_user_icon.png',
  `description` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recover_question` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recover_answer` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`Id_announcement`);

--
-- Indici per le tabelle `banned_ipaddresses`
--
ALTER TABLE `banned_ipaddresses`
  ADD PRIMARY KEY (`Ip_addressHash`),
  ADD KEY `FK_user_bannedIp` (`Id_banned`);

--
-- Indici per le tabelle `banned_users`
--
ALTER TABLE `banned_users`
  ADD PRIMARY KEY (`Id_ban`),
  ADD UNIQUE KEY `Id_banned` (`Id_banned`);

--
-- Indici per le tabelle `ban_appeals`
--
ALTER TABLE `ban_appeals`
  ADD PRIMARY KEY (`Id_banAppeal`),
  ADD KEY `FK_banApUser` (`Id_user`);

--
-- Indici per le tabelle `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`Id_comment`),
  ADD KEY `Fk_commUser` (`Id_user`),
  ADD KEY `Fk_commPost` (`Id_post`);

--
-- Indici per le tabelle `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`Id_feedback`),
  ADD KEY `FK_feedback_user` (`Id_user`);

--
-- Indici per le tabelle `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`Id_following`,`Id_user`),
  ADD KEY `Id_user` (`Id_user`);

--
-- Indici per le tabelle `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`Id_post`,`Id_user`),
  ADD KEY `Id_user` (`Id_user`);

--
-- Indici per le tabelle `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Id_notification`),
  ADD KEY `FK_user_notification` (`Id_user`);

--
-- Indici per le tabelle `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`Id_post`),
  ADD KEY `Id_pubblisher` (`Id_pubblisher`);

--
-- Indici per le tabelle `reportedposts`
--
ALTER TABLE `reportedposts`
  ADD PRIMARY KEY (`Id_reporter`,`Id_repPost`),
  ADD KEY `vincPost` (`Id_repPost`);

--
-- Indici per le tabelle `reportedusers`
--
ALTER TABLE `reportedusers`
  ADD PRIMARY KEY (`Id_reporter`,`Id_repUs`),
  ADD KEY `fk_us` (`Id_repUs`);

--
-- Indici per le tabelle `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`Id_session`),
  ADD KEY `Id_user` (`Id_user`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id_user`),
  ADD UNIQUE KEY `username_uniqueUsName` (`username`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `announcements`
--
ALTER TABLE `announcements`
  MODIFY `Id_announcement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `banned_users`
--
ALTER TABLE `banned_users`
  MODIFY `Id_ban` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `ban_appeals`
--
ALTER TABLE `ban_appeals`
  MODIFY `Id_banAppeal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `comments`
--
ALTER TABLE `comments`
  MODIFY `Id_comment` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5934;

--
-- AUTO_INCREMENT per la tabella `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `Id_feedback` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Id_notification` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=818;

--
-- AUTO_INCREMENT per la tabella `posts`
--
ALTER TABLE `posts`
  MODIFY `Id_post` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `Id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `FK_commentPost` FOREIGN KEY (`Id_post`) REFERENCES `posts` (`Id_post`) ON DELETE CASCADE;

--
-- Limiti per la tabella `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `FK_followerUsers` FOREIGN KEY (`Id_user`) REFERENCES `users` (`Id_user`) ON DELETE CASCADE;

--
-- Limiti per la tabella `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `FK_likesPost` FOREIGN KEY (`Id_post`) REFERENCES `posts` (`Id_post`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
