<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>OrizonPlus • Paramètres</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
	<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

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

		.page-title {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 2rem;
			display: flex;
			align-items: center;
			gap: 1rem;
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

		.form-group {
			margin-bottom: 1.5rem;
		}

		.form-group:last-child {
			margin-bottom: 0;
		}

		.form-label {
			display: block;
			margin-bottom: 0.5rem;
			font-size: 0.875rem;
			font-weight: 600;
			color: var(--text-secondary);
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.form-input {
			width: 100%;
			padding: 0.75rem 1rem;
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			color: var(--text-primary);
			font-size: 0.875rem;
			transition: all 0.3s ease;
		}

		.password-wrapper {
			position: relative;
			display: flex;
			align-items: center;
		}

		.password-wrapper .form-input {
			padding-right: 3rem;
		}

		.password-toggle {
			position: absolute;
			right: 0.85rem;
			background: none;
			border: none;
			color: var(--text-secondary);
			cursor: pointer;
			font-size: 1rem;
			padding: 0;
			line-height: 1;
			transition: color 0.2s ease;
		}

		.password-toggle:hover {
			color: var(--text-primary);
		}

		.form-input:focus {
			outline: none;
			border-color: var(--accent-blue);
			box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
		}

		.form-input::placeholder {
			color: var(--text-secondary);
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
			box-shadow: var(--shadow-lg);
		}

		.btn-primary:disabled {
			opacity: 0.5;
			cursor: not-allowed;
			transform: none;
		}

		.btn-secondary {
			background: var(--bg-tertiary);
			color: var(--text-primary);
			border: 1px solid var(--border-color);
		}

		.btn-secondary:hover {
			background: var(--border-color);
			transform: translateY(-2px);
		}

		.button-group {
			display: flex;
			gap: 1rem;
			flex-wrap: wrap;
			margin-top: 2rem;
		}

		.alert {
			padding: 1rem 1.5rem;
			border-radius: var(--radius);
			display: flex;
			align-items: flex-start;
			gap: 1rem;
			animation: slideDown 0.3s ease;

			/* Toujours visible — fixé en haut de l'écran */
			position: fixed;
			top: 1.25rem;
			left: 50%;
			transform: translateX(-50%);
			z-index: 9999;
			min-width: 320px;
			max-width: 600px;
			width: calc(100% - 2rem);
			box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
		}

		@keyframes slideDown {
			from {
				opacity: 0;
				transform: translateX(-50%) translateY(-20px);
			}

			to {
				opacity: 1;
				transform: translateX(-50%) translateY(0);
			}
		}

		.alert-success {
			background: #0a3d20;
			border: 1px solid var(--accent-green);
			color: #b8f5d0;
		}

		.alert-danger {
			background: #3d0a0a;
			border: 1px solid var(--accent-red);
			color: #f5b8b8;
		}

		.alert-icon {
			font-size: 1.2rem;
			flex-shrink: 0;
			margin-top: 0.2rem;
		}

		.alert-message {
			flex: 1;
		}

		.alert-close {
			background: none;
			border: none;
			color: inherit;
			cursor: pointer;
			font-size: 1.2rem;
			padding: 0;
			flex-shrink: 0;
		}

		.alert-close:hover {
			opacity: 0.8;
		}

		.info-box {
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-left: 3px solid var(--accent-blue);
			border-radius: var(--radius);
			padding: 1rem;
			margin-bottom: 1.5rem;
			font-size: 0.875rem;
		}

		.info-box.warning {
			border-left-color: var(--accent-yellow);
			background: rgba(255, 184, 0, 0.1);
		}

		.password-requirements {
			background: var(--bg-tertiary);
			border: 1px solid var(--border-color);
			border-radius: var(--radius);
			padding: 1rem;
			margin-top: 1rem;
			font-size: 0.875rem;
		}

		.password-requirements h4 {
			color: var(--text-secondary);
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 0.75rem;
			font-size: 0.75rem;
		}

		.password-requirements ul {
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 0.5rem;
		}

		.password-requirements li {
			color: var(--text-secondary);
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.password-requirements li i {
			font-size: 0.75rem;
		}

		.password-requirements .requirement-met {
			color: var(--accent-green);
		}

		.grid-2-col {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
			gap: 2rem;
		}

		@media (max-width: 768px) {
			.container {
				padding: 1rem;
			}

			.page-title {
				font-size: 1.5rem;
				margin-bottom: 1.5rem;
			}

			.button-group {
				flex-direction: column;
			}

			.btn {
				width: 100%;
				justify-content: center;
			}
		}
	</style>
</head>

<body>
	<div id="app">
		<!-- Header -->
		<header class="header no-print">
			<div class="header-content">
				<div class="logo">
					<a href="index.php">
						<img src="logo.png" alt="OrizonPlus">
					</a>
				</div>

				<button class="hamburger-btn" @click="toggleMobileMenu" aria-label="Toggle menu">
					<i class="fas" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
				</button>

				<ul class="nav-menu" :class="{ active: mobileMenuOpen }">
					<li>
						<a href="index.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-folder-open"></i> Projets
						</a>
					</li>
					<li>
						<a href="expenses.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-receipt"></i> Dépenses
						</a>
					</li>
					<li v-if="user_role === 'admin'">
						<a href="users.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-users"></i> Utilisateurs
						</a>
					</li>
					<li v-if="user_role === 'admin' || user_role === 'utilisateur'">
						<a href="notifications.php" class="nav-link" @click="closeMobileMenu">
							<i class="fas fa-bell"></i> Notifications
						</a>
					</li>
					<li v-if="user_role === 'utilisateur' || user_role === 'consultant'">
						<a href="parameters.php" class="nav-link active" @click="closeMobileMenu">
							<i class="fas fa-cog"></i> Paramètres
						</a>
					</li>
					<li>
						<a href="api/index.php?action=logout" class="nav-link" @click="closeMobileMenu" style="color: var(--accent-red);">
							<i class="fas fa-sign-out-alt"></i> Déconnexion
						</a>
					</li>
				</ul>
			</div>
		</header>

		<!-- Main Content -->
		<main class="container">
			<div class="page-title">
				<i class="fas fa-shield-alt"></i>
				Paramètres de Sécurité
			</div>

			<!-- Alert Messages -->
			<div v-if="alert.message" class="alert" :class="`alert-${alert.type}`">
				<i :class="['fas', 'alert-icon', alert.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle']"></i>
				<div class="alert-message">{{ alert.message }}</div>
				<button class="alert-close" @click="closeAlert">
					<i class="fas fa-times"></i>
				</button>
			</div>

			<!-- Password Change Section -->
			<div class="section-card">
				<div class="section-header">
					<div class="section-title">
						<i class="fas fa-lock"></i>
						Modifier le Mot de Passe
					</div>
				</div>
				<div class="section-content">
					<div class="info-box warning">
						<i class="fas fa-info-circle"></i>
						Pour votre sécurité, veuillez choisir un mot de passe fort et unique.
					</div>

					<form @submit.prevent="changePassword">
						<div class="form-group">
							<label class="form-label" for="old_password">
								<i class="fas fa-key"></i> Ancien Mot de Passe
							</label>
							<div class="password-wrapper">
								<input
									:type="showOldPassword ? 'text' : 'password'"
									id="old_password"
									v-model="passwordForm.oldPassword"
									class="form-input"
									placeholder="Entrez votre mot de passe actuel"
									required>
								<button type="button" class="password-toggle" @click="showOldPassword = !showOldPassword" tabindex="-1">
									<i class="fas" :class="showOldPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
								</button>
							</div>
						</div>

						<div class="form-group">
							<label class="form-label" for="new_password">
								<i class="fas fa-lock-open"></i> Nouveau Mot de Passe
							</label>
							<div class="password-wrapper">
								<input
									:type="showNewPassword ? 'text' : 'password'"
									id="new_password"
									v-model="passwordForm.newPassword"
									@input="checkPasswordStrength"
									class="form-input"
									placeholder="Entrez votre nouveau mot de passe"
									required>
								<button type="button" class="password-toggle" @click="showNewPassword = !showNewPassword" tabindex="-1">
									<i class="fas" :class="showNewPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
								</button>
							</div>
						</div>

						<div class="form-group">
							<label class="form-label" for="confirm_password">
								<i class="fas fa-check-circle"></i> Confirmer le Mot de Passe
							</label>
							<div class="password-wrapper">
								<input
									:type="showConfirmPassword ? 'text' : 'password'"
									id="confirm_password"
									v-model="passwordForm.confirmPassword"
									@input="checkPasswordStrength"
									class="form-input"
									placeholder="Confirmez votre nouveau mot de passe"
									required>
								<button type="button" class="password-toggle" @click="showConfirmPassword = !showConfirmPassword" tabindex="-1">
									<i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
								</button>
							</div>
						</div>

						<div class="password-requirements">
							<h4>Critères de Sécurité</h4>
							<ul>
								<li :class="{ 'requirement-met': passwordRequirements.length }">
									<i class="fas" :class="passwordRequirements.length ? 'fa-check-circle' : 'fa-circle-notch'"></i>
									Au moins 6 caractères
								</li>
								<li :class="{ 'requirement-met': passwordRequirements.match }">
									<i class="fas" :class="passwordRequirements.match ? 'fa-check-circle' : 'fa-circle-notch'"></i>
									Les mots de passe correspondent
								</li>
							</ul>
						</div>

						<div class="button-group">
							<button type="submit" class="btn btn-primary" :disabled="!isFormValid || isSaving">
								<i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
								{{ isSaving ? 'Modification...' : 'Modifier le Mot de Passe' }}
							</button>
							<a href="index.php" class="btn btn-secondary">
								<i class="fas fa-arrow-left"></i> Retour
							</a>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>

	<script>
		const API_BASE_URL = 'api/index.php';
		const {
			createApp
		} = Vue;

		createApp({
			data() {
				return {
					user_id: <?php echo $user_id; ?>,
					user_role: '<?php echo $_SESSION["user_role"] ?? "utilisateur"; ?>',
					mobileMenuOpen: false,
					passwordForm: {
						oldPassword: '',
						newPassword: '',
						confirmPassword: ''
					},
					passwordRequirements: {
						length: false,
						match: false
					},
					alert: {
						message: '',
						type: '' // success | danger
					},
					isSaving: false,
					showOldPassword: false,
					showNewPassword: false,
					showConfirmPassword: false
				};
			},
			computed: {
				isFormValid() {
					return this.passwordRequirements.length &&
						this.passwordRequirements.match &&
						this.passwordForm.oldPassword.length > 0;
				}
			},
			methods: {

				toggleMobileMenu() {
					this.mobileMenuOpen = !this.mobileMenuOpen;
				},

				closeMobileMenu() {
					this.mobileMenuOpen = false;
				},

				checkPasswordStrength() {
					this.passwordRequirements.length =
						this.passwordForm.newPassword.length >= 6;

					this.passwordRequirements.match =
						this.passwordForm.newPassword.length > 0 &&
						this.passwordForm.confirmPassword.length > 0 &&
						this.passwordForm.newPassword === this.passwordForm.confirmPassword;
				},

				async changePassword() {

					this.isSaving = true;

					try {
						const formData = new FormData();
						const route = `${API_BASE_URL}?action=updatePassword`;

						formData.append('old_password', this.passwordForm.oldPassword);
						formData.append('new_password', this.passwordForm.newPassword);
						formData.append('confirm_password', this.passwordForm.confirmPassword);

						const response = await fetch(route, {
							method: 'POST',
							body: formData
						});

						const data = await response.json();

						// 🔴 Si erreur backend → afficher exactement le message retourné
						if (!data.success) {
							this.showAlert(data.message, "danger");
							return;
						}

						// 🟢 Succès
						this.showAlert(data.message, "success");

						this.passwordForm = {
							oldPassword: '',
							newPassword: '',
							confirmPassword: ''
						};

						this.passwordRequirements = {
							length: false,
							match: false
						};

						this.showOldPassword = false;
						this.showNewPassword = false;
						this.showConfirmPassword = false;

					} catch (error) {
						this.showAlert("Erreur réseau.", "danger");
					} finally {
						this.isSaving = false;
					}
				},

				showAlert(message, type) {
					this.alert.message = message;
					this.alert.type = type;

					setTimeout(() => {
						this.closeAlert();
					}, 5000);
				},

				closeAlert() {
					this.alert.message = '';
					this.alert.type = '';
				},

				capitalizeRole(role) {
					if (!role) return '';
					return role.charAt(0).toUpperCase() + role.slice(1);
				}
			}
		}).mount('#app');
	</script>
</body>

</html>