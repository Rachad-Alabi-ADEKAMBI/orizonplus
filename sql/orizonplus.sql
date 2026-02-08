-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 08 fév. 2026 à 19:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `orizonplus`
--

-- --------------------------------------------------------

--
-- Structure de la table `budget_lines`
--

CREATE TABLE `budget_lines` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `budget_lines`
--

INSERT INTO `budget_lines` (`id`, `name`, `created_at`) VALUES
(1, 'Main d’œuvreee', '2026-02-05 18:04:04'),
(2, 'Matériel', '2026-02-05 18:04:04'),
(3, 'Transport', '2026-02-05 18:04:04'),
(4, 'Fournitures', '2026-02-05 18:04:04'),
(5, 'Sous-traitance', '2026-02-05 18:04:04'),
(7, 'tetxte ', '2026-02-05 19:31:18'),
(8, 'aa', '2026-02-05 20:10:18');

-- --------------------------------------------------------

--
-- Structure de la table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `project_budget_line_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `expenses`
--

INSERT INTO `expenses` (`id`, `project_id`, `project_budget_line_id`, `amount`, `expense_date`, `description`, `created_at`) VALUES
(30, 17, 27, 4500.00, '2026-02-08', 'iuy', '2026-02-08 18:17:18'),
(31, 16, 26, 450000.00, '2026-02-08', NULL, '2026-02-08 18:18:12'),
(32, 16, 25, 10.00, '2026-02-08', NULL, '2026-02-08 18:19:54');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `total_budget` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `total_budget`, `created_at`) VALUES
(16, 'resr', NULL, 0.00, '2026-02-08 12:52:46'),
(17, 'projet IA', NULL, 0.00, '2026-02-08 18:16:01');

-- --------------------------------------------------------

--
-- Structure de la table `project_budget_lines`
--

CREATE TABLE `project_budget_lines` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `budget_line_id` int(11) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `project_budget_lines`
--

INSERT INTO `project_budget_lines` (`id`, `project_id`, `budget_line_id`, `allocated_amount`, `created_at`) VALUES
(25, 16, 1, 404050.00, '2026-02-08 12:52:46'),
(26, 16, 3, 790000.00, '2026-02-08 12:52:46'),
(27, 17, 1, 12000.00, '2026-02-08 18:16:01'),
(28, 17, 3, 47000.00, '2026-02-08 18:16:01');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `role`, `created_at`) VALUES
(1, 'jolydon', '$2a$12$R31WBodRYoXAvqpmsfmnGent0B.gTkKwucydJZBV7qtMeYPKryLYS', 'admin', '2026-02-05 14:27:38');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `budget_lines`
--
ALTER TABLE `budget_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `project_budget_line_id` (`project_budget_line_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `project_budget_lines`
--
ALTER TABLE `project_budget_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `budget_line_id` (`budget_line_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `budget_lines`
--
ALTER TABLE `budget_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `project_budget_lines`
--
ALTER TABLE `project_budget_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`project_budget_line_id`) REFERENCES `project_budget_lines` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `project_budget_lines`
--
ALTER TABLE `project_budget_lines`
  ADD CONSTRAINT `project_budget_lines_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_budget_lines_ibfk_2` FOREIGN KEY (`budget_line_id`) REFERENCES `budget_lines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
