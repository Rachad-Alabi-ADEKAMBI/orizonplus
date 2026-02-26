<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (
	!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin' &&
	($_SESSION['user_role'] ?? '') !== 'utilisateur'
) {
	header("Location: login.php");
	exit;
}

$user_role = $_SESSION['user_role']  ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>OrizonPlus • Notifications</title>
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

		.page-header {
			margin-bottom: 2rem;
		}

		.page-title {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 0.5rem;
			display: flex;
			align-items: center;
			gap: 0.75rem;
		}

		.page-title i {
			background: var(--gradient-2);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
		}

		.page-subtitle {
			color: var(--text-secondary);
			font-size: 0.95rem;
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

		.search-input:focus {
			outline: none;
			border-color: var(--accent-blue);
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
			cursor: pointer;
			user-select: none;
		}

		.table th:hover {
			color: var(--text-primary);
		}

		.table th i {
			margin-left: 0.25rem;
			font-size: 0.75rem;
		}

		.table td {
			padding: 1rem;
			border-bottom: 1px solid var(--border-color);
		}

		.table tbody tr {
			transition: all 0.2s ease;
		}

		.table tbody tr:hover {
			background: var(--bg-tertiary);
		}

		.notification-description {
			line-height: 1.5;
			color: var(--text-primary);
		}

		.notification-date {
			color: var(--text-secondary);
			font-size: 0.875rem;
			white-space: nowrap;
		}

		.notification-id {
			color: var(--text-secondary);
			font-weight: 600;
			font-size: 0.875rem;
		}

		.badge {
			display: inline-block;
			padding: 0.25rem 0.75rem;
			border-radius: 20px;
			font-size: 0.75rem;
			font-weight: 600;
			white-space: nowrap;
		}

		.badge-info {
			background: rgba(0, 212, 255, 0.2);
			color: var(--accent-cyan);
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

		.pagination {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 0.5rem;
			margin-top: 1.5rem;
			flex-wrap: wrap;
		}

		.pagination button {
			padding: 0.5rem 1rem;
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			color: var(--text-primary);
			cursor: pointer;
			font-size: 0.875rem;
			transition: all 0.3s ease;
			min-width: 40px;
		}

		.pagination button:hover:not(:disabled) {
			background: var(--accent-blue);
			border-color: var(--accent-blue);
		}

		.pagination button.active {
			background: var(--accent-blue);
			border-color: var(--accent-blue);
		}

		.pagination button:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}

		.pagination-info {
			color: var(--text-secondary);
			font-size: 0.875rem;
			padding: 0.5rem 1rem;
		}

		.loading {
			text-align: center;
			padding: 2rem;
			color: var(--text-secondary);
		}

		.loading i {
			font-size: 2rem;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			from {
				transform: rotate(0deg);
			}

			to {
				transform: rotate(360deg);
			}
		}

		.error-message {
			background: rgba(255, 59, 59, 0.1);
			border: 1px solid var(--accent-red);
			border-radius: var(--radius);
			padding: 1rem;
			color: var(--accent-red);
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		/* Responsive table pour mobile avec data-label */
		@media (max-width: 768px) {
			.container {
				padding: 1rem;
			}

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

			.table tbody tr:hover {
				background: var(--bg-secondary);
			}

			.table td {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				padding: 0.75rem 0;
				border-bottom: 1px solid var(--border-color);
				text-align: right;
			}

			.table td:last-child {
				border-bottom: none;
			}

			.table td::before {
				content: attr(data-label);
				font-weight: 600;
				color: var(--text-secondary);
				text-align: left;
				flex-shrink: 0;
				margin-right: 1rem;
			}

			.notification-description {
				text-align: right;
				max-width: 60%;
			}

			.pagination {
				gap: 0.25rem;
			}

			.pagination button {
				padding: 0.4rem 0.8rem;
				font-size: 0.8rem;
				min-width: 36px;
			}

			.pagination-info {
				width: 100%;
				text-align: center;
				order: -1;
				margin-bottom: 0.5rem;
			}
		}

		@media (max-width: 480px) {
			.page-title {
				font-size: 1.5rem;
			}

			.search-box {
				min-width: 100%;
			}

			.notification-description {
				max-width: 100%;
				word-break: break-word;
			}

			.table td::before {
				min-width: 80px;
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
					OrizonPlus
				</div>
				<ul class="nav-menu" :class="{ active: menuOpen }">
					<li><a href="index.php" class="nav-link"><i class="fas fa-folder-open"></i> Projets</a></li>
					<li><a href="expenses.php" class="nav-link"><i class="fas fa-wallet"></i> Dépenses</a></li>
					<li v-if="user_role=='admin'">
						<a href="users.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-users"></i> Utilisateurs
						</a>
					</li>
					<li><a href="suppliers.php" class="nav-link"> <i class="fas fa-truck"></i> Fournisseurs</a></li>

					<li><a href="notifications.php" class="nav-link active"><i class="fas fa-bell"></i> Notifications
						</a></li>


					<li v-if="user_role=='utilisateur' || user_role=='consultant'">
						<a href="parameters.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-cog"></i> Paramètres</a>
					</li>


					<li>
						<a href="api/index.php?action=logout" class="nav-link" @click="closeMobileMenu" style="color: var(--accent-red);">
							<i class="fas fa-sign-out-alt"></i> Déconnexion
						</a>
					</li>
				</ul>
				<button class="hamburger-btn" @click="menuOpen = !menuOpen">
					<i class="fas" :class="menuOpen ? 'fa-times' : 'fa-bars'"></i>
				</button>
			</div>
		</header>

		<!-- Main Container -->
		<div class="container">
			<!-- Page Header -->
			<div class="page-header">
				<p style="margin-bottom: 5px">
					Bonjour <?= ucfirst($_SESSION['user_name'])  ?>, Vous êtes connecté à votre compte <strong> {{ user_role }} </strong>
				</p>
			</div>

			<!-- Notifications Section -->
			<div class="section-card">
				<div class="section-header">
					<h2 class="section-title">
						<i class="fas fa-list"></i>
						Liste des notifications
					</h2>
					<span class="badge badge-info" v-if="!loading">
						{{ filteredNotifications.length }} notification(s)
					</span>
				</div>

				<div class="section-content">
					<!-- Search Bar -->
					<div class="filters">
						<div class="search-box">
							<i class="fas fa-search"></i>
							<input
								type="text"
								class="search-input"
								placeholder="Rechercher une notification..."
								v-model="searchQuery" />
						</div>
					</div>

					<!-- Loading State -->
					<div v-if="loading" class="loading">
						<i class="fas fa-spinner"></i>
						<p>Chargement des notifications...</p>
					</div>

					<!-- Error State -->
					<div v-else-if="error" class="error-message">
						<i class="fas fa-exclamation-triangle"></i>
						<span>{{ error }}</span>
					</div>

					<!-- Table -->
					<div v-else-if="paginatedNotifications.length > 0" class="table-container">
						<table class="table" v-if="user_role == 'admin'">
							<thead>
								<tr>
									<th @click="sortBy('id')">
										ID
										<i class="fas" :class="getSortIcon('id')"></i>
									</th>
									<th @click="sortBy('description')">
										Description
										<i class="fas" :class="getSortIcon('description')"></i>
									</th>
									<th @click="sortBy('created_at')">
										Date
										<i class="fas" :class="getSortIcon('created_at')"></i>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="notification in paginatedNotifications" :key="notification.id">
									<td data-label="ID">
										<span class="notification-id">#{{ notification.id }}</span>
									</td>
									<td data-label="Description">
										<div class="notification-description">{{ notification.description }}</div>
									</td>
									<td data-label="Date">
										<span class="notification-date">{{ formatDate(notification.created_at) }}</span>
									</td>
								</tr>
							</tbody>
						</table>

						<table class="table" v-else>
							<thead>
								<tr>
									<th @click="sortBy('description')">
										Description
										<i class="fas" :class="getSortIcon('description')"></i>
									</th>
									<th @click="sortBy('created_at')">
										Date
										<i class="fas" :class="getSortIcon('created_at')"></i>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="notification in paginatedNotifications" :key="notification.id">
									<td data-label="Description">
										<div class="notification-description">{{ notification.description }}</div>
									</td>
									<td data-label="Date">
										<span class="notification-date">{{ formatDate(notification.created_at) }}</span>
									</td>
								</tr>
							</tbody>
						</table>

						<!-- Pagination -->
						<div class="pagination">
							<span class="pagination-info">
								Page {{ currentPage }} sur {{ totalPages }} • {{ filteredNotifications.length }} résultat(s)
							</span>
							<button
								@click="goToPage(1)"
								:disabled="currentPage === 1"
								title="Première page">
								<i class="fas fa-angle-double-left"></i>
							</button>
							<button
								@click="goToPage(currentPage - 1)"
								:disabled="currentPage === 1"
								title="Page précédente">
								<i class="fas fa-angle-left"></i>
							</button>

							<button
								v-for="page in visiblePages"
								:key="page"
								@click="goToPage(page)"
								:class="{ active: currentPage === page }">
								{{ page }}
							</button>

							<button
								@click="goToPage(currentPage + 1)"
								:disabled="currentPage === totalPages"
								title="Page suivante">
								<i class="fas fa-angle-right"></i>
							</button>
							<button
								@click="goToPage(totalPages)"
								:disabled="currentPage === totalPages"
								title="Dernière page">
								<i class="fas fa-angle-double-right"></i>
							</button>
						</div>
					</div>

					<!-- Empty State -->
					<div v-else class="empty-state">
						<i class="fas fa-bell-slash"></i>
						<h3>Aucune notification trouvée</h3>
						<p>{{ searchQuery ? 'Essayez de modifier votre recherche' : 'Vous n\'avez aucune notification pour le moment' }}</p>
					</div>
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

		const API_BASE_URL = 'api/index.php';

		createApp({
			data() {
				return {
					menuOpen: false,
					API_BASE_URL: API_BASE_URL,
					user_name: '<?php echo $_SESSION["user_name"] ?>',
					user_role: '<?php echo $_SESSION["user_role"] ?? "user"; ?>',
					notifications: [],
					loading: true,
					error: null,
					searchQuery: '',
					sortKey: 'created_at',
					sortOrder: 'desc',
					currentPage: 1,
					itemsPerPage: 10
				};
			},
			computed: {
				filteredNotifications() {
					let filtered = this.notifications;

					// Search filter
					if (this.searchQuery) {
						const query = this.searchQuery.toLowerCase();
						filtered = filtered.filter(notification => {
							return (
								notification.description.toLowerCase().includes(query) ||
								notification.id.toString().includes(query) ||
								notification.created_at.toLowerCase().includes(query)
							);
						});
					}

					// Sort
					filtered.sort((a, b) => {
						let aVal = a[this.sortKey];
						let bVal = b[this.sortKey];

						// Handle date sorting
						if (this.sortKey === 'created_at') {
							aVal = new Date(aVal);
							bVal = new Date(bVal);
						}

						// Handle numeric sorting
						if (this.sortKey === 'id') {
							aVal = parseInt(aVal);
							bVal = parseInt(bVal);
						}

						if (aVal < bVal) return this.sortOrder === 'asc' ? -1 : 1;
						if (aVal > bVal) return this.sortOrder === 'asc' ? 1 : -1;
						return 0;
					});

					return filtered;
				},
				totalPages() {
					return Math.ceil(this.filteredNotifications.length / this.itemsPerPage);
				},
				paginatedNotifications() {
					const start = (this.currentPage - 1) * this.itemsPerPage;
					const end = start + this.itemsPerPage;
					return this.filteredNotifications.slice(start, end);
				},
				visiblePages() {
					const pages = [];
					const total = this.totalPages;
					const current = this.currentPage;

					// Always show first page
					if (total <= 7) {
						for (let i = 1; i <= total; i++) {
							pages.push(i);
						}
					} else {
						// Show current page and 2 pages on each side
						let start = Math.max(2, current - 1);
						let end = Math.min(total - 1, current + 1);

						pages.push(1);

						if (start > 2) {
							pages.push('...');
						}

						for (let i = start; i <= end; i++) {
							pages.push(i);
						}

						if (end < total - 1) {
							pages.push('...');
						}

						if (total > 1) {
							pages.push(total);
						}
					}

					return pages;
				}
			},
			watch: {
				searchQuery() {
					this.currentPage = 1; // Reset to first page on search
				}
			},
			methods: {
			async markNotificationsAsReaden() {
    try {
        const params = new URLSearchParams({
            action: 'markNotificationsAsReaden'
        });
        if (this.user_role !== 'admin') {
            params.append('user_id', '<?php echo $_SESSION["user_id"]; ?>');
        }

        const url = `${API_BASE_URL}?${params.toString()}`;

        console.group('📬 markNotificationsAsReaden');
        console.log('🔗 URL      :', url);
        console.log('📦 Params   :', Object.fromEntries(params));

        const response = await fetch(url);
        const raw = await response.text(); // text() d'abord pour éviter crash si pas du JSON

        console.log('📡 Status   :', response.status, response.statusText);

        let parsed = null;
        try {
            parsed = JSON.parse(raw);
            console.log('✅ Réponse  :', parsed);
        } catch {
            console.warn('⚠️ Réponse non-JSON :', raw);
        }

        console.groupEnd();

    } catch (err) {
        console.error('❌ Erreur markNotificationsAsReaden:', err);
    }
},

				async fetchNotifications() {
					this.loading = true;
					this.error = null;

					try {
						const params = new URLSearchParams({
							action: 'getNotifications'
						});
						if (this.user_role !== 'admin') {
							params.append('user_id', '<?php echo $_SESSION["user_id"]; ?>');
						}
						const response = await fetch(`${API_BASE_URL}?${params.toString()}`);
						const result = await response.json();

						if (result.success) {
							this.notifications = result.data || [];
						} else {
							this.error = result.message || 'Erreur lors du chargement des notifications';
						}
					} catch (err) {
						console.error('Erreur de récupération:', err);
						this.error = 'Erreur de connexion au serveur';
					} finally {
						this.loading = false;
					}
				},

				sortBy(key) {
					if (this.sortKey === key) {
						this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
					} else {
						this.sortKey = key;
						this.sortOrder = 'asc';
					}
					this.currentPage = 1; // Reset to first page
				},
				getSortIcon(key) {
					if (this.sortKey !== key) {
						return 'fa-sort';
					}
					return this.sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
				},
				goToPage(page) {
					if (page === '...') return;
					if (page >= 1 && page <= this.totalPages) {
						this.currentPage = page;
						// Scroll to top
						window.scrollTo({
							top: 0,
							behavior: 'smooth'
						});
					}
				},
				formatDate(dateString) {
					if (!dateString) return 'N/A';

					try {
						const date = new Date(dateString);
						const now = new Date();
						const diffTime = now - date;
						const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
						const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
						const diffMinutes = Math.floor(diffTime / (1000 * 60));

						// Relative time for recent notifications
						if (diffMinutes < 1) return 'À l\'instant';
						if (diffMinutes < 60) return `Il y a ${diffMinutes} min`;
						if (diffHours < 24) return `Il y a ${diffHours}h`;
						if (diffDays < 7) return `Il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`;

						// Otherwise show formatted date
						const options = {
							year: 'numeric',
							month: 'short',
							day: 'numeric',
							hour: '2-digit',
							minute: '2-digit'
						};
						return date.toLocaleDateString('fr-FR', options);
					} catch (err) {
						return dateString;
					}
				}
			},
			mounted() {
				this.markNotificationsAsReaden();
				this.fetchNotifications();

				// Auto-refresh every 30 seconds
				setInterval(() => {
					this.fetchNotifications();
				}, 30000);
			}
		}).mount('#app');
	</script>
</body>

</html>