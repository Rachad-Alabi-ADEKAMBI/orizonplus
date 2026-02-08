<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                padding: 10px;
                font-size: 12px;
                border-top: 1px solid #ccc;
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
            color: white;
        }

        .btn-success:hover {
            background: #00c760;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--accent-yellow);
            color: #000;
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
            padding: 0.5rem 0.75rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .table-container {
            overflow-x: auto;
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
            height: 300px;
            margin-top: 1rem;
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
        }

        .pagination button {
            padding: 0.5rem 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            cursor: pointer;
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

        @media (max-width: 768px) {
            .header-content {
                flex-direction: row;
                gap: 1rem;
            }

            .nav-menu {
                flex-direction: column;
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }

            .modal {
                max-width: 95%;
                width: 95%;
            }

            /* Responsive Tables avec data-label */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 1.5rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                padding: 1rem;
                background: var(--bg-tertiary);
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 0;
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
                font-size: 0.85rem;
            }

            .table tbody td>* {
                flex-shrink: 0;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <!-- Header -->
        <!-- Header -->
        <header class="header no-print">
            <div class="header-content">
                <div class="logo">
                    <img src="logo.png" alt="">
                </div>

                <button class="hamburger-btn" @click="toggleMobileMenu" aria-label="Toggle menu">
                    <i class="fas" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>

                <ul class="nav-menu" :class="{ active: mobileMenuOpen }">
                    <li>
                        <a href="index.php" class="nav-link active" @click="closeMobileMenu">
                            <i class="fas fa-folder-open"></i> Projets
                        </a>
                    </li>
                    <li>
                        <a href="expenses.php" class="nav-link" @click="closeMobileMenu">
                            <i class="fas fa-receipt"></i> Dépenses
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

        <div class="container">
            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Transactions</span>
                        <div class="stat-icon" style="background: rgba(0, 112, 243, 0.2); color: var(--accent-blue);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.totalExpenses }}</div>
                    <div class="stat-change" style="color: var(--accent-blue);">
                        <i class="fas fa-check-circle"></i>
                        Enregistrées
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Montant Total</span>
                        <div class="stat-icon" style="background: rgba(0, 230, 118, 0.2); color: var(--accent-green);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalAmount) }}</div>
                    <div class="stat-change" style="color: var(--accent-green);">
                        Dépensé au total
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Ce Mois</span>
                        <div class="stat-icon" style="background: rgba(0, 212, 255, 0.2); color: var(--accent-cyan);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.thisMonth) }}</div>
                    <div class="stat-change" style="color: var(--accent-cyan);">
                        Dépenses mensuelles
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Alertes</span>
                        <div class="stat-icon" style="background: rgba(255, 184, 0, 0.2); color: var(--accent-yellow);">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value" style="color: var(--accent-yellow);">{{ stats.overBudget }}</div>
                    <div class="stat-change" style="color: var(--accent-yellow);">
                        <i class="fas fa-bell"></i>
                        Lignes en alerte
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid no-print">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-pie" style="color: var(--accent-cyan);"></i>
                        Dépenses par Projet
                    </h3>
                    <div class="chart-container">
                        <canvas ref="projectsChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-line" style="color: var(--accent-green);"></i>
                        Évolution des Dépenses
                    </h3>
                    <div class="chart-container">
                        <canvas ref="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Expenses Table Section -->
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">
                            <i class="fas fa-list"></i>
                            Historique des Dépenses ({{ filteredExpenses.length }})
                        </h2>
                    </div>
                    <div class="no-print" style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-success btn-sm" @click="printExpenses">
                            <i class="fas fa-print"></i>
                            Imprimer
                        </button>
                        <button class="btn btn-primary" @click="openExpenseModal">
                            <i class="fas fa-plus-circle"></i>
                            Nouvelle Dépense
                        </button>
                    </div>
                </div>
                <div class="section-content">
                    <!-- Filters -->
                    <div class="filters no-print">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="search-input"
                                placeholder="Rechercher une dépense, projet ou ligne..."
                                v-model="searchQuery" @input="filterExpenses">
                        </div>
                        <select class="filter-select" v-model="projectFilter" @change="filterExpenses">
                            <option value="">Tous les projets</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <select class="filter-select" v-model="statusFilter" @change="filterExpenses">
                            <option value="">Tous les statuts</option>
                            <option value="ok">Budget OK</option>
                            <option value="warning">Budget serré</option>
                            <option value="over">Budget dépassé</option>
                        </select>
                        <input type="date" class="filter-input" v-model="dateFrom"
                            placeholder="Du" @change="filterExpenses">
                        <input type="date" class="filter-input" v-model="dateTo"
                            placeholder="Au" @change="filterExpenses">
                    </div>

                    <!-- Expenses Table -->
                    <div v-if="paginatedExpenses.length === 0" class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>Aucune dépense enregistrée</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Projet</th>
                                    <th>Ligne budgétaire</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Alloué</th>
                                    <th>Utilisé %</th>
                                    <th>Restant</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in paginatedExpenses" :key="expense.id"
                                    :class="getRowClass(expense)">
                                    <td data-label="Date">{{ formatDate(expense.expense_date) }}</td>
                                    <td data-label="Projet"><strong>{{ expense.project_name }}</strong></td>
                                    <td data-label="Ligne budgétaire">{{ expense.budget_line_name }}</td>
                                    <td data-label="Description">{{ expense.description }}</td>
                                    <td data-label="Montant"><strong style="color: var(--accent-green);">{{ formatCurrency(expense.amount) }}</strong></td>
                                    <td data-label="Alloué">{{ formatCurrency(expense.allocated_amount) }}</td>
                                    <td data-label="Utilisé %">
                                        <span :style="{color: getPercentageColor(expense)}">
                                            {{ getUsagePercentage(expense) }}%
                                        </span>
                                    </td>
                                    <td data-label="Restant">
                                        <span class="badge" :class="getBadgeClass(getRemainingAmount(expense), expense.allocated_amount)">
                                            {{ formatCurrency(getRemainingAmount(expense)) }}
                                        </span>
                                    </td>
                                    <td class="no-print" data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn btn-warning btn-sm btn-icon"
                                                @click="editExpense(expense)" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-icon"
                                                @click="deleteExpense(expense)" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination no-print">
                            <button @click="prevPage" :disabled="currentPage === 1">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button v-for="page in totalPages" :key="page"
                                @click="currentPage = page"
                                :class="{active: currentPage === page}">
                                {{ page }}
                            </button>
                            <button @click="nextPage" :disabled="currentPage === totalPages">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Footer (Hidden, only shows when printing) -->
        <div class="print-footer" style="display: none;">
            <p>Rapport généré via l'application OrizonPlus - {{ new Date().toLocaleString('fr-FR') }}</p>
        </div>

        <!-- Expense Modal (Create/Edit) -->
        <div class="modal-overlay" :class="{active: modals.expense}" @click.self="closeExpenseModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i :class="isEditMode ? 'fas fa-edit' : 'fas fa-plus-circle'"></i>
                        {{ isEditMode ? 'Modifier la Dépense' : 'Nouvelle Dépense' }}
                    </h3>
                    <button class="modal-close" @click="closeExpenseModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Projet</label>
                        <select class="form-select" v-model="expense.project_id" @change="fetchLines">
                            <option value="">Sélectionner un projet</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <div class="form-group" v-if="lines.length > 0">
                        <label class="form-label">Ligne budgétaire</label>
                        <select class="form-select"
                            v-model="expense.project_budget_line_id"
                            @change="updateLineInfo">
                            <option value="">Sélectionner une ligne</option>
                            <option v-for="l in lines"
                                :key="l.project_budget_line_id"
                                :value="l.project_budget_line_id">
                                {{ l.name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="selectedLine" class="info-box" :class="getInfoBoxClass()">
                        <div class="info-row">
                            <span>Montant alloué:</span>
                            <strong>{{ formatCurrency(selectedLine.allocated_amount) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Déjà utilisé:</span>
                            <strong>{{ formatCurrency(selectedLine.spent) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Restant:</span>
                            <strong :style="{ color: (selectedLine.allocated_amount - selectedLine.spent) < 0 ? 'var(--accent-red)' : 'var(--accent-green)'}">
                                {{ formatCurrency(selectedLine.allocated_amount - selectedLine.spent) }}
                            </strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Montant dépensé (FCFA)</label>
                        <input type="number" class="form-input" v-model.number="expense.amount"
                            placeholder="Ex: 50000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-input" v-model="expense.description"
                            placeholder="Ex: Achat de matériel, Paiement prestataire...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeExpenseModal">Annuler</button>
                    <button class="btn btn-success" @click="saveExpense">
                        <i class="fas fa-check"></i>
                        {{ isEditMode ? 'Modifier' : 'Enregistrer' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue;
        const API_BASE_URL = 'api/index.php';

        createApp({
            data() {
                return {
                    expenses: [],
                    filteredExpenses: [],
                    projects: [],
                    lines: [],
                    expense: {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        description: ''
                    },
                    selectedLine: null,
                    isEditMode: false,
                    modals: {
                        expense: false
                    },
                    searchQuery: '',
                    projectFilter: '',
                    statusFilter: '',
                    dateFrom: '',
                    dateTo: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    stats: {
                        totalExpenses: 0,
                        totalAmount: 0,
                        thisMonth: 0,
                        overBudget: 0
                    },
                    projectsChart: null,
                    evolutionChart: null,
                    mobileMenuOpen: false
                };
            },
            computed: {
                paginatedExpenses() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredExpenses.slice(start, end);
                },
                totalPages() {
                    return Math.ceil(this.filteredExpenses.length / this.itemsPerPage);
                }
            },
            mounted() {
                this.loadData();
            },
            methods: {
                async loadData() {
                    await this.fetchExpenses();
                    await this.fetchProjects();
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
                        this.expenses = (data.data || []).sort((a, b) => {
                            return new Date(b.expense_date) - new Date(a.expense_date);
                        });
                        this.filteredExpenses = this.expenses;
                        this.calculateStats();
                        this.$nextTick(() => {
                            this.renderCharts();
                        });
                    } catch (error) {
                        console.error('[v0] Error fetching expenses:', error);
                    }
                },
                async fetchLines() {
                    if (!this.expense.project_id) return;
                    try {
                        const response = await fetch(
                            `${API_BASE_URL}?action=getProjectBudgetLines&project_id=${this.expense.project_id}`
                        );
                        const data = await response.json();
                        this.lines = data.data || [];

                        // Reset line selection when project changes unless in edit mode
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
                            allocated_amount: allocated,
                            spent: spent,
                            remaining: allocated - spent
                        };
                    } else {
                        this.selectedLine = null;
                    }
                },
                async saveExpense() {
                    if (
                        !this.expense.project_id ||
                        !this.expense.project_budget_line_id ||
                        !this.expense.amount
                    ) {
                        alert('Veuillez remplir tous les champs obligatoires');
                        return;
                    }

                    const payload = {
                        project_id: Number(this.expense.project_id),
                        project_budget_line_id: Number(this.expense.project_budget_line_id),
                        lines: [{
                            project_budget_line_id: Number(this.expense.project_budget_line_id),
                            amount: Number(this.expense.amount),
                            description: this.expense.description || null
                        }]
                    };

                    let route = `${API_BASE_URL}?action=createExpense`;
                    let method = 'POST';

                    if (this.isEditMode && this.expense.id) {
                        route = `${API_BASE_URL}?action=updateExpense&id=${this.expense.id}`;
                        payload.amount = Number(this.expense.amount);
                        payload.description = this.expense.description;
                    }

                    console.log('=== SAVE EXPENSE ===');
                    console.log('Route:', route);
                    console.log('Payload:', payload);

                    try {
                        const response = await fetch(route, {
                            method,
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        console.log('HTTP status:', response.status);

                        const text = await response.text();
                        console.log('Raw response:', text);

                        const data = JSON.parse(text);
                        console.log('Parsed response:', data);

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
                        console.error('Error saving expense:', error);
                        alert('Erreur lors de l\'enregistrement de la dépense');
                    }
                },
                openExpenseModal() {
                    this.isEditMode = false;
                    this.expense = {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        description: ''
                    };
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
                        description: expense.description
                    };

                    // Fetch lines for the selected project
                    await this.fetchLines();

                    // Update line info after lines are loaded
                    this.$nextTick(() => {
                        this.updateLineInfo();
                    });

                    this.modals.expense = true;
                },
                async deleteExpense(expense) {
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
                renderCharts() {
                    if (this.projectsChart) this.projectsChart.destroy();
                    if (this.evolutionChart) this.evolutionChart.destroy();

                    if (this.$refs.projectsChart && this.projects.length > 0) {
                        const ctx = this.$refs.projectsChart.getContext('2d');
                        if (ctx) {
                            const projectExpenses = {};
                            this.expenses.forEach(e => {
                                if (!projectExpenses[e.project_name]) {
                                    projectExpenses[e.project_name] = 0;
                                }
                                projectExpenses[e.project_name] += parseFloat(e.amount || 0);
                            });

                            this.projectsChart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: Object.keys(projectExpenses),
                                    datasets: [{
                                        data: Object.values(projectExpenses),
                                        backgroundColor: ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            labels: {
                                                color: '#ededed'
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }

                    if (this.$refs.evolutionChart) {
                        const ctx = this.$refs.evolutionChart.getContext('2d');
                        if (ctx) {
                            const monthlyExpenses = {};
                            this.expenses.forEach(e => {
                                const date = new Date(e.expense_date);
                                const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                                if (!monthlyExpenses[key]) {
                                    monthlyExpenses[key] = 0;
                                }
                                monthlyExpenses[key] += parseFloat(e.amount || 0);
                            });

                            const sortedKeys = Object.keys(monthlyExpenses).sort();

                            this.evolutionChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: sortedKeys,
                                    datasets: [{
                                        label: 'Dépenses mensuelles',
                                        data: sortedKeys.map(k => monthlyExpenses[k]),
                                        borderColor: '#00e676',
                                        backgroundColor: 'rgba(0, 230, 118, 0.2)',
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                color: '#ededed'
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                color: '#ededed'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            labels: {
                                                color: '#ededed'
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }
                },
                printExpenses() {
                    const printContent = this.generatePrintContent();
                    const printWindow = window.open('', '', 'width=800,height=600');
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                },
                generatePrintContent() {
                    const now = new Date().toLocaleString('fr-FR');
                    let html = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Rapport Dépenses - OrizonPlus</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    padding: 20px;
                                    color: #000;
                                    background: #fff;
                                }
                                h1 {
                                    text-align: center;
                                    color: #0070f3;
                                    margin-bottom: 30px;
                                }
                                .stats {
                                    display: grid;
                                    grid-template-columns: repeat(4, 1fr);
                                    gap: 15px;
                                    margin-bottom: 30px;
                                }
                                .stat-box {
                                    border: 1px solid #ddd;
                                    padding: 15px;
                                    border-radius: 8px;
                                    text-align: center;
                                }
                                .stat-label {
                                    font-size: 12px;
                                    color: #666;
                                    text-transform: uppercase;
                                    margin-bottom: 5px;
                                }
                                .stat-value {
                                    font-size: 24px;
                                    font-weight: bold;
                                    color: #000;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-bottom: 30px;
                                }
                                th, td {
                                    border: 1px solid #ddd;
                                    padding: 10px;
                                    text-align: left;
                                    font-size: 12px;
                                }
                                th {
                                    background: #f5f5f5;
                                    font-weight: bold;
                                }
                                tr.warning {
                                    background: #fff3cd;
                                }
                                tr.danger {
                                    background: #f8d7da;
                                }
                                .footer {
                                    text-align: center;
                                    margin-top: 50px;
                                    padding-top: 20px;
                                    border-top: 1px solid #ddd;
                                    font-size: 12px;
                                    color: #666;
                                }
                            </style>
                        </head>
                        <body>
                            <h1>Rapport des Dépenses - OrizonPlus</h1>
                            
                            <div class="stats">
                                <div class="stat-box">
                                    <div class="stat-label">Total Transactions</div>
                                    <div class="stat-value">${this.stats.totalExpenses}</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-label">Montant Total</div>
                                    <div class="stat-value">${this.formatCurrency(this.stats.totalAmount)}</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-label">Ce Mois</div>
                                    <div class="stat-value">${this.formatCurrency(this.stats.thisMonth)}</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-label">Alertes</div>
                                    <div class="stat-value" style="color: #ffb800;">${this.stats.overBudget}</div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Projet</th>
                                        <th>Ligne budgétaire</th>
                                        <th>Description</th>
                                        <th>Montant</th>
                                        <th>Alloué</th>
                                        <th>Utilisé %</th>
                                        <th>Restant</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredExpenses.forEach(expense => {
                        const rowClass = this.getRowClass(expense);
                        const remaining = this.getRemainingAmount(expense);
                        html += `
                            <tr class="${rowClass}">
                                <td>${this.formatDate(expense.expense_date)}</td>
                                <td><strong>${expense.project_name}</strong></td>
                                <td>${expense.budget_line_name}</td>
                                <td>${expense.description}</td>
                                <td><strong>${this.formatCurrency(expense.amount)}</strong></td>
                                <td>${this.formatCurrency(expense.allocated_amount)}</td>
                                <td>${this.getUsagePercentage(expense)}%</td>
                                <td>${this.formatCurrency(remaining)}</td>
                            </tr>`;
                    });

                    html += `
                                </tbody>
                            </table>

                            <div class="footer">
                                <p><strong>Rapport généré via l'application OrizonPlus</strong></p>
                                <p>${now}</p>
                            </div>
                        </body>
                        </html>
                    `;

                    return html;
                },
                logout() {
                    if (confirm('Voulez-vous vous déconnecter ?')) {
                        localStorage.removeItem('user');
                        window.location.href = 'api/index.php?action=logout';
                    }
                },

                toggleMobileMenu() {
                    this.mobileMenuOpen = !this.mobileMenuOpen;
                },

                closeMobileMenu() {
                    this.mobileMenuOpen = false;
                }
            }
        }).mount('#app');
    </script>

</body>

</html>