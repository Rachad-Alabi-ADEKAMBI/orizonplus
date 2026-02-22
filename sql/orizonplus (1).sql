-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 22 fév. 2026 à 19:26
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
(9, 'Transport', '2026-02-08 19:07:17'),
(10, 'Marketing', '2026-02-08 19:07:32'),
(11, 'Matériaux d\'enrobage Tuyauteries grillage avertisseurs et colle tangite Chef chantier Relai QHSE Magasinier  Missions et déplacements  Carburation des véhicules et engins de chantier Equipements HSE Personnel de chantier ', '2026-02-08 21:17:11'),
(12, 'Matériaux d\'enrobage', '2026-02-09 09:14:15'),
(13, 'Tuyauteries grillage avertisseurs et colle tangites', '2026-02-09 09:17:18'),
(14, 'Chef chantier', '2026-02-09 09:19:53'),
(15, 'Relai QHSE', '2026-02-09 09:20:16'),
(16, 'Magasinier', '2026-02-09 09:20:53'),
(17, 'Mission et déplacements', '2026-02-09 09:21:17'),
(18, 'Carburation des véhicules et engins de chantier', '2026-02-09 09:21:58'),
(19, 'Equipement HSE', '2026-02-09 09:22:26'),
(20, 'Personnel de chantier', '2026-02-09 09:22:47'),
(21, 'autres', '2026-02-12 07:54:49'),
(22, 'test', '2026-02-21 14:01:07');

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
  `documents` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `expenses`
--

INSERT INTO `expenses` (`id`, `project_id`, `project_budget_line_id`, `amount`, `expense_date`, `description`, `documents`, `created_at`, `updated_at`, `user_id`) VALUES
(254, 37, 94, 6038346.00, '2026-02-09', 'Achat de tuyaux OPT de 45 et 80 - BIIC 3705054 G.I.C - Avce/Achat', NULL, '2026-02-13 07:14:47', '2026-02-13 08:14:47', NULL),
(255, 37, 94, 151188.00, '2026-02-09', 'Achat de grillage et colle tangits_BIIC 3705055_CTPS', NULL, '2026-02-13 07:14:47', '2026-02-13 08:14:47', NULL),
(256, 37, 98, 50000.00, '2026-02-09', 'Divers dépenes Semaine 1_Frais de mission sur Bohicon_BIIC 3657156', NULL, '2026-02-13 07:14:47', '2026-02-13 08:14:47', NULL),
(257, 37, 98, 13000.00, '2026-02-09', 'Diverses dépenses semaine 1 _déplacement RT avec ouvriers_BIIC 3857157', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(258, 37, 95, 150000.00, '2026-02-09', 'Diverse dépense_Semaine1_Paiement chef chantier_BIIC 3657158', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(259, 37, 99, 47000.00, '2026-02-09', 'Diverses Dépenses_Semaine1_PMT carburant_Déplac mat_COT PAR_BIIC 3657159', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(260, 37, 98, 30000.00, '2026-02-09', 'Diverses Dépenses_PMT frais mission_BIIC3657160', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(261, 37, 98, 50000.00, '2026-02-09', 'Diverses dépenses_PMT carburant du RE_mission_BIIC3657101', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(262, 37, 101, 138000.00, '2026-02-09', 'Diverses dépenses_BIIC 3657162', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(263, 37, 98, 50000.00, '2026-02-09', 'PMT_déplacement _facilitation_réception materiels_BIIC3657163', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(264, 37, 99, 20000.00, '2026-02-09', 'PMT_ Réparation moto_Chef chantier_BIIC 3657164', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(265, 37, 99, 22000.00, '2026-02-09', 'PMT_Réparation camion grue_BIIC 3657165', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(266, 37, 99, 30000.00, '2026-02-09', 'PMT_carburant camionnette_BIIC 3657166', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(267, 37, 94, 2587862.00, '2026-02-09', 'Solde _Achat tuyau OPT de 45 et 80_BIIC 3705066_GIC_FAC01303815', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(268, 37, 101, 400000.00, '2026-02-09', 'PMT_Personnels chantier_BIIC 3703456', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(269, 37, 97, 78000.00, '2026-02-09', 'PMT_Magasinier_BIIC 370356', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(270, 37, 98, 150000.00, '2026-02-09', 'Frais de mission du resp_BIIC 3703456', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(271, 37, 99, 50000.00, '2026-02-09', 'PMT_Gasoil pour engin_BIIC 3703456', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(272, 37, 96, 100000.00, '2026-02-09', 'PMT_Relai HSE_CR 048_FAC01684290-1', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(273, 37, 101, 226000.00, '2026-02-09', 'PMT_ Personnel_08-09_13-09-25_SGB 7666998', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(274, 37, 97, 54000.00, '2026-02-09', 'PMT_Chauffeur_01-09_13-09-25_CR 0585', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(275, 37, 101, 120000.00, '2026-02-09', 'PMT_Manoeuvres_ 16-09_20-09-25_BIII 7744779', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(276, 37, 101, 274000.00, '2026-02-09', 'PMT_Personnels_22-09_27-09-25_BIIC7744784', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(277, 37, 101, 308000.00, '2026-02-09', 'PMT_Personnels_29-09_05-10-25_BIIC 7744789', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(278, 37, 97, 34920.00, '2026-02-09', 'PMT_Chauffeur_22-09_04-10-25_CR 0619', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(279, 37, 96, 100000.00, '2026-02-09', 'PMT_HSE_22-08_22-09-25_CR 0623_FAC01684290-3', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(280, 37, 95, 150000.00, '2026-02-09', 'PMT_Chef chantier_30-08_01-10-25_BIIC 7744788_FAC01171962-51_52', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(281, 37, 98, 64500.00, '2026-02-09', 'Mission Bohicon', NULL, '2026-02-13 07:14:48', '2026-02-13 08:14:48', NULL),
(282, 35, 90, 77000.00, '2026-02-12', '', '[\"6999a71a9986e_1771677466.png\"]', '2026-02-13 07:14:48', '2026-02-21 13:37:46', NULL),
(285, 35, 90, 500000.00, '2026-02-20', 'test', '[\"6997b383bd901.pdf\",\"6997b383bdc68.pdf\"]', '2026-02-20 14:31:50', NULL, 1),
(289, 39, 102, 2241000.00, '2026-02-21', 'pour utilisateur 1', '[\"699a2552b3549_1771709778.png\",\"699a2552b4114_1771709778.png\"]', '2026-02-21 17:00:05', '2026-02-22 12:12:24', 6),
(290, 39, 102, 500000.00, '2026-02-21', 'a faire', '[\"699a2742de0a6.pdf\"]', '2026-02-21 21:46:58', NULL, 5),
(291, 40, 106, 50000.00, '2026-02-22', '', '[]', '2026-02-22 11:37:18', '2026-02-22 12:38:27', 6),
(292, 40, 106, 350000.00, '2025-01-01', '', '[]', '2026-02-22 13:35:45', '2026-02-22 14:36:30', 6),
(293, 42, 110, 1000000.00, '2026-02-22', 'la description', '[\"699b28f4d6397_1771776244.png\",\"699b28f4d7122_1771776244.png\"]', '2026-02-22 16:04:04', '2026-02-22 17:04:04', 2),
(294, 42, 110, 999999.99, '2026-02-22', '', '[\"699b295a7c46c.png\"]', '2026-02-22 16:07:43', NULL, 2);

-- --------------------------------------------------------

--
-- Structure de la table `expenses_validations`
--

CREATE TABLE `expenses_validations` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `project_id` int(11) NOT NULL,
  `project_budget_line_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `documents` text DEFAULT NULL,
  `status` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `expenses_validations`
--

INSERT INTO `expenses_validations` (`id`, `created_at`, `project_id`, `project_budget_line_id`, `amount`, `description`, `expense_date`, `documents`, `status`, `user_id`) VALUES
(1, '2026-02-18 12:46:22', 35, 90, 999999.99, 'yufgh', '2026-02-18', '[\"6995a68ed7431.pdf\",\"6995a68ed8e9b.pdf\"]', 'en attente', 1),
(4, '2026-02-20 02:06:11', 35, 90, 500000.00, 'test', '2026-02-20', '[\"6997b383bd901.pdf\",\"6997b383bdc68.pdf\"]', 'acceptée', 1),
(7, '2026-02-21 22:44:34', 39, 102, 500000.00, 'a faire', '2026-02-21', '[\"699a2742de0a6.pdf\"]', 'acceptée', 5),
(8, '2026-02-22 12:13:37', 40, 106, 500000.00, '', '2026-02-22', NULL, 'acceptée', 6),
(9, '2026-02-22 17:05:46', 42, 110, 999999.99, '', '2026-02-22', '[\"699b295a7c46c.png\"]', 'acceptée', 2);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_name`, `description`, `is_read`, `created_at`) VALUES
(1, 1, 'jolydon', 'Projet \"projet ia\" (ID 23) modifié par jolydon.\n\nLigne ID 49 modifiée : 1200000.00 → 11200000\nNouvelle ligne ajoutée (Budget ID 19) : 100000', 0, '2026-02-12 08:47:24'),
(2, 1, 'jolydon', 'Projet \"projet ia\" (ID 23) modifié par jolydon.\n\nNouveau document ajouté : 698d8615dfb67.pdf\nNouveau document ajouté : 698d8615e06eb.png', 0, '2026-02-12 08:49:41'),
(3, 1, 'jolydon', 'Projet \"projet ia\" (ID 23) modifié par jolydon.\n\nDocument supprimé : 698d8615e06eb.png', 0, '2026-02-12 08:51:01'),
(4, 1, 'jolydon', 'Projet \"projet ia\" (ID 23) modifié par jolydon.\n\nNouveau document ajouté : 698d86ae5cebc.png\nNouveau document ajouté : 698d86ae61dbd.pdf', 0, '2026-02-12 08:52:14'),
(5, 1, 'jolydon', 'Le projet \"projet ia\" a été modifié par jolydon.\n\nDocument ajouté : 698d9103749ba.pdf\nDescription modifié : \"notre projet\" → \"notre projet oh\"\nDepartment modifié : \"Génie Civil\" → \"Electricité\"\nLocation modifié : \"Lokossa\" → \"Lokossaa\"\nDate_of_creation modifié : \"2026-02-10\" → \"2026-02-11\"', 0, '2026-02-12 09:36:19'),
(6, 1, 'jolydon', 'Nouvelle dépense enregistrée par jolydon pour le projet \"projet ia\":\nLigne budgétaire: Mission et déplacements\nMontant: 100000 FCFA\nDescription: gubh\nDocument: images/698db3a006d94.png', 0, '2026-02-12 12:04:00'),
(7, 1, 'jolydon', 'Nouvelle dépense enregistrée par jolydon pour le projet \"projet ia\":\nLigne budgétaire: Mission et déplacements\nMontant: 1400000 FCFA\nDocument: 698db6007982d.png', 0, '2026-02-12 12:14:08'),
(8, 1, 'jolydon', 'Dépense  modifiée par\' jolydon', 0, '2026-02-12 12:24:11'),
(9, 1, 'jolydon', 'Dépense  modifiée par\' jolydon', 0, '2026-02-12 12:24:37'),
(10, 1, 'jolydon', 'Dépense  modifiée par\' jolydon', 0, '2026-02-12 12:29:14'),
(11, 1, 'jolydon', 'Dépense modifiée par jolydon:\nDescription: \"\" → \"test\"\nDocument: aucun → images/698dbff65e5c5.png', 0, '2026-02-12 12:56:38'),
(12, 2, 'rachad', 'Dépense modifiée par rachad:\nMontant: 1400000.00 → 2000000', 0, '2026-02-12 15:41:59'),
(13, 2, 'rachad', 'Dépense modifiée par rachad:\nMontant: 2000000.00 → 2500000', 0, '2026-02-12 15:42:11'),
(14, 2, 'rachad', 'Dépense modifiée par rachad:\nMontant: 2500000.00 → 302500000', 0, '2026-02-12 15:43:25'),
(15, 1, 'jolydon', 'Dépense modifiée par jolydon:\nMontant: 500000.00 → 2000', 0, '2026-02-17 17:11:45'),
(16, 1, 'jolydon', 'Dépense modifiée par jolydon:\nMontant: 2000.00 → 522000', 0, '2026-02-18 11:58:21'),
(17, 1, 'jolydon', 'Dépense modifiée par jolydon:\nMontant: 522000.00 → 2000', 0, '2026-02-18 12:00:24'),
(18, 1, 'jolydon', 'Nouvelle dépense enregistrée par jolydon pour le projet \"projet test\":\nLigne budgétaire: Magasinier\nMontant: 150000 FCFA', 0, '2026-02-18 12:32:43'),
(19, 1, 'jolydon', 'Nouvelle demande de validation\n\nUtilisateur : jolydon\nProjet : Chantier Cotonou\nLigne budgétaire : Transport\nMontant : 500000 FCFA\nDate : 2026-02-20\nDescription : test\nDocuments : 6997b383bd901.pdf, 6997b383bdc68.pdf\n⚠ Dépassement : 300000 FCFA\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-20 02:06:11'),
(20, 1, 'jolydon', 'Votre demande de validation a été enregistrée.\n\nProjet : Chantier Cotonou\nLigne budgétaire : Transport\nMontant : 500000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-20 02:06:11'),
(21, 1, 'jolydon', 'Dépense modifiée par jolydon:', 0, '2026-02-20 05:01:46'),
(22, 1, 'jolydon', 'Nouvelle demande de validation\n\nUtilisateur : jolydon\nProjet : projet test\nLigne budgétaire : Magasinier\nMontant : 9000000 FCFA\nDate : 2026-02-20\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-20 13:24:06'),
(23, 1, 'jolydon', 'Votre demande de validation a été enregistrée.\n\nProjet : projet test\nLigne budgétaire : Magasinier\nMontant : 9000000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-20 13:24:06'),
(24, 1, 'jolydon', 'Bonjour jolydon,\n\nVotre demande de validation a été acceptée.\n\nProjet : projet test\nLigne budgétaire : Magasinier\nMontant : 1 000 000 FCFA\nDate : 2026-02-20\n\nLa dépense a été enregistrée dans le système.', 0, '2026-02-20 15:35:46'),
(25, 1, 'jolydon', 'Bonjour jolydon,\n\nVotre demande de validation d\'une dépense a été refusée.\n\nMontant : 1 000 000 FCFA\nDescription : test test\n\nMerci de vérifier votre demande ou contacter l\'administration.', 0, '2026-02-20 15:41:31'),
(26, 2, 'rachad', 'Bonjour rachad,\n\nVotre mot de passe a été mis à jour avec succès.', 0, '2026-02-20 18:02:56'),
(27, 1, 'jolydon', 'Dépense modifiée par jolydon:\nMontant: 999999.99 → 40000', 0, '2026-02-20 21:09:38'),
(28, 2, 'rachad', 'Nouvelle dépense enregistrée par rachad pour le projet \"projet test\":\nLigne budgétaire: Magasinier\nMontant: 52000 FCFA\nDescription: nouvo\nDocuments joints: 2', 0, '2026-02-20 21:33:17'),
(29, 1, 'jolydon', 'Documents de la dépense ID 287 mis à jour par jolydon. Total documents : 1', 0, '2026-02-20 21:35:07'),
(30, 2, 'rachad', 'Dépense modifiée par rachad:\nMontant: 52000.00 → 102000\nDocuments mis à jour (3 total)', 0, '2026-02-20 21:41:14'),
(31, 2, 'rachad', 'Dépense modifiée par rachad:\nMontant: 2000.00 → 77000\nDocuments mis à jour (1 total)', 0, '2026-02-21 13:37:46'),
(32, 1, 'jolydon', 'Le projet \"Chantier Cotonou\" a été modifié par jolydon.\n\nDocument ajouté : 6999b2177b71e.png\nDocument ajouté : 6999b2177ba8b.png\nDescription modifié : \"\" → \"un prjet de cotonou\"\nDepartment modifié : \"\" → \"Electricité\"\nLocation modifié : \"\" → \"Cotonou\"\nDate_of_creation modifié : \"\" → \"2025-02-20\"', 0, '2026-02-21 14:24:39'),
(33, 1, 'jolydon', 'Le projet \"TRAVAUX PROVISOIRES DE DEPLACEMENT DE RESEAUX TELECOM BOHICON TINDJI ZAKPOTA\" a été modifié par jolydon.\n\nDescription modifié : \"\" → \"nouveaux travaux\"\nDepartment modifié : \"\" → \"Génie Civil\"\nLocation modifié : \"\" → \"Parakou\"\nDate_of_creation modifié : \"\" → \"2025-01-01\"', 0, '2026-02-21 14:25:29'),
(34, 1, 'jolydon', 'Le projet \"projet test\" a été modifié par jolydon.\n\nDepartment modifié : \"\" → \"Télécommunication\"\nLocation modifié : \"\" → \"Bohicon\"\nDate_of_creation modifié : \"\" → \"2025-12-02\"', 0, '2026-02-21 14:26:08'),
(35, 1, 'jolydon', 'Le projet \"Chantier Cotonou\" a été modifié par jolydon.\n\nLigne \"Transport\" modifiée : 200000.00 → 50000000\nLigne \"Marketing\" modifiée : 100000.00 → 2100000', 0, '2026-02-21 14:26:23'),
(36, 2, 'rachad', 'Nouvelle demande de validation\n\nUtilisateur : rachad\nProjet : Chantier Cotonou\nLigne budgétaire : Marketing\nMontant : 2500000 FCFA\nDate : 2026-02-21\n⚠ Dépassement : 400000 FCFA\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-21 14:30:15'),
(37, 2, 'rachad', 'Votre demande de validation a été enregistrée.\n\nProjet : Chantier Cotonou\nLigne budgétaire : Marketing\nMontant : 2500000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-21 14:30:15'),
(38, 2, 'rachad', 'Bonjour rachad,\n\nVotre demande de validation a été acceptée.\n\nProjet : Chantier Cotonou\nLigne budgétaire : Marketing\nMontant : 1 000 000 FCFA\nDate : 2026-02-21\n\nLa dépense a été enregistrée dans le système.', 0, '2026-02-21 14:31:05'),
(39, 2, 'rachad', 'Bonjour rachad, votre mot de passe a été mis à jour avec succès.', 0, '2026-02-21 14:38:54'),
(40, 2, 'rachad', 'Bonjour rachad, votre mot de passe a été mis à jour avec succès.', 0, '2026-02-21 14:40:33'),
(41, 2, 'rachad', 'Bonjour rachad, votre mot de passe a été mis à jour avec succès.', 0, '2026-02-21 14:43:17'),
(42, 1, 'jolydon', 'Le projet \"Chantier Cotonou\" a été modifié par jolydon.\n\nName modifié : \"Chantier Cotonou\" → \"Chantier Cotonouu\"', 0, '2026-02-21 15:02:08'),
(43, 1, 'jolydon', 'Le projet \"chantier AA\" a été modifié par jolydon.\n\nDescription modifié : \"nouveau test\" → \"nouvelle description\"\nLocation modifié : \"Cotonou\" → \"Accra\"', 0, '2026-02-21 17:57:55'),
(44, 1, 'jolydon', 'Nouvelle dépense enregistrée par jolydon pour le projet \"chantier AA\":\nLigne budgétaire: Transport\nMontant: 55000 FCFA\nDescription: description dépense', 0, '2026-02-21 18:00:05'),
(45, 1, 'Système', 'Projet modifié : \"chantier AA\" (ID 39). Détails des changements :\n\nObservation modifié : \"\" → \"yughyughuuio\"', 0, '2026-02-21 19:32:36'),
(46, 1, 'Système', 'Projet modifié : \"chantier AA\" (ID 39). Détails des changements :\n\nObservation modifié : \"yughyughuuio\" → \"une observation\"', 0, '2026-02-21 22:06:38'),
(47, 1, 'jolydon', 'Dépense modifiée par jolydon:\nMontant: 55000.00 → 255000\nDocuments mis à jour (3 total)', 0, '2026-02-21 22:36:18'),
(48, 1, 'Système', 'Projet modifié : \"Chantier Cotonou\" (ID 35). Détails des changements :\n\nName modifié : \"Chantier Cotonouu\" → \"Chantier Cotonou\"\nDescription modifié : \"un prjet de cotonou\" → \"un projet de cotonou\"\nObservation modifié : \"\" → \"à faire avant decembre\"\nContract number modifié : \"\" → \"BC cot 256\"\nContract amount ht modifié : \"0\" → \"60000000\"\nExecution budget ht modifié : \"0\" → \"50000000\"\nCollected amount ht modifié : \"0\" → \"4000000\"\nLigne ajoutée : \"Equipement HSE\" (2000000)', 0, '2026-02-21 22:39:18'),
(49, 1, 'Système', 'Projet modifié : \"projet  Calavi\" (ID 38). Détails des changements :\n\nName modifié : \"huuih\" → \"projet  Calavi\"\nDescription modifié : \"huihhh\" → \"Calavi SBEE\"\nLocation modifié : \"hguiuh\" → \"Calavi\"\nDate of creation modifié : \"2026-02-21\" → \"2026-01-01\"\nObservation modifié : \"\" → \"urgent\"\nContract number modifié : \"ijo\" → \"bc new 2026\"\nContract amount ht modifié : \"45\" → \"1000000\"\nExecution budget ht modifié : \"5565656\" → \"500000\"\nCollected amount ht modifié : \"45454\" → \"600000\"\nLigne ajoutée : \"Chef chantier\" (500000)', 0, '2026-02-21 22:40:42'),
(50, 1, 'Système', 'Projet modifié : \"TRAVAUX PROVISOIRES DE DEPLACEMENT DE RESEAUX TELECOM BOHICON TINDJI ZAKPOTA\" (ID 37). Détails des changements :\n\nContract number modifié : \"\" → \"BC 2824 RB\"\nContract amount ht modifié : \"0\" → \"20000000\"\nExecution budget ht modifié : \"0\" → \"13675000\"\nCollected amount ht modifié : \"0\" → \"11000000\"', 0, '2026-02-21 22:42:14'),
(51, 5, 'utilisateur2', 'Nouvelle demande de validation\n\nUtilisateur : utilisateur2\nProjet : chantier AA\nLigne budgétaire : Transport\nMontant : 500000 FCFA\nDate : 2026-02-21\nDescription : a faire\nDocuments : 699a2742de0a6.pdf\n⚠ Dépassement : 100000 FCFA\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-21 22:44:35'),
(52, 5, 'utilisateur2', 'Votre demande de validation a été enregistrée.\n\nProjet : chantier AA\nLigne budgétaire : Transport\nMontant : 500000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-21 22:44:35'),
(53, 5, 'utilisateur2', 'Bonjour utilisateur2,\n\nVotre demande de validation a été acceptée.\n\nProjet : chantier AA\nLigne budgétaire : Transport\nMontant : 500 000 FCFA\nDate : 2026-02-21\nDescription : a faire\n\nLa dépense a été enregistrée dans le système.', 0, '2026-02-21 22:46:58'),
(54, 6, 'utilisateur 1', 'Dépense modifiée par utilisateur 1:\nMontant: 255000.00 → 155000', 0, '2026-02-22 01:01:20'),
(55, 1, 'admin', 'Dépense #289 modifiée par utilisateur 1 :\nMontant : 155000.00 → 151000', 1, '2026-02-22 01:12:25'),
(56, 1, 'admin', 'Projet : chantier AA\nDépense #289 modifiée par utilisateur 1 :\nMontant : 151000.00 → 91000\nDescription modifiée', 1, '2026-02-22 01:17:18'),
(57, 1, 'admin', 'Projet : Projet inconnu\nDépense #289 modifiée par utilisateur 1 :\nMontant : 91000.00 → 81000', 1, '2026-02-22 01:23:05'),
(58, 1, 'admin', 'Projet modifié : \"Pariskaa\" (ID 40). Détails des changements :\n\nAmount to pay to suppliers modifié : \"4000000\" → \"1000000\"\nAmount paid to suppliers modifié : \"460000\" → \"346000\"', 1, '2026-02-22 03:13:23'),
(59, 1, 'admin', 'Projet modifié : \"Pariskaa\" (ID 40). Détails des changements :\n\nDocument ajouté : doc_699a668c03676.png\nDocument ajouté : doc_699a668c039bc.png\nAmount to pay to suppliers modifié : \"1000000\" → \"2000000\"', 1, '2026-02-22 03:14:36'),
(60, 1, 'admin', 'Projet modifié : \"Pariskaa\" (ID 40). Détails des changements :\n\nObservation modifié : \"\" → \"le commentaire\"', 1, '2026-02-22 03:21:58'),
(61, 1, 'admin', 'Projet modifié : \"chantier AA\" (ID 39). Détails des changements :\n\nAmount to pay to suppliers modifié : \"\" → \"450000\"\nAmount paid to suppliers modifié : \"\" → \"25000\"', 1, '2026-02-22 03:42:56'),
(62, 1, 'Admin', 'Documents de la dépense \"pour utilisateur 1\" du projet \"chantier AA\" mis à jour par utilisateur 1. Total documents : 2', 1, '2026-02-22 12:01:59'),
(63, 1, 'admin', '════════════════════════════════\n  MODIFICATION DE DÉPENSE\n════════════════════════════════\nProjet       : chantier AA\nContrat      : yghui hihu \nDépense      : #289\nMontant actuel : 41 000 FCFA\nDate dépense : 2026-02-21\nLigne budg.  : Transport\nModifié par  : utilisateur 1 (user #6)\nLe           : 22/02/2026 à 12:11\n────────────────────────────────\nModifications :\n• Montant      : 81 000 FCFA → 41 000 FCFA\n• Responsable  : attribué à utilisateur 1 (user #6)\n════════════════════════════════', 1, '2026-02-22 12:11:46'),
(64, 1, 'admin', '════════════════════════════════\n  MODIFICATION DE DÉPENSE\n════════════════════════════════\nProjet       : chantier AA\nContrat      : yghui hihu \nDépense      : #289\nMontant actuel : 2 241 000 FCFA\nDate dépense : 2026-02-21\nLigne budg.  : Transport\nModifié par  : utilisateur 1 (user #6)\nLe           : 22/02/2026 à 12:12\n────────────────────────────────\nModifications :\n• Montant      : 41 000 FCFA → 2 241 000 FCFA\n════════════════════════════════', 1, '2026-02-22 12:12:24'),
(65, 6, 'utilisateur 1', 'Nouvelle demande de validation\n\nUtilisateur : utilisateur 1\nProjet : Pariskaa\nLigne budgétaire : Magasinier\nMontant : 500000 FCFA\nDate : 2026-02-22\n⚠ Dépassement : 80000 FCFA\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-22 12:13:38'),
(66, 6, 'utilisateur 1', 'Votre demande de validation a été enregistrée.\n\nProjet : Pariskaa\nLigne budgétaire : Magasinier\nMontant : 500000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-22 12:13:38'),
(67, 6, 'utilisateur 1', 'Bonjour utilisateur 1,\n\nVotre demande de validation a été acceptée.\n\nProjet : Pariskaa\nLigne budgétaire : Magasinier\nMontant : 500 000 FCFA\nDate : 2026-02-22\n\nLa dépense a été enregistrée dans le système.', 0, '2026-02-22 12:37:18'),
(68, 1, 'admin', '════════════════════════════════\n  MODIFICATION DE DÉPENSE\n════════════════════════════════\nProjet       : Pariskaa\nContrat      : 96 po 054/9\nDépense      : #291\nMontant actuel : 50 000 FCFA\nDate dépense : 2026-02-22\nLigne budg.  : Magasinier\nModifié par  : utilisateur 1 (user #6)\nLe           : 22/02/2026 à 12:38\n────────────────────────────────\nModifications :\n• Montant      : 500 000 FCFA → 50 000 FCFA\n════════════════════════════════', 1, '2026-02-22 12:38:27'),
(69, 1, 'admin', 'Projet modifié : \"TRAVAUX PROVISOIRES DE DEPLACEMENT DE RESEAUX TELECOM BOHICON TINDJI ZAKPOTA\" (ID 37). Détails des changements :\n\nDocument ajouté : doc_699b02d2c6dca.png\nDocument ajouté : doc_699b02d2c72e4.pdf\nObservation modifié : \"\" → \"difficile comme projet\"\nAmount to pay to suppliers modifié : \"\" → \"4500000\"\nAmount paid to suppliers modifié : \"\" → \"1000000\"', 1, '2026-02-22 14:21:22'),
(70, 6, 'utilisateur 1', 'Nouvelle dépense enregistrée par utilisateur 1 pour le projet \"Pariskaa\":\nLigne budgétaire: Magasinier\nMontant: 150000 FCFA', 0, '2026-02-22 14:35:45'),
(71, 1, 'admin', '════════════════════════════════\n  MODIFICATION DE DÉPENSE\n════════════════════════════════\nProjet       : Pariskaa\nContrat      : 96 po 054/9\nDépense      : #292\nMontant actuel : 350 000 FCFA\nDate dépense : 2025-01-01\nLigne budg.  : Magasinier\nModifié par  : utilisateur 1 (user #6)\nLe           : 22/02/2026 à 14:36\n────────────────────────────────\nModifications :\n• Montant      : 150 000 FCFA → 350 000 FCFA\n════════════════════════════════', 1, '2026-02-22 14:36:30'),
(72, 1, 'admin', 'Projet modifié : \"projet test\" (ID 42). Détails des changements :\n\nObservation modifié : \"\" → \"une observation\"', 1, '2026-02-22 17:00:55'),
(73, 2, 'rachad', 'Nouvelle dépense enregistrée par rachad pour le projet \"projet test\":\nLigne budgétaire: Relai QHSE\nMontant: 1000000 FCFA\nDescription: la description\nDocuments joints: 2', 0, '2026-02-22 17:04:04'),
(74, 2, 'rachad', 'Nouvelle demande de validation\n\nUtilisateur : rachad\nProjet : projet test\nLigne budgétaire : Relai QHSE\nMontant : 2000000 FCFA\nDate : 2026-02-22\nDocuments : 699b295a7c46c.png\n\nAction requise : Confirmer ou refuser.', 0, '2026-02-22 17:05:46'),
(75, 2, 'rachad', 'Votre demande de validation a été enregistrée.\n\nProjet : projet test\nLigne budgétaire : Relai QHSE\nMontant : 2000000 FCFA\nStatut : en attente\n\nVous serez notifié après décision.', 0, '2026-02-22 17:05:46'),
(76, 2, 'rachad', 'Bonjour rachad,\n\nVotre demande de validation a été acceptée.\n\nProjet : projet test\nLigne budgétaire : Relai QHSE\nMontant : 1 000 000 FCFA\nDate : 2026-02-22\n\nLa dépense a été enregistrée dans le système.', 0, '2026-02-22 17:07:43');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contract_number` varchar(255) DEFAULT NULL,
  `contract_amount_ht` int(12) DEFAULT 0,
  `execution_budget_ht` int(12) DEFAULT 0,
  `collected_amount_ht` int(12) DEFAULT 0,
  `total_payment_made` decimal(15,2) DEFAULT 0.00,
  `observation` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `project_status` varchar(30) DEFAULT NULL,
  `amount_to_pay_to_suppliers` int(12) DEFAULT NULL,
  `amount_paid_to_suppliers` int(12) DEFAULT NULL,
  `date_of_creation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `name`, `contract_number`, `contract_amount_ht`, `execution_budget_ht`, `collected_amount_ht`, `total_payment_made`, `observation`, `description`, `created_at`, `department`, `location`, `documents`, `project_status`, `amount_to_pay_to_suppliers`, `amount_paid_to_suppliers`, `date_of_creation`) VALUES
(35, 'Chantier Cotonou', 'BC cot 256', 60000000, 50000000, 4000000, 0.00, 'à faire avant decembre', 'un projet de cotonou', '2026-02-13 07:14:47', 'Electricité', 'Cotonou', '[\"6999b2177b71e.png\",\"6999b2177ba8b.png\"]', 'Déverrouillé', NULL, NULL, '2025-02-20'),
(37, 'TRAVAUX PROVISOIRES DE DEPLACEMENT DE RESEAUX TELECOM BOHICON TINDJI ZAKPOTA', 'BC 2824 RB', 20000000, 13675000, 11000000, 0.00, 'difficile comme projet', 'nouveaux travaux', '2026-02-13 07:14:47', 'Génie Civil', 'Parakou', '[\"doc_699b02d2c6dca.png\",\"doc_699b02d2c72e4.pdf\"]', 'Déverrouillé', 4500000, 1000000, '2025-01-01'),
(38, 'projet  Calavi', 'bc new 2026', 1000000, 500000, 200000, 0.00, 'urgent', 'Calavi SBEE', '2026-02-21 14:42:29', 'Electricité', 'Calavi', '[]', 'Déverrouillé', NULL, NULL, '2026-01-01'),
(39, 'chantier AA', 'yghui hihu ', 1500000, 1000000, 700000, 0.00, 'une observation', 'nouvelle description', '2026-02-21 16:53:42', 'Electricité', 'Accra', '[\"doc_6999e316c431d.png\",\"doc_6999e316c4ac7.png\",\"doc_6999e316c4de5.png\"]', 'Déverrouillé', 450000, 25000, '2026-02-15'),
(40, 'Pariskaa', '96 po 054/9', 8000000, 6000000, 2500000, 0.00, 'le commentaire', 'ouch', '2026-02-22 02:07:25', 'AEP', 'France', '[\"doc_699a64dd033ca.png\",\"doc_699a668c03676.png\",\"doc_699a668c039bc.png\"]', 'Déverrouillé', 2000000, 346000, '2026-02-22'),
(41, 'projet test dimanche', 'BC DIM 002', 5000000, 3500000, 2700000, 0.00, NULL, 'une description', '2026-02-22 15:53:04', 'Electricité', 'Cotonou', '[\"doc_699b2660101d4.png\",\"doc_699b2660106f9.png\"]', 'Verrouillé', 2000000, 400000, '2026-02-22'),
(42, 'projet test', 'BC 2026', 5000000, 3500000, 2700000, 0.00, 'une observation', 'une description', '2026-02-22 16:00:13', 'Electricité', 'Cotonou', '[\"doc_699b280d418c1.png\",\"doc_699b280d420c0.png\"]', 'Déverrouillé', 400000, 250000, '2026-02-22');

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
(90, 35, 9, 50000000.00, '2026-02-13 07:14:47'),
(93, 37, 12, 1020000.00, '2026-02-13 07:14:47'),
(94, 37, 13, 8855000.00, '2026-02-13 07:14:47'),
(95, 37, 14, 300000.00, '2026-02-13 07:14:47'),
(96, 37, 15, 200000.00, '2026-02-13 07:14:47'),
(97, 37, 16, 200000.00, '2026-02-13 07:14:47'),
(98, 37, 17, 500000.00, '2026-02-13 07:14:47'),
(99, 37, 18, 200000.00, '2026-02-13 07:14:47'),
(100, 37, 19, 200000.00, '2026-02-13 07:14:47'),
(101, 37, 20, 2200000.00, '2026-02-13 07:14:47'),
(102, 39, 9, 400000.00, '2026-02-21 16:53:42'),
(103, 39, 10, 200000.00, '2026-02-21 16:53:42'),
(104, 35, 19, 2000000.00, '2026-02-21 21:39:18'),
(105, 38, 14, 500000.00, '2026-02-21 21:40:42'),
(106, 40, 16, 420000.00, '2026-02-22 02:07:25'),
(107, 40, 21, 5000000.00, '2026-02-22 02:07:25'),
(108, 41, 14, 2000000.00, '2026-02-22 15:53:04'),
(109, 41, 16, 1500000.00, '2026-02-22 15:53:04'),
(110, 42, 15, 2000000.00, '2026-02-22 16:00:13'),
(111, 42, 21, 1500000.00, '2026-02-22 16:00:13');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(150) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `role`, `created_at`, `status`, `updated_at`) VALUES
(1, 'jolydon', '$2a$12$R31WBodRYoXAvqpmsfmnGent0B.gTkKwucydJZBV7qtMeYPKryLYS', 'admin', '2026-02-05 14:27:38', 'Actif', NULL),
(2, 'rachad', '$2y$10$wNcsDnAY0WigGWLiWqapferJ3cvsa5sh5GkNC9ZoIp2i1pUCiw5MK', 'utilisateur', '2026-02-05 14:27:38', 'Actif', '2026-02-20 15:07:09'),
(5, 'utilisateur2', '$2y$10$spzwKPNpJhUQB6x7120Rc.nx2fuTfW1Ji.1BR.exW3fkiuq2tjS1G', 'utilisateur', '2026-02-21 21:42:56', 'Actif', NULL),
(6, 'utilisateur 1', '$2y$10$3oTOFRPNCmdZ/jbuN/YFyOAFRovRUQ2K8Clgnk4xYO3H4q0RZYrNi', 'utilisateur', '2026-02-21 23:58:33', 'Actif', NULL);

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
-- Index pour la table `expenses_validations`
--
ALTER TABLE `expenses_validations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expenses_validations_project` (`project_id`),
  ADD KEY `fk_expenses_validations_budget_line` (`project_budget_line_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- AUTO_INCREMENT pour la table `expenses_validations`
--
ALTER TABLE `expenses_validations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `project_budget_lines`
--
ALTER TABLE `project_budget_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Contraintes pour la table `expenses_validations`
--
ALTER TABLE `expenses_validations`
  ADD CONSTRAINT `fk_expenses_validations_budget_line` FOREIGN KEY (`project_budget_line_id`) REFERENCES `project_budget_lines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expenses_validations_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
