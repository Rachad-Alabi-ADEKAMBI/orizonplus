<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'consultant';
$canEdit = in_array($user_role, ['admin', 'utilisateur']);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrizonPlus • Gestion des Dépenses</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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

        @media print {
            body {
                background: white;
                color: black;
            }

            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                color: black;
            }

            .no-print {
                display: none !important;
            }

            .print-footer {
                display: block !important;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                padding: 10px;
                font-size: 12px;
                border-top: 1px solid #ccc;
                background: white;
            }

            table {
                border: 1px solid #000;
                margin-bottom: 20px;
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #ccc;
                color: black !important;
                padding: 8px;
            }

            h2,
            h3 {
                color: black !important;
                page-break-after: avoid;
            }
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

        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--text-secondary);
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
        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .filter-select,
        .filter-input {
            padding: 0.75rem 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-input {
            min-width: 150px;
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
            cursor: pointer;
            user-select: none;
        }

        .table th:hover {
            color: var(--text-primary);
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

        .table tbody tr.warning {
            background: rgba(255, 184, 0, 0.1);
            border-left: 3px solid var(--accent-yellow);
        }

        .table tbody tr.danger {
            background: rgba(255, 59, 59, 0.1);
            border-left: 3px solid var(--accent-red);
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
        .form-textarea,
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
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .form-input:read-only {
            opacity: 0.7;
            cursor: default;
        }

        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-tertiary);
        }

        .file-upload-area:hover {
            border-color: var(--accent-blue);
            background: var(--bg-secondary);
        }

        .file-upload-area.dragover {
            border-color: var(--accent-cyan);
            background: rgba(0, 212, 255, 0.1);
        }

        .file-input {
            display: none;
        }

        .file-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 1rem;
            justify-content: space-between;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            min-width: 0;
        }

        .file-info i {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .file-name {
            font-size: 0.875rem;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-size {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .attached-file {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-tertiary);
            border-radius: var(--radius);
            margin-top: 0.5rem;
        }

        .attached-file i {
            font-size: 1.2rem;
        }

        .info-box {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--accent-blue);
            border-radius: var(--radius);
            padding: 1rem;
            margin-top: 1rem;
        }

        .info-box.warning {
            border-left-color: var(--accent-yellow);
            background: rgba(255, 184, 0, 0.1);
        }

        .info-box.danger {
            border-left-color: var(--accent-red);
            background: rgba(255, 59, 59, 0.1);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 350px;
        }

        .chart-container canvas {
            max-width: 100%;
            max-height: 100%;
        }

        @media (max-width: 1024px) {
            .chart-container {
                height: 350px;
                min-height: 300px;
            }
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
                min-height: 250px;
            }
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-secondary);
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
            min-width: 40px;
        }

        .pagination button:hover:not(:disabled) {
            background: var(--accent-blue);
        }

        .pagination button.active {
            background: var(--accent-blue);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive Tables with data-label */
        @media (max-width: 768px) {
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

            .filter-input,
            .search-box {
                min-width: 100%;
            }

            .modal {
                max-width: 95%;
            }

            .table-container {
                border: none;
            }

            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                padding: 0.5rem;
            }

            .table tbody tr.warning,
            .table tbody tr.danger {
                border-left-width: 3px;
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 0.5rem;
                border-bottom: 1px solid var(--border-color);
                text-align: right;
            }

            .table tbody td:last-child {
                border-bottom: none;
            }

            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-secondary);
                text-align: left;
                margin-right: 1rem;
                flex: 1;
            }

            .table tbody td .action-buttons {
                justify-content: flex-end;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                    <span>OrizonPlus</span>
                </div>
                <ul class="nav-menu" :class="{ active: menuOpen }">
                    <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="expenses.php" class="nav-link active"><i class="fas fa-wallet"></i> Dépenses</a></li>
                    <li v-if="user_role=='admin'">
                        <a href="users.php" class="nav-link" @click="closeMobileMenu">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>

                    <li v-if="user_role=='admin'">
                        <a href="notifications.php" class="nav-link" @click="closeMobileMenu">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <li><button @click="logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></li>
                </ul>
                <button class="hamburger-btn" @click="menuOpen = !menuOpen">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>

        <main class="container">
            <p style="margin-bottom: 5px">
                Bonjour <?= ucfirst($_SESSION['user_name'])  ?>, Vous êtes connecté à votre compte <strong> {{ user_role }} </strong>
            </p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Dépenses</span>
                        <div class="stat-icon" style="background: rgba(0, 112, 243, 0.2); color: var(--accent-blue);">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.totalExpenses }}</div>
                    <div class="stat-change">Enregistrées</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Montant Total</span>
                        <div class="stat-icon" style="background: rgba(255, 184, 0, 0.2); color: var(--accent-yellow);">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalAmount) }}</div>
                    <div class="stat-change">Toutes périodes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Ce Mois</span>
                        <div class="stat-icon" style="background: rgba(0, 230, 118, 0.2); color: var(--accent-green);">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.thisMonth) }}</div>
                    <div class="stat-change">{{ new Date().toLocaleDateString('fr-FR', { month: 'long' }) }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Hors Budget</span>
                        <div class="stat-icon" style="background: rgba(255, 59, 59, 0.2); color: var(--accent-red);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.overBudget }}</div>
                    <div class="stat-change">Lignes dépassées</div>
                </div>
            </div>

            <!-- Section des graphiques -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Dépenses par Projet</h3>
                    <div class="chart-container">
                        <canvas ref="projectsChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Évolution des Dépenses</h3>
                    <div class="chart-container">
                        <canvas ref="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-wallet"></i>
                        Gestion des Dépenses
                    </h2>
                    <button v-if="canEdit" @click="openExpenseModal" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle Dépense
                    </button>
                </div>

                <div class="section-content">
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="search-input" v-model="searchQuery" @input="filterExpenses"
                                style="max-width: 250px;"
                                placeholder="Rechercher...">
                        </div>
                        <select class="filter-select" v-model="projectFilter" @change="filterExpenses"
                            style="max-width: 250px;">
                            <option value="" style="max-width: 250px;">Tous les projets</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id" style="max-width: 250px;">
                                {{ project.name }}
                            </option>
                        </select>
                        <select class="filter-select" v-model="statusFilter" @change="filterExpenses"
                            style="max-width: 250px;">
                            <option value="">Tous les statuts</option>
                            <option value="ok">OK (&lt; 80%)</option>
                            <option value="warning">Attention (≥ 80%)</option>
                            <option value="over">Dépassé</option>
                        </select>
                        <input type="date" class="filter-input" v-model="dateFrom" @change="filterExpenses" style="max-width: 250px;">
                        <input type="date" class="filter-input" v-model="dateTo" @change="filterExpenses" style="max-width: 250px;">
                    </div>

                    <div class="table-container">
                        <table class="table" v-if="paginatedExpenses.length > 0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Projet</th>
                                    <th>Ligne Budgétaire</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Réalisation</th>
                                    <th>Statut</th>
                                    <th>Document</th>
                                    <th v-if="canEdit" class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in paginatedExpenses" :key="expense.id"
                                    :class="getRowClass(expense)">
                                    <td data-label="Date">{{ formatDate(expense.expense_date) }}</td>
                                    <td data-label="Projet"><strong>{{ reduceWord(expense.project_name) }}</strong></td>
                                    <td data-label="Ligne Budgétaire">{{ expense.budget_line_name }}</td>
                                    <td data-label="Description" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ expense.description || '-' }}
                                    </td>
                                    <td data-label="Montant"><strong>{{ formatCurrency(expense.amount) }}</strong></td>
                                    <td data-label="Réalisation">{{ formatCurrency(expense.spent) }}</td>
                                    <td data-label="Statut">
                                        <span class="badge"
                                            :class="getBadgeClass(expense.remaining, expense.allocated_amount)">
                                            {{ getUsagePercentage(expense) }}%
                                        </span>
                                    </td>
                                    <td data-label="Document">
                                        <button v-if="expense.document" @click="viewDocument(expense)"
                                            class="btn btn-sm btn-secondary">
                                            <i :class="getDocumentIcon(expense.document)"
                                                :style="{ color: getDocumentColor(expense.document) }"></i>
                                        </button>
                                        <span v-else style="color: var(--text-secondary);">-</span>
                                    </td>
                                    <td v-if="canEdit" class="no-print" data-label="Actions">
                                        <div class="action-buttons">
                                            <button @click="editExpense(expense)" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteExpense(expense)" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-else class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Aucune dépense trouvée</p>
                        </div>
                    </div>

                    <div class="pagination" v-if="totalPages > 1">
                        <button @click="prevPage" :disabled="currentPage === 1">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button v-for="page in totalPages" :key="page" @click="currentPage = page"
                            :class="{ active: currentPage === page }">
                            {{ page }}
                        </button>
                        <button @click="nextPage" :disabled="currentPage === totalPages">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal Ajouter/Modifier Dépense -->
        <div class="modal-overlay" :class="{ active: modals.expense }" @click.self="closeExpenseModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-wallet"></i>
                        {{ isEditMode ? 'Modifier la Dépense' : 'Nouvelle Dépense' }}
                    </h3>
                    <button class="modal-close" @click="closeExpenseModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Projet *</label>
                        <select class="form-select" v-model="expense.project_id" @change="fetchLines" required>
                            <option value="">Sélectionner un projet</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">
                                {{ reduceWord(project.name) }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ligne Budgétaire *</label>
                        <select class="form-select" v-model="expense.project_budget_line_id" @change="updateLineInfo"
                            :disabled="!expense.project_id" required>
                            <option value="">Sélectionner une ligne</option>
                            <option v-for="line in lines" :key="line.project_budget_line_id"
                                :value="line.project_budget_line_id">
                                {{ line.name }} - {{ formatCurrency(line.allocated_amount) }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date de la dépense *</label>
                        <input type="date" class="form-input" v-model="expense.expense_date" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Montant (FCFA) *</label>
                        <input type="number" class="form-input" v-model="expense.amount" step="0.01" required
                            placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-input" v-model="expense.description"
                            placeholder="Description de la dépense">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Document justificatif (PDF, PNG, JPG - Max 5Mo)</label>

                        <div v-if="isEditMode && expense.document && !selectedFile" class="attached-file">
                            <i :class="getDocumentIcon(expense.document)"
                                :style="{ color: getDocumentColor(expense.document) }"></i>
                            <span>Document existant</span>
                            <button @click="viewDocument(expense)" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Voir
                            </button>
                            <button @click="removeExistingDocument" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <div v-if="!expense.document || selectedFile" class="file-upload-area" :class="{ dragover: isDragging }"
                            @click="$refs.fileInput.click()" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
                            @drop.prevent="onFileDrop">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--accent-blue);"></i>
                            <p style="margin-top: 1rem; color: var(--text-secondary);">
                                Cliquez ou glissez un fichier ici
                            </p>
                            <input type="file" ref="fileInput" class="file-input" @change="onFileSelect"
                                accept=".pdf,.png,.jpg,.jpeg">
                        </div>

                        <div v-if="selectedFile" class="file-preview">
                            <div class="file-info">
                                <i :class="getFileIcon(selectedFile)" :style="{ color: getFileColor(selectedFile) }"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <div class="file-name">{{ selectedFile.name }}</div>
                                    <div class="file-size">{{ formatFileSize(selectedFile.size) }}</div>
                                </div>
                            </div>
                            <button @click="removeFile" class="btn btn-sm btn-danger btn-icon">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedLine" class="info-box" :class="getInfoBoxClass()">
                        <div class="info-row">
                            <span>Budget Alloué:</span>
                            <strong>{{ formatCurrency(selectedLine.allocated_amount) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Déjà Dépensé:</span>
                            <strong>{{ formatCurrency(selectedLine.spent) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Restant:</span>
                            <strong :style="{ color: selectedLine.remaining < 0 ? 'var(--accent-red)' : 'var(--accent-green)' }">
                                {{ formatCurrency(selectedLine.remaining) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="closeExpenseModal" class="btn btn-secondary">Annuler</button>
                    <button @click="saveExpense" class="btn btn-primary" :disabled="isSaving">
                        <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Visionneuse de Document -->
        <div class="modal-overlay" :class="{ active: modals.documentViewer }" @click.self="closeDocumentViewer">
            <div class="modal" style="max-width: 900px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-file"></i>
                        Document Justificatif
                    </h3>
                    <button class="modal-close" @click="closeDocumentViewer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 0; max-height: 80vh; overflow: auto;">
                    <iframe v-if="viewingDocument && !isImage(viewingDocument)" :src="viewingDocument"
                        style="width: 100%; height: 80vh; border: none;"></iframe>
                    <img v-else-if="viewingDocument" :src="viewingDocument" style="width: 100%; height: auto;"
                        alt="Document">
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'api/index.php';
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    canEdit: <?php echo $canEdit ? 'true' : 'false'; ?>,
                    user_name: '<?php echo $_SESSION["user_name"] ?>',
                    user_role: '<?php echo $_SESSION["user_role"] ?? "user"; ?>',
                    menuOpen: false,
                    expenses: [],
                    filteredExpenses: [],
                    projects: [],
                    lines: [],
                    selectedLine: null,
                    expense: {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        expense_date: new Date().toISOString().split('T')[0],
                        description: '',
                        document: null
                    },
                    selectedFile: null,
                    isDragging: false,
                    isEditMode: false,
                    isSaving: false,
                    searchQuery: '',
                    projectFilter: '',
                    statusFilter: '',
                    dateFrom: '',
                    dateTo: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    modals: {
                        expense: false,
                        documentViewer: false
                    },
                    viewingDocument: null,
                    stats: {
                        totalExpenses: 0,
                        totalAmount: 0,
                        thisMonth: 0,
                        overBudget: 0
                    },
                    projectsChart: null,
                    evolutionChart: null,
                    isRenderingCharts: false
                };
            },
            computed: {
                paginatedExpenses() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredExpenses.slice(start, start + this.itemsPerPage);
                },
                totalPages() {
                    return Math.ceil(this.filteredExpenses.length / this.itemsPerPage);
                }
            },
            mounted() {
                this.fetchProjects();
                this.fetchExpenses();
            },
            methods: {
                logout() {
                    window.location.href = 'logout.php';
                },
                async fetchProjects() {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getProjects`);
                        const data = await response.json();
                        this.projects = data.data || [];
                    } catch (error) {
                        console.error('[v0] Error fetching projects:', error);
                    }
                },
                async fetchExpenses() {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getExpenses`);
                        const data = await response.json();
                        this.expenses = data.data || [];
                        this.filteredExpenses = this.expenses;
                        this.calculateStats();
                        this.$nextTick(() => {
                            this.renderCharts();
                        });
                    } catch (error) {
                        console.error('[v0] Error fetching expenses:', error);
                    }
                },
                reduceWord(text) {

                    if (!text) return '';

                    const str = String(text);

                    if (str.length <= 20) {
                        return str;
                    }

                    return str.substring(0, 20) + '...';
                },

                async fetchLines() {
                    if (!this.expense.project_id) return;
                    try {
                        const response = await fetch(
                            `${API_BASE_URL}?action=getProjectBudgetLines&project_id=${this.expense.project_id}`
                        );
                        const data = await response.json();
                        this.lines = data.data || [];
                        console.log('[v0] Lines fetched:', JSON.stringify(this.lines, null, 2));

                        if (!this.isEditMode) {
                            this.expense.project_budget_line_id = '';
                            this.selectedLine = null;
                        }
                    } catch (error) {
                        console.error('[v0] Error fetching lines:', error);
                    }
                },
                updateLineInfo() {
                    const line = this.lines.find(
                        l => l.project_budget_line_id == this.expense.project_budget_line_id
                    );

                    if (line) {
                        const allocated = Number(line.allocated_amount) || 0;
                        const spent = Number(line.spent) || 0;

                        this.selectedLine = {
                            name: line.line_name,
                            allocated_amount: allocated,
                            spent: spent,
                            remaining: allocated - spent
                        };
                        console.log('[v0] Selected line:', this.selectedLine);
                    } else {
                        this.selectedLine = null;
                    }
                },
                onFileSelect(event) {
                    const file = event.target.files[0];
                    this.validateAndSetFile(file);
                },
                onDragOver(event) {
                    this.isDragging = true;
                },
                onDragLeave(event) {
                    this.isDragging = false;
                },
                onFileDrop(event) {
                    this.isDragging = false;
                    const file = event.dataTransfer.files[0];
                    this.validateAndSetFile(file);
                },
                validateAndSetFile(file) {
                    if (!file) return;

                    const validTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'];
                    const maxSize = 5 * 1024 * 1024; // 5MB

                    if (!validTypes.includes(file.type)) {
                        alert('Type de fichier non valide. Seuls les fichiers PDF, PNG et JPG sont acceptés.');
                        return;
                    }

                    if (file.size > maxSize) {
                        alert('Le fichier est trop volumineux. La taille maximale est de 5Mo.');
                        return;
                    }

                    this.selectedFile = file;
                },
                removeFile() {
                    this.selectedFile = null;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },
                async removeExistingDocument() {
                    if (!confirm('Voulez-vous vraiment supprimer ce document ?')) return;

                    try {
                        const response = await fetch(`${API_BASE_URL}?action=removeExpenseDocument&id=${this.expense.id}`, {
                            method: 'POST'
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.expense.document = null;
                            alert('Document supprimé avec succès');
                            this.fetchExpenses();
                        } else {
                            alert(data.message || 'Erreur lors de la suppression du document');
                        }
                    } catch (error) {
                        console.error('[v0] Error removing document:', error);
                        alert('Erreur lors de la suppression du document');
                    }
                },
                async saveExpense() {

                    if (!this.canEdit) return;

                    if (
                        !this.expense.project_id ||
                        !this.expense.project_budget_line_id ||
                        !this.expense.amount ||
                        !this.expense.expense_date
                    ) {
                        alert('Veuillez remplir tous les champs obligatoires');
                        return;
                    }

                    this.isSaving = true;

                    try {

                        const formData = new FormData();

                        formData.append('project_id', Number(this.expense.project_id));
                        formData.append('project_budget_line_id', Number(this.expense.project_budget_line_id));
                        formData.append('amount', Number(this.expense.amount));
                        formData.append('expense_date', this.expense.expense_date);
                        formData.append('description', this.expense.description || '');

                        if (this.selectedFile) {
                            formData.append('document', this.selectedFile);
                        }

                        let route = `${API_BASE_URL}?action=createExpense`;
                        if (this.isEditMode && this.expense.id) {
                            route = `${API_BASE_URL}?action=updateExpense&id=${this.expense.id}`;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 🔎 DEBUG LOGS
                        |--------------------------------------------------------------------------
                        */

                        console.log('===== SAVE EXPENSE =====');
                        console.log('Route:', route);

                        console.log('Payload (FormData):');
                        for (let [key, value] of formData.entries()) {
                            console.log(key, value);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 📡 Requête
                        |--------------------------------------------------------------------------
                        */

                        const response = await fetch(route, {
                            method: 'POST',
                            body: formData
                        });

                        console.log('HTTP Status:', response.status);

                        const data = await response.json();

                        console.log('Server Response:', data);

                        if (!data.success) {
                            alert(data.message || 'Une erreur est survenue');
                            return;
                        }

                        alert(
                            data.message ||
                            (this.isEditMode ?
                                'Dépense modifiée avec succès' :
                                'Dépense enregistrée avec succès')
                        );

                        this.closeExpenseModal();
                        this.fetchExpenses();

                    } catch (error) {

                        console.error('Network / JS Error:', error);
                        alert('Erreur lors de l\'enregistrement de la dépense');

                    } finally {
                        this.isSaving = false;
                    }
                },
                openExpenseModal() {
                    if (!this.canEdit) return;

                    this.isEditMode = false;
                    this.expense = {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: '',
                        expense_date: new Date().toISOString().split('T')[0],
                        description: '',
                        document: null
                    };
                    this.selectedFile = null;
                    this.selectedLine = null;
                    this.lines = [];
                    this.modals.expense = true;
                },
                async editExpense(expense) {
                    this.isEditMode = true;
                    this.expense = {
                        id: expense.id,
                        project_id: expense.project_id,
                        project_budget_line_id: expense.project_budget_line_id,
                        amount: expense.amount,
                        expense_date: expense.expense_date,
                        description: expense.description,
                        document: expense.document
                    };
                    this.selectedFile = null;

                    await this.fetchLines();

                    this.$nextTick(() => {
                        this.updateLineInfo();
                    });

                    this.modals.expense = true;
                },
                async deleteExpense(expense) {
                    if (!this.canEdit) return;

                    if (!confirm(`Êtes-vous sûr de vouloir supprimer cette dépense de ${this.formatCurrency(expense.amount)} ?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`${API_BASE_URL}?action=deleteExpense&id=${expense.id}`, {
                            method: 'DELETE'
                        });

                        const data = await response.json();

                        if (!data.success) {
                            alert(data.message || 'Erreur lors de la suppression');
                            return;
                        }

                        alert(data.message || 'Dépense supprimée avec succès');
                        this.fetchExpenses();

                    } catch (error) {
                        console.error('[v0] Error deleting expense:', error);
                        alert('Erreur lors de la suppression de la dépense');
                    }
                },
                closeExpenseModal() {
                    this.modals.expense = false;
                    this.isEditMode = false;
                    this.selectedFile = null;
                },
                viewDocument(expense) {
                    if (!expense.document) return;

                    // Ajouter le préfixe "images/" si nécessaire
                    const docPath = expense.document.startsWith('images/') ?
                        expense.document :
                        'images/' + expense.document;

                    this.viewingDocument = docPath;
                    this.modals.documentViewer = true;
                },

                closeDocumentViewer() {
                    this.modals.documentViewer = false;
                    this.viewingDocument = null;
                },
                filterExpenses() {
                    let filtered = this.expenses;

                    if (this.searchQuery) {
                        filtered = filtered.filter(e =>
                            e.description.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            e.project_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            e.budget_line_name.toLowerCase().includes(this.searchQuery.toLowerCase())
                        );
                    }

                    if (this.projectFilter) {
                        filtered = filtered.filter(e => e.project_id == this.projectFilter);
                    }

                    if (this.statusFilter) {
                        filtered = filtered.filter(e => {
                            const remaining = parseFloat(e.remaining || 0);
                            const allocated = parseFloat(e.allocated_amount || 0);
                            const percentage = allocated > 0 ? ((allocated - remaining) / allocated) * 100 : 0;

                            if (this.statusFilter === 'ok') return percentage < 80 && remaining >= 0;
                            if (this.statusFilter === 'warning') return percentage >= 80 && remaining >= 0;
                            if (this.statusFilter === 'over') return remaining < 0;
                        });
                    }

                    if (this.dateFrom) {
                        filtered = filtered.filter(e => new Date(e.expense_date) >= new Date(this.dateFrom));
                    }

                    if (this.dateTo) {
                        filtered = filtered.filter(e => new Date(e.expense_date) <= new Date(this.dateTo));
                    }

                    this.filteredExpenses = filtered;
                    this.currentPage = 1;
                },
                calculateStats() {
                    this.stats.totalExpenses = this.expenses.length;
                    this.stats.totalAmount = this.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);

                    const currentMonth = new Date().getMonth();
                    const currentYear = new Date().getFullYear();
                    this.stats.thisMonth = this.expenses
                        .filter(e => {
                            const date = new Date(e.expense_date);
                            return date.getMonth() === currentMonth && date.getFullYear() === currentYear;
                        })
                        .reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);

                    this.stats.overBudget = this.expenses.filter(e => parseFloat(e.remaining || 0) < 0).length;
                },
                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'XOF',
                        minimumFractionDigits: 0
                    }).format(value || 0);
                },
                formatDate(date) {
                    return new Date(date).toLocaleDateString('fr-FR');
                },
                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },
                getRowClass(expense) {
                    const allocated = parseFloat(expense.allocated_amount || 0);
                    const spent = parseFloat(expense.spent || 0);
                    const percentage = allocated > 0 ? (spent / allocated) * 100 : 0;
                    const remaining = allocated - spent;

                    if (remaining < 0 || percentage > 100) return 'danger';
                    if (percentage >= 80) return 'warning';
                    return '';
                },
                getBadgeClass(remaining, allocated) {
                    const rem = parseFloat(remaining || 0);
                    const alloc = parseFloat(allocated || 0);
                    const percentage = alloc > 0 ? ((alloc - rem) / alloc) * 100 : 0;

                    if (rem < 0) return 'badge-danger';
                    if (percentage > 80) return 'badge-warning';
                    return 'badge-success';
                },
                getUsagePercentage(expense) {
                    const allocated = parseFloat(expense.allocated_amount || 0);
                    const spent = parseFloat(expense.spent || 0);
                    if (allocated === 0) return 0;
                    return Math.round((spent / allocated) * 100);
                },
                getRemainingAmount(expense) {
                    const allocated = parseFloat(expense.allocated_amount || 0);
                    const spent = parseFloat(expense.spent || 0);
                    return allocated - spent;
                },
                getPercentageColor(expense) {
                    const percentage = this.getUsagePercentage(expense);
                    if (percentage >= 100) return 'var(--accent-red)';
                    if (percentage >= 80) return 'var(--accent-yellow)';
                    return 'var(--accent-green)';
                },
                getInfoBoxClass() {
                    if (!this.selectedLine) return '';
                    const remaining = this.selectedLine.allocated_amount - this.selectedLine.spent;
                    const percentage = this.selectedLine.allocated_amount > 0 ?
                        (this.selectedLine.spent / this.selectedLine.allocated_amount) * 100 :
                        0;

                    if (remaining < 0) return 'danger';
                    if (percentage > 80) return 'warning';
                    return '';
                },
                getFileIcon(file) {
                    if (file.type === 'application/pdf') return 'fas fa-file-pdf';
                    return 'fas fa-file-image';
                },
                getFileColor(file) {
                    if (file.type === 'application/pdf') return 'var(--accent-red)';
                    return 'var(--accent-blue)';
                },
                getDocumentIcon(filename) {
                    if (!filename) return 'fas fa-file';
                    if (filename.toLowerCase().endsWith('.pdf')) return 'fas fa-file-pdf';
                    return 'fas fa-file-image';
                },
                getDocumentColor(filename) {
                    if (!filename) return 'var(--text-secondary)';
                    if (filename.toLowerCase().endsWith('.pdf')) return 'var(--accent-red)';
                    return 'var(--accent-blue)';
                },
                isImage(filename) {
                    if (!filename) return false;
                    const ext = filename.toLowerCase();
                    return ext.endsWith('.png') || ext.endsWith('.jpg') || ext.endsWith('.jpeg');
                },
                renderCharts() {
                    if (this.isRenderingCharts) {
                        console.log('[v0] Render already in progress, skipping...');
                        return;
                    }

                    this.isRenderingCharts = true;
                    console.log('[v0] Starting charts render...');

                    // Détruire les anciens graphiques
                    if (this.projectsChart) {
                        try {
                            this.projectsChart.destroy();
                        } catch (e) {
                            console.error('[v0] Error destroying projectsChart:', e);
                        }
                        this.projectsChart = null;
                    }
                    if (this.evolutionChart) {
                        try {
                            this.evolutionChart.destroy();
                        } catch (e) {
                            console.error('[v0] Error destroying evolutionChart:', e);
                        }
                        this.evolutionChart = null;
                    }

                    // Attendre que le DOM soit prêt
                    setTimeout(() => {
                        this.$nextTick(() => {
                            try {
                                const projectsCanvas = this.$refs.projectsChart;
                                const evolutionCanvas = this.$refs.evolutionChart;

                                if (!projectsCanvas || !evolutionCanvas) {
                                    console.error('[v0] Les refs des canvas ne sont pas disponibles');
                                    this.isRenderingCharts = false;
                                    return;
                                }

                                // Vérifier que les canvas sont dans le DOM
                                if (!document.body.contains(projectsCanvas) || !document.body.contains(evolutionCanvas)) {
                                    console.error('[v0] Les canvas ne sont pas dans le DOM');
                                    this.isRenderingCharts = false;
                                    return;
                                }

                                const colors = ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b', '#ff6b9d', '#10b981'];

                                // Graphique des dépenses par projet
                                if (projectsCanvas && this.projects.length > 0 && this.expenses.length > 0) {
                                    try {
                                        const parent1 = projectsCanvas.parentElement;
                                        if (parent1) {
                                            parent1.style.position = 'relative';
                                            parent1.style.height = '400px';
                                            parent1.style.width = '100%';
                                        }

                                        const ctx1 = projectsCanvas.getContext('2d');
                                        if (!ctx1) {
                                            console.error('[v0] Impossible d\'obtenir le contexte 2d pour projectsCanvas');
                                            this.isRenderingCharts = false;
                                            return;
                                        }

                                        const projectExpenses = {};
                                        this.expenses.forEach(e => {
                                            if (!projectExpenses[e.project_name]) {
                                                projectExpenses[e.project_name] = 0;
                                            }
                                            projectExpenses[e.project_name] += parseFloat(e.amount || 0);
                                        });

                                        this.projectsChart = new Chart(ctx1, {
                                            type: 'doughnut',
                                            data: {
                                                labels: Object.keys(projectExpenses),
                                                datasets: [{
                                                    data: Object.values(projectExpenses),
                                                    backgroundColor: colors.slice(0, Object.keys(projectExpenses).length),
                                                    borderColor: '#111111',
                                                    borderWidth: 2
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                devicePixelRatio: 2,
                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        position: 'bottom',
                                                        labels: {
                                                            color: '#ededed',
                                                            padding: 15,
                                                            font: {
                                                                size: 13,
                                                                weight: '600'
                                                            }
                                                        }
                                                    },
                                                    tooltip: {
                                                        enabled: true,
                                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                        padding: 12,
                                                        cornerRadius: 8,
                                                        callbacks: {
                                                            label: (ctx) => {
                                                                const val = ctx.parsed;
                                                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                                                return `${ctx.label}: ${this.formatCurrency(val)} (${pct}%)`;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    } catch (e) {
                                        console.error('[v0] Erreur création graphique projects:', e);
                                    }
                                }

                                // Graphique d'évolution des dépenses
                                if (evolutionCanvas && this.expenses.length > 0) {
                                    try {
                                        const parent2 = evolutionCanvas.parentElement;
                                        if (parent2) {
                                            parent2.style.position = 'relative';
                                            parent2.style.height = '400px';
                                            parent2.style.width = '100%';
                                        }

                                        const ctx2 = evolutionCanvas.getContext('2d');
                                        if (!ctx2) {
                                            console.error('[v0] Impossible d\'obtenir le contexte 2d pour evolutionCanvas');
                                            this.isRenderingCharts = false;
                                            return;
                                        }

                                        const sorted = [...this.expenses].sort((a, b) =>
                                            new Date(a.expense_date) - new Date(b.expense_date)
                                        );

                                        const grouped = {};
                                        sorted.forEach(e => {
                                            const date = this.formatDate(e.expense_date);
                                            if (!grouped[date]) {
                                                grouped[date] = 0;
                                            }
                                            grouped[date] += parseFloat(e.amount || 0);
                                        });

                                        // Calculer le cumulé
                                        const dates = Object.keys(grouped);
                                        const cumulativeData = [];
                                        let cumul = 0;
                                        dates.forEach(date => {
                                            cumul += grouped[date];
                                            cumulativeData.push(cumul);
                                        });

                                        this.evolutionChart = new Chart(ctx2, {
                                            type: 'line',
                                            data: {
                                                labels: dates,
                                                datasets: [{
                                                    label: 'Dépenses cumulées',
                                                    data: cumulativeData,
                                                    borderColor: '#00d4ff',
                                                    backgroundColor: 'rgba(0, 212, 255, 0.1)',
                                                    fill: true,
                                                    tension: 0.4,
                                                    pointBackgroundColor: '#00d4ff',
                                                    pointBorderColor: '#fff',
                                                    pointRadius: 4,
                                                    pointBorderWidth: 2
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                devicePixelRatio: 2,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        ticks: {
                                                            color: '#ededed',
                                                            font: {
                                                                size: 12
                                                            },
                                                            callback: function(value) {
                                                                return value.toLocaleString();
                                                            }
                                                        },
                                                        grid: {
                                                            color: '#2a2a2a'
                                                        }
                                                    },
                                                    x: {
                                                        ticks: {
                                                            color: '#ededed',
                                                            font: {
                                                                size: 12
                                                            },
                                                            maxRotation: 45,
                                                            minRotation: 45
                                                        },
                                                        grid: {
                                                            color: '#2a2a2a',
                                                            display: false
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        labels: {
                                                            color: '#ededed',
                                                            padding: 15,
                                                            font: {
                                                                size: 13,
                                                                weight: '600'
                                                            }
                                                        }
                                                    },
                                                    tooltip: {
                                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                        padding: 12,
                                                        cornerRadius: 8,
                                                        callbacks: {
                                                            label: (ctx) => {
                                                                return `Total: ${this.formatCurrency(ctx.parsed.y)}`;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    } catch (e) {
                                        console.error('[v0] Erreur création graphique evolution:', e);
                                    }
                                }

                                console.log('[v0] Rendu des graphiques terminé');
                                this.isRenderingCharts = false;
                            } catch (error) {
                                console.error('[v0] Erreur générale renderCharts:', error);
                                this.isRenderingCharts = false;
                            }
                        });
                    }, 200);
                }
            }
        }).mount('#app');
    </script>
</body>

</html>