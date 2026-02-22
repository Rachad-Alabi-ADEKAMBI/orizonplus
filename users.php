<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
// s'il n'y a pas de session ou user non correct, rediriger vers login.php
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
	header("Location: login.php");
	exit;
}

$user_role = $_SESSION['user_role'] ?? 'consultant';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>OrizonPlus • Gestion des Utilisateurs</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon">

	<script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		:root {
			--bg-primary: #0a0a0a;
			--bg-secondary: #111111;
			--bg-tertiary: #1a1a1a;
			--border-color: #2a2a2a;
			--text-primary: #ededed;
			--text-secondary: #a0a0a0;
			--accent-blue: #0070f3;
			--accent-cyan: #00d4ff;
			--accent-green: #00e676;
			--accent-red: #ff3b3b;
			--accent-yellow: #ffb800;
			--accent-purple: #7c3aed;
			--gradient-2: linear-gradient(135deg, #0070f3 0%, #00d4ff 100%);
			--shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);
			--radius: 12px;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
			background: var(--bg-primary);
			color: var(--text-primary);
			line-height: 1.6;
		}

		.notif-badge {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: var(--accent-red);
			color: #fff;
			font-size: 0.65rem;
			font-weight: 700;
			min-width: 18px;
			height: 18px;
			border-radius: 999px;
			padding: 0 4px;
			margin-left: 4px;
			line-height: 1;
			animation: pulse-badge 2s infinite;
		}

		.footer {
			background: var(--bg-secondary);
			border-top: 1px solid var(--border-color);
			padding: 2rem;
			margin-top: 3rem;
			text-align: center;
		}

		.footer-content {
			max-width: 1400px;
			margin: 0 auto;
			display: flex;
			flex-direction: column;
			gap: 1rem;
		}

		.footer-top {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 2rem;
			padding-bottom: 2rem;
			border-bottom: 1px solid var(--border-color);
		}

		.footer-section h4 {
			font-size: 0.875rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: var(--text-secondary);
			margin-bottom: 1rem;
			font-weight: 600;
		}

		.footer-section p {
			font-size: 0.875rem;
			color: var(--text-secondary);
			line-height: 1.6;
		}

		.footer-bottom {
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 1rem;
			padding-top: 2rem;
		}

		.footer-info {
			font-size: 0.75rem;
			color: var(--text-secondary);
		}


		.header {
			background: var(--bg-secondary);
			border-bottom: 1px solid var(--border-color);
			padding: 1rem 2rem;
			position: sticky;
			top: 0;
			z-index: 100;
		}

		.header-content {
			max-width: 1400px;
			margin: 0 auto;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 1rem;
		}

		.logo {
			font-size: 1.5rem;
			font-weight: 700;
			display: flex;
			align-items: center;
			gap: 0.5rem;
			background: var(--gradient-2);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
		}

		.nav-menu {
			display: flex;
			gap: 1rem;
			list-style: none;
			order: 2;
		}

		.nav-link {
			padding: 0.5rem 1rem;
			border-radius: var(--radius);
			text-decoration: none;
			color: var(--text-secondary);
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			gap: 0.5rem;
			background: transparent;
			border: none;
			cursor: pointer;
			font-size: 0.875rem;
		}

		.nav-link:hover,
		.nav-link.active {
			color: var(--text-primary);
			background: var(--bg-tertiary);
		}

		.hamburger-btn {
			display: none;
			background: none;
			border: none;
			color: var(--text-primary);
			font-size: 1.5rem;
			cursor: pointer;
			padding: 0.5rem;
			order: 3;
		}

		@media (max-width: 768px) {
			.hamburger-btn {
				display: block;
			}

			.logo {
				order: 1;
			}

			.nav-menu {
				position: fixed;
				top: 73px;
				left: 0;
				right: 0;
				background: var(--bg-secondary);
				border-bottom: 1px solid var(--border-color);
				flex-direction: column;
				gap: 0;
				padding: 1rem 0;
				transform: translateX(-100%);
				transition: transform 0.3s ease;
				z-index: 99;
			}

			.nav-menu.active {
				transform: translateX(0);
			}

			.nav-link {
				padding: 1rem 2rem;
				border-radius: 0;
			}
		}

		.container {
			max-width: 1400px;
			margin: 0 auto;
			padding: 2rem;
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 1.5rem;
			margin-bottom: 2rem;
		}

		.stat-card {
			background: var(--bg-secondary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			padding: 1.5rem;
			transition: all 0.3s ease;
			position: relative;
			overflow: hidden;
		}

		.stat-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 3px;
			background: var(--gradient-2);
			transform: scaleX(0);
			transition: transform 0.3s ease;
		}

		.stat-card:hover::before {
			transform: scaleX(1);
		}

		.stat-card:hover {
			transform: translateY(-4px);
			border-color: var(--accent-blue);
			box-shadow: var(--shadow-lg);
		}

		.stat-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 1rem;
		}

		.stat-label {
			color: var(--text-secondary);
			font-size: 0.875rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.stat-icon {
			width: 40px;
			height: 40px;
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.2rem;
		}

		.stat-value {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 0.5rem;
		}

		.section-card {
			background: var(--bg-secondary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			margin-bottom: 2rem;
			overflow: hidden;
		}

		.section-header {
			padding: 1.5rem;
			border-bottom: 1px solid var(--border-color);
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 1rem;
		}

		.section-title {
			font-size: 1.25rem;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.section-content {
			padding: 1.5rem;
		}

		.filters {
			display: flex;
			gap: 1rem;
			margin-bottom: 1.5rem;
			flex-wrap: wrap;
		}

		.search-box {
			flex: 1;
			min-width: 250px;
			position: relative;
		}

		.search-box i {
			position: absolute;
			left: 1rem;
			top: 50%;
			transform: translateY(-50%);
			color: var(--text-secondary);
		}

		.search-input {
			width: 100%;
			padding: 0.75rem 1rem 0.75rem 2.5rem;
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			color: var(--text-primary);
			font-size: 0.875rem;
			transition: all 0.3s ease;
		}

		.search-input:focus,
		.filter-select:focus {
			outline: none;
			border-color: var(--accent-blue);
		}

		.filter-select {
			padding: 0.75rem 1rem;
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			color: var(--text-primary);
			font-size: 0.875rem;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.btn {
			padding: 0.75rem 1.5rem;
			border-radius: var(--radius);
			border: none;
			font-size: 0.875rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			text-decoration: none;
		}

		.btn-primary {
			background: var(--accent-blue);
			color: white;
		}

		.btn-primary:hover {
			background: #0060df;
			transform: translateY(-2px);
		}

		.btn-success {
			background: var(--accent-green);
			color: #111;
		}

		.btn-success:hover {
			background: #00c760;
			transform: translateY(-2px);
		}

		.btn-warning {
			background: var(--accent-yellow);
			color: #111;
		}

		.btn-warning:hover {
			background: #e5a600;
			transform: translateY(-2px);
		}

		.btn-danger {
			background: var(--accent-red);
			color: white;
		}

		.btn-danger:hover {
			background: #e02a2a;
			transform: translateY(-2px);
		}

		.btn-secondary {
			background: var(--bg-tertiary);
			color: var(--text-primary);
			border: 1px solid var(--border-color);
		}

		.btn-sm {
			padding: 0.5rem 1rem;
			font-size: 0.8rem;
		}

		.btn-icon {
			width: 36px;
			height: 36px;
			padding: 0;
			justify-content: center;
		}

		.btn:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}

		.action-buttons {
			display: flex;
			gap: 0.5rem;
			flex-wrap: wrap;
		}

		.table-container {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.table {
			width: 100%;
			border-collapse: collapse;
		}

		.table thead {
			background: var(--bg-tertiary);
		}

		.table th {
			padding: 1rem;
			text-align: left;
			font-weight: 600;
			font-size: 0.875rem;
			color: var(--text-secondary);
			border-bottom: 1px solid var(--border-color);
			white-space: nowrap;
		}

		.table td {
			padding: 1rem;
			border-bottom: 1px solid var(--border-color);
			word-wrap: break-word;
		}

		.table tbody tr {
			transition: all 0.2s ease;
		}

		.table tbody tr:hover {
			background: var(--bg-tertiary);
		}

		.table tbody tr.banned {
			background: rgba(255, 59, 59, 0.1);
			border-left: 3px solid var(--accent-red);
			opacity: 0.7;
		}

		.badge {
			display: inline-block;
			padding: 0.25rem 0.75rem;
			border-radius: 20px;
			font-size: 0.75rem;
			font-weight: 600;
			white-space: nowrap;
		}

		.badge-success {
			background: rgba(0, 230, 118, 0.2);
			color: var(--accent-green);
		}

		.badge-danger {
			background: rgba(255, 59, 59, 0.2);
			color: var(--accent-red);
		}

		.badge-warning {
			background: rgba(255, 184, 0, 0.2);
			color: var(--accent-yellow);
		}

		.badge-info {
			background: rgba(0, 212, 255, 0.2);
			color: var(--accent-cyan);
		}

		.badge-purple {
			background: rgba(124, 58, 237, 0.2);
			color: var(--accent-purple);
		}

		.modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.8);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 1000;
			opacity: 0;
			visibility: hidden;
			transition: all 0.3s ease;
			backdrop-filter: blur(4px);
		}

		.modal-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		.modal {
			background: var(--bg-secondary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			max-width: 600px;
			width: 90%;
			max-height: 90vh;
			overflow-y: auto;
			transform: scale(0.9);
			transition: all 0.3s ease;
		}

		.modal-overlay.active .modal {
			transform: scale(1);
		}

		.modal-header {
			padding: 1.5rem;
			border-bottom: 1px solid var(--border-color);
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.modal-title {
			font-size: 1.25rem;
			font-weight: 600;
		}

		.modal-close {
			background: none;
			border: none;
			color: var(--text-secondary);
			font-size: 1.5rem;
			cursor: pointer;
		}

		.modal-close:hover {
			color: var(--text-primary);
		}

		.modal-body {
			padding: 1.5rem;
			max-height: calc(90vh - 150px);
			overflow-y: auto;
		}

		.modal-footer {
			padding: 1.5rem;
			border-top: 1px solid var(--border-color);
			display: flex;
			justify-content: flex-end;
			gap: 1rem;
		}

		.form-group {
			margin-bottom: 1.5rem;
		}

		.form-label {
			display: block;
			margin-bottom: 0.5rem;
			font-size: 0.875rem;
			font-weight: 600;
			color: var(--text-secondary);
		}

		.form-input,
		.form-select {
			width: 100%;
			padding: 0.75rem 1rem;
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			color: var(--text-primary);
			font-size: 0.875rem;
			transition: all 0.3s ease;
		}

		.form-input:focus,
		.form-select:focus {
			outline: none;
			border-color: var(--accent-blue);
		}

		.form-input:read-only {
			opacity: 0.7;
			cursor: default;
		}

		.empty-state {
			text-align: center;
			padding: 3rem 1rem;
			color: var(--text-secondary);
		}

		.empty-state i {
			font-size: 3rem;
			margin-bottom: 1rem;
			opacity: 0.5;
		}

		.alert {
			padding: 1rem;
			border-radius: var(--radius);
			margin-bottom: 1rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.alert-success {
			background: rgba(0, 230, 118, 0.2);
			border: 1px solid var(--accent-green);
			color: var(--accent-green);
		}

		.alert-error {
			background: rgba(255, 59, 59, 0.2);
			border: 1px solid var(--accent-red);
			color: var(--accent-red);
		}

		/* Responsive table with data labels */
		@media (max-width: 768px) {
			.table thead {
				display: none;
			}

			.table tbody tr {
				display: block;
				margin-bottom: 1rem;
				border: 1px solid var(--border-color);
				border-radius: var(--radius);
				padding: 1rem;
			}

			.table tbody tr.banned {
				border-left: 3px solid var(--accent-red);
			}

			.table tbody td {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 0.5rem 0;
				border-bottom: 1px solid var(--border-color);
			}

			.table tbody td:last-child {
				border-bottom: none;
			}

			.table tbody td::before {
				content: attr(data-label);
				font-weight: 600;
				color: var(--text-secondary);
				margin-right: 1rem;
			}

			.action-buttons {
				margin-left: auto;
			}
		}

		@media (max-width: 480px) {
			.container {
				padding: 1rem;
			}

			.stats-grid {
				grid-template-columns: 1fr;
			}

			.section-header {
				flex-direction: column;
				align-items: flex-start;
			}

			.filters {
				flex-direction: column;
			}

			.search-box {
				width: 100%;
			}
		}
	</style>
</head>

<body>
	<div id="app">
		<!-- Header -->
		<header class="header">
			<div class="header-content">
				<div class="logo">
					<i class="fas fa-chart-line"></i>
					<span>OrizonPlus</span>
				</div>
				<nav class="nav-menu" :class="{ active: menuOpen }">
					<a href="index.php" class="nav-link">
						<i class="fas fa-folder-open"></i>
						<span>Projets</span>
					</a>
					<a href="expenses.php" class="nav-link">
						<i class="fas fa-receipt"></i>
						<span>Dépenses</span>
					</a>
					<a href="users.php" class="nav-link active">
						<i class="fas fa-users"></i>
						<span>Utilisateurs</span>
					</a>

					<li v-if="user_role=='admin'">
						<a href="notifications.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-bell"></i> Notifications
							<span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
						</a>
					</li>
					<li>
						<a href="api/index.php?action=logout" class="nav-link" @click="closeMobileMenu" style="color: var(--accent-red);">
							<i class="fas fa-sign-out-alt"></i> Déconnexion
						</a>
					</li>
				</nav>
				<button class="hamburger-btn" @click="menuOpen = !menuOpen">
					<i class="fas fa-bars"></i>
				</button>
			</div>
		</header>

		<!-- Main Content -->
		<main class="container">
			<!-- Statistics -->
			<p style="margin-bottom: 5px">
				Bonjour <?= ucfirst($_SESSION['user_name'])  ?>, Vous êtes connecté à votre compte <strong> {{ user_role }} </strong>
			</p>
			<div class="stats-grid">
				<div class="stat-card">
					<div class="stat-header">
						<span class="stat-label">Total Utilisateurs</span>
						<div class="stat-icon" style="background: rgba(0, 112, 243, 0.2); color: var(--accent-blue);">
							<i class="fas fa-users"></i>
						</div>
					</div>
					<div class="stat-value">{{ users.length }}</div>
				</div>



				<div class="stat-card">
					<div class="stat-header">
						<span class="stat-label">Utilisateurs Actifs</span>
						<div class="stat-icon" style="background: rgba(0, 230, 118, 0.2); color: var(--accent-green);">
							<i class="fas fa-check-circle"></i>
						</div>
					</div>
					<div class="stat-value">{{ activeCount }}</div>
				</div>
			</div>

			<!-- Alerts -->
			<div v-if="alert.show" :class="['alert', alert.type === 'success' ? 'alert-success' : 'alert-error']">
				<i :class="alert.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
				<span>{{ alert.message }}</span>
			</div>

			<!-- Users List -->
			<div class="section-card">
				<div class="section-header">
					<h2 class="section-title">
						<i class="fas fa-users"></i>
						Liste des Utilisateurs
					</h2>
					<button class="btn btn-primary" @click="openCreateModal">
						<i class="fas fa-plus"></i>
						Ajouter un utilisateur
					</button>
				</div>
				<div class="section-content">
					<!-- Filters -->
					<div class="filters">
						<div class="search-box">
							<i class="fas fa-search"></i>
							<input type="text" class="search-input" placeholder="Rechercher un utilisateur..."
								v-model="searchQuery" @input="filterUsers">
						</div>
						<select class="filter-select" v-model="roleFilter" @change="filterUsers">
							<option value="">Tous les rôles</option>
							<option value="admin">Admin</option>
							<option value="utilisateur">Utilisateur</option>
							<option value="consultant">Consultant</option>
						</select>
						<select class="filter-select" v-model="statusFilter" @change="filterUsers">
							<option value="">Tous les statuts</option>
							<option value="Actif">Actif</option>
							<option value="Banni">Banni</option>
						</select>
					</div>

					<!-- Users Table -->
					<div class="table-container">
						<table class="table" v-if="filteredUsers.length > 0">
							<thead>
								<tr>
									<th>ID</th>
									<th>Nom</th>
									<th>Rôle</th>
									<th>Statut</th>
									<th>Date de création</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="user in filteredUsers" :key="user.id"
									:class="{ 'banned': user.status === 'Banni' }">
									<td data-label="ID">{{ user.id }}</td>
									<td data-label="Nom">{{ user.name }}</td>
									<td data-label="Rôle">
										<span :class="getRoleBadgeClass(user.role)" class="badge">
											{{ user.role }}
										</span>
									</td>
									<td data-label="Statut">
										<span :class="getStatusBadgeClass(user.status)" class="badge">
											{{ user.status }}
										</span>
									</td>
									<td data-label="Date de création">{{ formatDate(user.created_at) }}</td>
									<td data-label="Actions">
										<div class="action-buttons">
											<button class="btn btn-sm btn-primary" @click="openEditModal(user)"
												title="Modifier">
												<i class="fas fa-edit"></i>
											</button>
											<button v-if="user.status === 'Actif'" class="btn btn-sm btn-danger"
												@click="confirmBanUser(user)" title="Bannir">
												<i class="fas fa-ban"></i>
											</button>
											<button v-else class="btn btn-sm btn-success"
												@click="confirmUnbanUser(user)" title="Débannir">
												<i class="fas fa-check"></i>
											</button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>

						<!-- Empty State -->
						<div v-else class="empty-state">
							<i class="fas fa-user-slash"></i>
							<p>Aucun utilisateur trouvé</p>
						</div>
					</div>
				</div>
			</div>
		</main>

		<!-- Create User Modal -->
		<div class="modal-overlay" :class="{ active: showCreateModal }" @click.self="closeCreateModal">
			<div class="modal">
				<div class="modal-header">
					<h3 class="modal-title">Ajouter un nouvel utilisateur</h3>
					<button class="modal-close" @click="closeCreateModal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="modal-body">
					<form @submit.prevent="createUser">
						<div class="form-group">
							<label class="form-label">Nom d'utilisateur *</label>
							<input type="text" class="form-input" v-model="newUser.name" required
								placeholder="Entrez le nom d'utilisateur">
						</div>
						<div class="form-group">
							<label class="form-label">Mot de passe *</label>
							<input type="password" class="form-input" v-model="newUser.password" required
								placeholder="Entrez le mot de passe" minlength="6">
						</div>
						<div class="form-group">
							<label class="form-label">Rôle *</label>
							<select class="form-select" v-model="newUser.role" required>
								<option value="">Sélectionnez un rôle</option>
								<option value="admin">Admin</option>
								<option value="utilisateur">Utilisateur</option>
								<option value="consultant">Consultant</option>
							</select>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="closeCreateModal">Annuler</button>
					<button type="button" class="btn btn-primary" @click="createUser" :disabled="isSubmitting">
						<i class="fas fa-plus"></i>
						{{ isSubmitting ? 'Création...' : 'Créer' }}
					</button>
				</div>
			</div>
		</div>

		<!-- Edit User Modal -->
		<div class="modal-overlay" :class="{ active: showEditModal }" @click.self="closeEditModal">
			<div class="modal">
				<div class="modal-header">
					<h3 class="modal-title">Modifier l'utilisateur</h3>
					<button class="modal-close" @click="closeEditModal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="modal-body">
					<form @submit.prevent="updateUser">
						<div class="form-group">
							<label class="form-label">Nom d'utilisateur</label>
							<input type="text" class="form-input" v-model="editUser.name" readonly>
						</div>
						<div class="form-group">
							<label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
							<input type="password" class="form-input" v-model="editUser.password"
								placeholder="Entrez le nouveau mot de passe" minlength="6">
						</div>
						<div class="form-group">
							<label class="form-label">Rôle *</label>
							<select class="form-select" v-model="editUser.role" required>
								<option value="admin">Admin</option>
								<option value="utilisateur">Utilisateur</option>
								<option value="consultant">Consultant</option>
							</select>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="closeEditModal">Annuler</button>
					<button type="button" class="btn btn-primary" @click="updateUser" :disabled="isSubmitting">
						<i class="fas fa-save"></i>
						{{ isSubmitting ? 'Modification...' : 'Modifier' }}
					</button>
				</div>
			</div>
		</div>

		<!-- Confirmation Modal -->
		<div class="modal-overlay" :class="{ active: showConfirmModal }" @click.self="closeConfirmModal">
			<div class="modal">
				<div class="modal-header">
					<h3 class="modal-title">{{ confirmModal.title }}</h3>
					<button class="modal-close" @click="closeConfirmModal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="modal-body">
					<p>{{ confirmModal.message }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" @click="closeConfirmModal">Annuler</button>
					<button type="button" :class="['btn', confirmModal.type === 'ban' ? 'btn-danger' : 'btn-success']"
						@click="confirmModal.action" :disabled="isSubmitting">
						<i :class="confirmModal.type === 'ban' ? 'fas fa-ban' : 'fas fa-check'"></i>
						{{ isSubmitting ? 'Traitement...' : 'Confirmer' }}
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<footer class="footer">
		<div class="footer-content">
			<div class="footer-bottom">
				<div class="footer-info">
					© 2026 OrizonPlus • système de gestion | Version 1.0.0 •
				</div>
				<div class="footer-stats">
					<div class="footer-info">
						<p class="text-center text-secondary small text-center mt-4"
							style="text-align: center">
							Built with Blood, Sweat and Tears by
							<a class="text text-secondary"
								style="text-decoration: none; font-weight: bold; color: white;"
								href="https://rachad-alabi-adekambi.github.io/portfolio/">RA</a>
						</p>
					</div>
				</div>
			</div>
	</footer>

	<script>
		const {
			createApp
		} = Vue;

		createApp({
			data() {
				return {
					// Configuration de l'API
					baseUrl: 'api/index.php?action=', // Modifiez cette URL selon votre configuration
					user_name: '<?php echo $_SESSION["user_name"] ?>',
					user_role: '<?php echo $_SESSION["user_role"] ?? "user"; ?>',
					user_id: <?php echo intval($_SESSION["user_id"] ?? 0); ?>,

					// Menu mobile
					menuOpen: false,

					unreadCount: 0,

					// Users data
					users: [],
					filteredUsers: [],

					// Filters
					searchQuery: '',
					roleFilter: '',
					statusFilter: '',

					// Modals
					showCreateModal: false,
					showEditModal: false,
					showConfirmModal: false,

					// Forms
					newUser: {
						name: '',
						password: '',
						role: ''
					},
					editUser: {
						id: null,
						name: '',
						password: '',
						role: ''
					},
					confirmModal: {
						title: '',
						message: '',
						action: null,
						type: ''
					},

					// Loading states
					isLoading: false,
					isSubmitting: false,

					// Alert
					alert: {
						show: false,
						type: 'success',
						message: ''
					}
				};
			},
			computed: {
				adminCount() {
					return this.users.filter(u => u.role === 'admin').length;
				},
				activeCount() {
					return this.users.filter(u => u.status === 'Actif').length;
				},
				bannedCount() {
					return this.users.filter(u => u.status === 'Banni').length;
				}
			},

			methods: {
				async fetchUsers() {
					try {
						this.isLoading = true;
						const response = await fetch(this.baseUrl + 'getUsers');
						const result = await response.json();

						if (result.success) {
							this.users = result.data;
							this.filterUsers();
						} else {
							this.showAlert('error', result.message || 'Erreur lors du chargement des utilisateurs');
						}
					} catch (error) {
						console.error('Erreur:', error);
						this.showAlert('error', 'Erreur de connexion au serveur');
					} finally {
						this.isLoading = false;
					}
				},

				async fetchNotifications() {
					try {
						const response = await fetch(this.baseUrl + 'getNotifications');
						const data = await response.json();
						if (!data.success) return;

						const notifications = data.data || [];

						if (this.user_role === 'admin') {
							// Admin : notifications non lues avec user_name = 'admin'
							this.unreadCount = notifications.filter(n =>
								n.is_read == 0 && n.user_name === 'admin'
							).length;
						} else {
							// Utilisateur : notifications non lues avec son propre user_id
							this.unreadCount = notifications.filter(n =>
								n.is_read == 0 && n.user_id == this.user_id
							).length;
						}

						console.log(this.user_role);
					} catch (error) {
						console.error('[parameters] Erreur fetchNotifications:', error);
					}
				},

				filterUsers() {
					let filtered = [...this.users];

					// Filter by search query
					if (this.searchQuery) {
						const query = this.searchQuery.toLowerCase();
						filtered = filtered.filter(user =>
							user.name.toLowerCase().includes(query) ||
							user.role.toLowerCase().includes(query)
						);
					}

					// Filter by role
					if (this.roleFilter) {
						filtered = filtered.filter(user => user.role === this.roleFilter);
					}

					// Filter by status
					if (this.statusFilter) {
						filtered = filtered.filter(user => user.status === this.statusFilter);
					}

					this.filteredUsers = filtered;
				},

				openCreateModal() {
					this.newUser = {
						name: '',
						password: '',
						role: ''
					};
					this.showCreateModal = true;
				},

				closeCreateModal() {
					this.showCreateModal = false;
				},

				async createUser() {
					if (!this.newUser.name || !this.newUser.password || !this.newUser.role) {
						alert('Veuillez remplir tous les champs');
						return;
					}

					try {
						this.isSubmitting = true;

						const route = this.baseUrl + 'createUser';
						const payload = JSON.stringify(this.newUser);

						console.log('===== CREATE USER =====');
						console.log('Route:', route);
						console.log('Payload:', this.newUser);
						console.log('Payload (JSON):', payload);

						const response = await fetch(route, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json'
							},
							body: payload
						});

						console.log('HTTP Status:', response.status);

						// Toujours essayer de lire le JSON
						const result = await response.json();
						console.log('Server Response:', result);

						if (!response.ok) {
							// 🔥 Affiche le message exact du backend
							alert(result.message || 'Erreur serveur');
							return;
						}

						if (result.success) {
							alert('Utilisateur créé avec succès');
							this.closeCreateModal();
							await this.fetchUsers();
						} else {
							alert(result.message || 'Erreur lors de la création');
						}

					} catch (error) {
						console.error('Fetch Error:', error);
						alert('Erreur de connexion au serveur');
					} finally {
						this.isSubmitting = false;
					}
				},


				openEditModal(user) {
					this.editUser = {
						id: user.id,
						name: user.name,
						password: '',
						role: user.role
					};
					this.showEditModal = true;
				},

				closeEditModal() {
					this.showEditModal = false;
				},

				async updateUser() {
					if (!this.editUser.role) {
						alert('Veuillez sélectionner un rôle');
						return;
					}

					try {
						this.isSubmitting = true;

						const route = this.baseUrl + 'updateUser';

						const payload = {
							id: this.editUser.id,
							role: this.editUser.role
						};

						if (this.editUser.password) {
							payload.password = this.editUser.password;
						}

						const payloadJson = JSON.stringify(payload);

						// ===== DEBUG LOGS =====
						console.log('===== UPDATE USER =====');
						console.log('Route:', route);
						console.log('Payload:', payload);
						console.log('Payload (JSON):', payloadJson);

						const response = await fetch(route, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json'
							},
							body: payloadJson
						});

						console.log('HTTP Status:', response.status);

						const result = await response.json();
						console.log('Server Response:', result);

						if (!response.ok) {
							alert(result.message || 'Erreur serveur');
							return;
						}

						if (result.success) {
							alert('Utilisateur modifié avec succès');
							this.closeEditModal();
							await this.fetchUsers();
						} else {
							alert(result.message || 'Erreur lors de la modification');
						}

					} catch (error) {
						console.error('Fetch Error:', error);
						alert('Erreur de connexion au serveur');
					} finally {
						this.isSubmitting = false;
					}
				},

				confirmBanUser(user) {
					this.confirmModal = {
						title: 'Bannir l\'utilisateur',
						message: `Êtes-vous sûr de vouloir bannir l'utilisateur "${user.name}" ?`,
						action: () => this.banUser(user.id),
						type: 'ban'
					};
					this.showConfirmModal = true;
				},

				confirmUnbanUser(user) {
					this.confirmModal = {
						title: 'Débannir l\'utilisateur',
						message: `Êtes-vous sûr de vouloir débannir l'utilisateur "${user.name}" ?`,
						action: () => this.unbanUser(user.id),
						type: 'unban'
					};
					this.showConfirmModal = true;
				},

				async banUser(userId) {
					try {
						this.isSubmitting = true;
						const response = await fetch(this.baseUrl + 'banUser', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json'
							},
							body: JSON.stringify({
								id: userId
							})
						});

						const result = await response.json();

						if (result.success) {
							this.showAlert('success', 'Utilisateur banni avec succès');
							this.closeConfirmModal();
							await this.fetchUsers();
						} else {
							this.showAlert('error', result.message || 'Erreur lors du bannissement');
						}
					} catch (error) {
						console.error('Erreur:', error);
						this.showAlert('error', 'Erreur de connexion au serveur');
					} finally {
						this.isSubmitting = false;
					}
				},

				async unbanUser(userId) {
					try {
						this.isSubmitting = true;
						const response = await fetch(this.baseUrl + 'unbanUser', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json'
							},
							body: JSON.stringify({
								id: userId
							})
						});

						const result = await response.json();

						if (result.success) {
							this.showAlert('success', 'Utilisateur débanni avec succès');
							this.closeConfirmModal();
							await this.fetchUsers();
						} else {
							this.showAlert('error', result.message || 'Erreur lors du débannissement');
						}
					} catch (error) {
						console.error('Erreur:', error);
						this.showAlert('error', 'Erreur de connexion au serveur');
					} finally {
						this.isSubmitting = false;
					}
				},

				closeConfirmModal() {
					this.showConfirmModal = false;
				},

				getRoleBadgeClass(role) {
					const classes = {
						'admin': 'badge-purple',
						'utilisateur': 'badge-info',
						'consultant': 'badge-warning'
					};
					return classes[role] || 'badge-info';
				},

				getStatusBadgeClass(status) {
					return status === 'Actif' ? 'badge-success' : 'badge-danger';
				},

				formatDate(dateString) {
					const date = new Date(dateString);
					return date.toLocaleDateString('fr-FR', {
						year: 'numeric',
						month: 'long',
						day: 'numeric',
						hour: '2-digit',
						minute: '2-digit'
					});
				},

				showAlert(type, message) {
					this.alert = {
						show: true,
						type,
						message
					};

					setTimeout(() => {
						this.alert.show = false;
					}, 5000);
				}
			},
			mounted() {
				this.fetchUsers();
				this.fetchNotifications();
			}
		}).mount('#app');
	</script>
</body>

</html>