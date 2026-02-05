<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrizonPlus • Gestion des Dépenses</title>

    <!-- Vue 3 -->
    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Font Awesome -->
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
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #0070f3 0%, #00d4ff 100%);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);
            --radius: 12px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            gap: 1rem;
            list-style: none;
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
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-primary);
            background: var(--bg-tertiary);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Stats Cards */
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

        /* Section Card */
        .section-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 2rem;
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Filters */
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
            background: var(--bg-primary);
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

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .filter-input {
            min-width: 150px;
        }

        /* Button */
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
            box-shadow: 0 10px 30px rgba(0, 112, 243, 0.3);
        }

        .btn-success {
            background: var(--accent-green);
            color: white;
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

        /* Table */
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
        }

        .table tbody tr.danger {
            background: rgba(255, 59, 59, 0.1);
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

        /* Modal */
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
            transition: color 0.2s ease;
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

        /* Form */
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
            background: var(--bg-primary);
        }

        /* Info Box */
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

        /* Chart Container */
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

        /* Empty State */
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

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
            }

            .table-container {
                font-size: 0.875rem;
            }

            .table td,
            .table th {
                padding: 0.75rem 0.5rem;
            }

            .charts-grid {
                grid-template-columns: 1fr;
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
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.html" class="nav-link"><i class="fas fa-folder-open"></i> Projets</a></li>
                        <li><a href="expenses.html" class="nav-link active"><i class="fas fa-receipt"></i> Dépenses</a></li>
                        <li><a href="#" class="nav-link" @click="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Main Container -->
        <div class="container">
            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Dépenses</span>
                        <div class="stat-icon" style="background: rgba(255, 59, 59, 0.2); color: var(--accent-red);">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.totalExpenses }}</div>
                    <div class="stat-change">
                        <i class="fas fa-calendar"></i>
                        Enregistrées
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Montant Total</span>
                        <div class="stat-icon" style="background: rgba(124, 58, 237, 0.2); color: var(--accent-purple);">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalAmount) }}</div>
                    <div class="stat-change">
                        Dépensé au total
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Ce Mois</span>
                        <div class="stat-icon" style="background: rgba(0, 212, 255, 0.2); color: var(--accent-cyan);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.thisMonth) }}</div>
                    <div class="stat-change">
                        Dépenses mensuelles
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Budgets Dépassés</span>
                        <div class="stat-icon" style="background: rgba(255, 184, 0, 0.2); color: var(--accent-yellow);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.overBudget }}</div>
                    <div class="stat-change">
                        <i class="fas fa-alert-triangle"></i>
                        Lignes en alerte
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Dépenses par Projet</h3>
                    <div class="chart-container">
                        <canvas ref="projectsChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="fas fa-chart-line"></i> Évolution des Dépenses</h3>
                    <div class="chart-container">
                        <canvas ref="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Expenses Table Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-receipt"></i>
                        Dépenses ({{ filteredExpenses.length }})
                    </h2>
                    <button class="btn btn-primary" @click="openExpenseModal">
                        <i class="fas fa-plus"></i>
                        Nouvelle Dépense
                    </button>
                </div>
                <div class="section-content">
                    <!-- Filters -->
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="search-input" placeholder="Rechercher une dépense..." v-model="searchQuery" @input="filterExpenses">
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
                        <input type="date" class="filter-input" v-model="dateFilter" @change="filterExpenses">
                    </div>

                    <!-- Expenses Table -->
                    <div v-if="filteredExpenses.length === 0" class="empty-state">
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
                                    <th>Utilisé</th>
                                    <th>Restant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in filteredExpenses" :key="expense.id"
                                    :class="{'warning': expense.remaining > 0 && expense.remaining < expense.allocated_amount * 0.2, 'danger': expense.remaining < 0}">
                                    <td>{{ formatDate(expense.expense_date) }}</td>
                                    <td><strong>{{ expense.project_name }}</strong></td>
                                    <td>{{ expense.line_name }}</td>
                                    <td>{{ expense.description }}</td>
                                    <td><strong>{{ formatCurrency(expense.amount) }}</strong></td>
                                    <td>{{ formatCurrency(expense.allocated_amount) }}</td>
                                    <td>{{ formatCurrency(expense.spent) }}</td>
                                    <td>
                                        <span class="badge" :class="getBadgeClass(expense.remaining, expense.allocated_amount)">
                                            {{ formatCurrency(expense.remaining) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Modal -->
        <div class="modal-overlay" :class="{active: modals.expense}" @click.self="closeExpenseModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-plus-circle"></i>
                        Nouvelle Dépense
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
                        <select class="form-select" v-model="expense.project_budget_line_id" @change="updateLineInfo">
                            <option value="">Sélectionner une ligne</option>
                            <option v-for="l in lines" :key="l.id" :value="l.id">{{ l.name }}</option>
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
                            <strong :style="{color: selectedLine.remaining < 0 ? 'var(--accent-red)' : 'var(--accent-green)'}">
                                {{ formatCurrency(selectedLine.remaining) }}
                            </strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Montant dépensé (FCFA)</label>
                        <input type="number" class="form-input" v-model.number="expense.amount" placeholder="Ex: 50000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-input" v-model="expense.description" placeholder="Ex: Achat de matériel, Paiement prestataire...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeExpenseModal">Annuler</button>
                    <button class="btn btn-success" @click="saveExpense">
                        <i class="fas fa-check"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue;

        // Configuration de l'API
        const API_BASE_URL = 'http://127.0.0.1/orizonplus/api/index.php';

        createApp({
            data() {
                return {
                    expenses: [],
                    filteredExpenses: [],
                    projects: [],
                    lines: [],
                    expense: {
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        description: ''
                    },
                    selectedLine: null,
                    modals: {
                        expense: false
                    },
                    searchQuery: '',
                    projectFilter: '',
                    statusFilter: '',
                    dateFilter: '',
                    stats: {
                        totalExpenses: 0,
                        totalAmount: 0,
                        thisMonth: 0,
                        overBudget: 0
                    },
                    projectsChart: null,
                    evolutionChart: null
                };
            },
            mounted() {
                this.fetchProjects();
                this.fetchExpenses();
            },
            methods: {
                // API Calls with base URL
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
                async fetchLines() {
                    if (!this.expense.project_id) return;
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getProjectBudgetLines&project_id=${this.expense.project_id}`);
                        const data = await response.json();
                        this.lines = data.data || [];
                        this.expense.project_budget_line_id = '';
                        this.selectedLine = null;
                    } catch (error) {
                        console.error('[v0] Error fetching lines:', error);
                    }
                },
                updateLineInfo() {
                    const line = this.lines.find(l => l.id == this.expense.project_budget_line_id);
                    if (line) {
                        this.selectedLine = {
                            allocated_amount: line.allocated_amount,
                            spent: line.spent,
                            remaining: line.remaining
                        };
                    } else {
                        this.selectedLine = null;
                    }
                },
                async saveExpense() {
                    if (!this.expense.project_id || !this.expense.project_budget_line_id || !this.expense.amount) {
                        alert('Veuillez remplir tous les champs obligatoires');
                        return;
                    }
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=createExpense`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.expense)
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.closeExpenseModal();
                        this.fetchExpenses();
                    } catch (error) {
                        console.error('[v0] Error saving expense:', error);
                    }
                },
                // Modal Management
                openExpenseModal() {
                    this.expense = {
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        description: ''
                    };
                    this.selectedLine = null;
                    this.lines = [];
                    this.modals.expense = true;
                },
                closeExpenseModal() {
                    this.modals.expense = false;
                },
                // Filters
                filterExpenses() {
                    let filtered = this.expenses;

                    // Search filter
                    if (this.searchQuery) {
                        filtered = filtered.filter(e =>
                            e.description.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            e.project_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            e.line_name.toLowerCase().includes(this.searchQuery.toLowerCase())
                        );
                    }

                    // Project filter
                    if (this.projectFilter) {
                        filtered = filtered.filter(e => e.project_id == this.projectFilter);
                    }

                    // Status filter
                    if (this.statusFilter === 'ok') {
                        filtered = filtered.filter(e => e.remaining >= e.allocated_amount * 0.2);
                    } else if (this.statusFilter === 'warning') {
                        filtered = filtered.filter(e => e.remaining > 0 && e.remaining < e.allocated_amount * 0.2);
                    } else if (this.statusFilter === 'over') {
                        filtered = filtered.filter(e => e.remaining < 0);
                    }

                    // Date filter
                    if (this.dateFilter) {
                        filtered = filtered.filter(e => e.expense_date === this.dateFilter);
                    }

                    this.filteredExpenses = filtered;
                },
                // Stats Calculation
                calculateStats() {
                    this.stats.totalExpenses = this.expenses.length;
                    this.stats.totalAmount = this.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);

                    // Calculate this month
                    const now = new Date();
                    const thisMonth = this.expenses.filter(e => {
                        const expenseDate = new Date(e.expense_date);
                        return expenseDate.getMonth() === now.getMonth() && expenseDate.getFullYear() === now.getFullYear();
                    });
                    this.stats.thisMonth = thisMonth.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);

                    // Calculate over budget
                    this.stats.overBudget = new Set(this.expenses.filter(e => e.remaining < 0).map(e => e.project_budget_line_id)).size;
                },
                // Charts Rendering
                renderCharts() {
                    this.renderProjectsChart();
                    this.renderEvolutionChart();
                },
                renderProjectsChart() {
                    if (this.projectsChart) {
                        this.projectsChart.destroy();
                    }

                    const ctx = this.$refs.projectsChart;
                    if (!ctx || this.expenses.length === 0) return;

                    // Group expenses by project
                    const projectsData = {};
                    this.expenses.forEach(e => {
                        if (!projectsData[e.project_name]) {
                            projectsData[e.project_name] = 0;
                        }
                        projectsData[e.project_name] += parseFloat(e.amount || 0);
                    });

                    const labels = Object.keys(projectsData);
                    const data = Object.values(projectsData);
                    const colors = ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b'];

                    this.projectsChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors.slice(0, labels.length),
                                borderColor: colors.slice(0, labels.length),
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#ededed',
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                },
                renderEvolutionChart() {
                    if (this.evolutionChart) {
                        this.evolutionChart.destroy();
                    }

                    const ctx = this.$refs.evolutionChart;
                    if (!ctx || this.expenses.length === 0) return;

                    // Group expenses by month
                    const monthsData = {};
                    this.expenses.forEach(e => {
                        const date = new Date(e.expense_date);
                        const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                        if (!monthsData[monthKey]) {
                            monthsData[monthKey] = 0;
                        }
                        monthsData[monthKey] += parseFloat(e.amount || 0);
                    });

                    const sortedMonths = Object.keys(monthsData).sort();
                    const labels = sortedMonths.map(m => {
                        const [year, month] = m.split('-');
                        return new Date(year, month - 1).toLocaleDateString('fr-FR', {
                            month: 'short',
                            year: 'numeric'
                        });
                    });
                    const data = sortedMonths.map(m => monthsData[m]);

                    this.evolutionChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Dépenses',
                                data: data,
                                borderColor: '#0070f3',
                                backgroundColor: 'rgba(0, 112, 243, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#0070f3',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#a0a0a0'
                                    },
                                    grid: {
                                        color: '#2a2a2a'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#a0a0a0'
                                    },
                                    grid: {
                                        color: '#2a2a2a'
                                    }
                                }
                            }
                        }
                    });
                },
                // Utilities
                formatCurrency(value) {
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value || 0) + ' FCFA';
                },
                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                },
                getBadgeClass(remaining, allocated) {
                    if (remaining < 0) return 'badge-danger';
                    if (remaining < allocated * 0.2) return 'badge-warning';
                    return 'badge-success';
                },
                getInfoBoxClass() {
                    if (!this.selectedLine) return '';
                    if (this.selectedLine.remaining < 0) return 'danger';
                    if (this.selectedLine.remaining < this.selectedLine.allocated_amount * 0.2) return 'warning';
                    return '';
                },
                logout() {
                    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                        localStorage.removeItem('user');
                        window.location.href = 'login.php';
                    }
                }
            }
        }).mount('#app');
    </script>
</body>

</html>