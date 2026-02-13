<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
	header("Location: ../login.php");
	exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title>Importer un fichier Excel</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

	<style>
		body {
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
			background: #0a0a0a;
			color: #ededed;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		.card {
			background: #111;
			border: 1px solid #222;
			padding: 2rem;
			border-radius: 12px;
			width: 100%;
			max-width: 420px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
		}

		.card h2 {
			margin-bottom: 1.5rem;
			text-align: center;
			font-weight: 600;
		}

		.file-input-wrapper {
			position: relative;
			margin-bottom: 1.5rem;
		}

		input[type="file"] {
			width: 100%;
			padding: 0.75rem;
			background: #1a1a1a;
			border: 1px solid #333;
			border-radius: 8px;
			color: #aaa;
		}

		input[type="file"]::file-selector-button {
			background: #0070f3;
			border: none;
			padding: 0.5rem 1rem;
			border-radius: 6px;
			color: white;
			cursor: pointer;
			margin-right: 1rem;
		}

		input[type="file"]::file-selector-button:hover {
			background: #005bd1;
		}

		.btn {
			width: 100%;
			padding: 0.75rem;
			background: #00e676;
			border: none;
			border-radius: 8px;
			font-weight: 600;
			cursor: pointer;
			transition: 0.3s;
		}

		.btn:hover {
			background: #00c853;
		}

		.message {
			margin-top: 1rem;
			padding: 0.75rem;
			border-radius: 8px;
			text-align: center;
			font-size: 0.9rem;
		}

		.success {
			background: rgba(0, 230, 118, 0.1);
			border: 1px solid #00e676;
			color: #00e676;
		}

		.error {
			background: rgba(255, 59, 59, 0.1);
			border: 1px solid #ff3b3b;
			color: #ff3b3b;
		}
	</style>
</head>

<body>

	<div class="card">
		<p style="text-align: center;">
			<a href="../index.php" style="color: white; text-align: center; text-decoration: none;">Retour à l'accueil </a>
		</p>
		<h2><i class="fas fa-file-import"></i> Importer un fichier Excel</h2>

		<form action="function_import.php" method="POST" enctype="multipart/form-data">
			<div class="file-input-wrapper">
				<input
					type="file"
					name="file"
					accept=".xlsx,.xls"
					required>
			</div>

			<button type="submit" class="btn">
				<i class="fas fa-upload"></i> Importer
			</button>
		</form>

		<?php if (isset($_GET['success'])): ?>
			<div class="message success">
				Import effectué avec succès ✅
			</div>
		<?php endif; ?>

		<?php if (isset($_GET['error'])): ?>
			<div class="message error">
				<?= htmlspecialchars($_GET['error']) ?>
			</div>
		<?php endif; ?>
	</div>

</body>

</html>