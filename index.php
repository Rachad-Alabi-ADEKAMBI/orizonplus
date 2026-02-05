<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrizonPlus • Gestion de Projets</title>

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
            transition: all 0.3s ease;
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
        }

        .stat-change.positive {
            color: var(--accent-green);
        }

        .stat-change.negative {
            color: var(--accent-red);
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

        .filter-select:focus {
            outline: none;
            border-color: var(--accent-blue);
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

        .btn-danger {
            background: var(--accent-red);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
        }

        .table th:hover {
            color: var(--text-primary);
            background: var(--bg-secondary);
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
            color: #ffb800;
        }

        /* Progress Bar */
        .progress {
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-bar {
            height: 100%;
            background: var(--gradient-2);
            transition: width 0.8s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
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

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Budget Line Input */
        .budget-line-input {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            align-items: flex-start;
        }

        .budget-line-input .form-select,
        .budget-line-input .form-input {
            flex: 1;
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

        /* Loading */
        .loading {
            text-align: center;
            padding: 3rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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

            .action-buttons {
                flex-direction: column;
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
                        <li><a href="index.html" class="nav-link active"><i class="fas fa-folder-open"></i> Projets</a></li>
                        <li><a href="expenses.php" class="nav-link"><i class="fas fa-receipt"></i> Dépenses</a></li>
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
                        <span class="stat-label">Total Projets</span>
                        <div class="stat-icon" style="background: rgba(0, 112, 243, 0.2); color: var(--accent-blue);">
                            <i class="fas fa-folder"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.totalProjects }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        {{ stats.projectGrowth }}% ce mois
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Budget Total</span>
                        <div class="stat-icon" style="background: rgba(124, 58, 237, 0.2); color: var(--accent-purple);">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalBudget) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        Budget alloué
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Dépensé</span>
                        <div class="stat-icon" style="background: rgba(0, 230, 118, 0.2); color: var(--accent-green);">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalSpent) }}</div>
                    <div class="stat-change" :class="stats.spentPercentage > 80 ? 'negative' : 'positive'">
                        {{ stats.spentPercentage }}% utilisé
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Restant</span>
                        <div class="stat-icon" style="background: rgba(0, 212, 255, 0.2); color: var(--accent-cyan);">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalRemaining) }}</div>
                    <div class="stat-change" :class="stats.totalRemaining < 0 ? 'negative' : 'positive'">
                        <i :class="stats.totalRemaining < 0 ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle'"></i>
                        {{ stats.totalRemaining < 0 ? 'Budget dépassé' : 'Dans le budget' }}
                    </div>
                </div>
            </div>

            <!-- Budget Lines Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-list"></i>
                        Lignes Budgétaires
                    </h2>
                    <button class="btn btn-primary btn-sm" @click="openLineModal">
                        <i class="fas fa-plus"></i>
                        Ajouter
                    </button>
                </div>
                <div class="section-content">
                    <div v-if="availableLines.length === 0" class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune ligne budgétaire</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in availableLines" :key="line.id">
                                    <td>
                                        <input v-model="line.name" class="form-input" @blur="updateLine(line)" />
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-success btn-sm" @click="updateLine(line)">
                                                <i class="fas fa-save"></i> Enregistrer
                                            </button>
                                            <button class="btn btn-danger btn-sm" @click="deleteLine(line.id)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Projects Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-folder"></i>
                        Projets ({{ filteredProjects.length }})
                    </h2>
                    <button class="btn btn-primary" @click="openProjectModal">
                        <i class="fas fa-plus"></i>
                        Nouveau Projet
                    </button>
                </div>
                <div class="section-content">
                    <!-- Filters -->
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="search-input" placeholder="Rechercher un projet..." v-model="searchQuery" @input="filterProjects">
                        </div>
                        <select class="filter-select" v-model="budgetFilter" @change="filterProjects">
                            <option value="">Tous les projets</option>
                            <option value="remaining">Budget restant</option>
                            <option value="over">Budget dépassé</option>
                        </select>
                        <select class="filter-select" v-model="sortBy" @change="sortProjects">
                            <option value="name">Trier par nom</option>
                            <option value="total_budget">Trier par budget</option>
                            <option value="total_spent">Trier par dépensé</option>
                            <option value="remaining">Trier par restant</option>
                        </select>
                    </div>

                    <!-- Projects Table -->
                    <div v-if="filteredProjects.length === 0" class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>Aucun projet trouvé</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th @click="setSortBy('name')">
                                        Projet
                                        <i v-if="sortBy === 'name'" :class="sortAsc ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    </th>
                                    <th @click="setSortBy('total_budget')">
                                        Budget
                                        <i v-if="sortBy === 'total_budget'" :class="sortAsc ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    </th>
                                    <th @click="setSortBy('total_spent')">
                                        Dépensé
                                        <i v-if="sortBy === 'total_spent'" :class="sortAsc ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    </th>
                                    <th @click="setSortBy('remaining')">
                                        Restant
                                        <i v-if="sortBy === 'remaining'" :class="sortAsc ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    </th>
                                    <th>Progression</th>
                                    <th style="width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="project in filteredProjects" :key="project.id">
                                    <td><strong>{{ project.name }}</strong></td>
                                    <td>{{ formatCurrency(project.total_budget) }}</td>
                                    <td>{{ formatCurrency(project.total_spent) }}</td>
                                    <td>
                                        <span class="badge" :class="project.remaining < 0 ? 'badge-danger' : 'badge-success'">
                                            {{ formatCurrency(project.remaining) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ getProgress(project) }}%</div>
                                        <div class="progress">
                                            <div class="progress-bar" :style="{width: getProgress(project) + '%'}"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-primary btn-sm btn-icon" @click="viewProject(project)" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-primary btn-sm btn-icon" @click="editProject(project)" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-icon" @click="deleteProject(project.id)" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Modal -->
        <div class="modal-overlay" :class="{active: modals.line}" @click.self="closeLineModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-tag"></i>
                        Nouvelle Ligne Budgétaire
                    </h3>
                    <button class="modal-close" @click="closeLineModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom de la ligne</label>
                        <input type="text" class="form-input" v-model="newLineName" placeholder="Ex: Salaires, Marketing, Infrastructure..." />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" @click="closeLineModal">Annuler</button>
                    <button class="btn btn-primary" @click="createLine">
                        <i class="fas fa-check"></i>
                        Créer
                    </button>
                </div>
            </div>
        </div>

        <!-- Project Modal -->
        <div class="modal-overlay" :class="{active: modals.project}" @click.self="closeProjectModal">
            <div class="modal" style="max-width: 800px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-folder-plus"></i>
                        {{ isEditMode ? 'Modifier le Projet' : 'Nouveau Projet' }}
                    </h3>
                    <button class="modal-close" @click="closeProjectModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom du projet</label>
                        <input type="text" class="form-input" v-model="newProject.name" placeholder="Ex: Développement Web, Campagne Marketing..." />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Budget total (FCFA)</label>
                        <input type="number" class="form-input" v-model.number="newProject.total_budget" placeholder="Ex: 10000000" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (optionnel)</label>
                        <textarea class="form-textarea" v-model="newProject.description" placeholder="Description du projet..."></textarea>
                    </div>
                    <hr style="border-color: var(--border-color); margin: 1.5rem 0;">
                    <div class="form-group">
                        <label class="form-label">Lignes budgétaires</label>
                        <div v-for="(line, index) in projectLines" :key="index" class="budget-line-input">
                            <select v-if="!line.name" class="form-select" v-model="line.budget_line_id" @change="updateLineName(line)">
                                <option value="">Sélectionner une ligne</option>
                                <option v-for="l in availableLinesFiltered(line.budget_line_id)" :key="l.id" :value="l.id">{{ l.name }}</option>
                            </select>
                            <input v-else type="text" class="form-input" :value="line.name" readonly />
                            <input type="number" class="form-input" v-model.number="line.allocated_amount" placeholder="Montant alloué" />
                            <button class="btn btn-danger btn-icon" @click="removeProjectLine(index)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <button v-if="availableLinesFiltered().length > 0" class="btn btn-primary btn-sm" @click="addProjectLine">
                            <i class="fas fa-plus"></i>
                            Ajouter une ligne
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" @click="closeProjectModal">Annuler</button>
                    <button class="btn btn-success" @click="saveProject">
                        <i class="fas fa-check"></i>
                        {{ isEditMode ? 'Enregistrer' : 'Créer' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Project Detail Modal -->
        <div class="modal-overlay" :class="{active: modals.detail}" @click.self="closeDetailModal">
            <div class="modal" style="max-width: 900px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-eye"></i>
                        Détails du Projet
                    </h3>
                    <button class="modal-close" @click="closeDetailModal">&times;</button>
                </div>
                <div class="modal-body">
                    <h3 style="margin-bottom: 1rem;">{{ selectedProject.name }}</h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                        <div>
                            <div class="stat-label">Budget Total</div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem;">{{ formatCurrency(selectedProject.total_budget) }}</div>
                        </div>
                        <div>
                            <div class="stat-label">Dépensé</div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem;">{{ formatCurrency(selectedProject.total_spent) }}</div>
                        </div>
                        <div>
                            <div class="stat-label">Restant</div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.5rem;" :style="{color: selectedProject.remaining < 0 ? 'var(--accent-red)' : 'var(--accent-green)'}">{{ formatCurrency(selectedProject.remaining) }}</div>
                        </div>
                    </div>

                    <div class="stat-label">Progression</div>
                    <div style="font-size: 1.25rem; font-weight: 600; margin: 0.5rem 0;">{{ getProgress(selectedProject) }}%</div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar" :style="{width: getProgress(selectedProject) + '%'}"></div>
                    </div>

                    <div class="chart-container">
                        <canvas ref="projectChart"></canvas>
                    </div>

                    <hr style="border-color: var(--border-color); margin: 1.5rem 0;">

                    <h4 style="margin-bottom: 1rem;">Lignes budgétaires</h4>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ligne</th>
                                    <th>Montant alloué</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in selectedProject.lines" :key="line.budget_line_id">
                                    <td>{{ line.name }}</td>
                                    <td>{{ formatCurrency(line.allocated_amount) }}</td>
                                </tr>
                                <tr v-if="!selectedProject.lines || selectedProject.lines.length === 0">
                                    <td colspan="2" class="empty-state">Aucune ligne budgétaire</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" @click="closeDetailModal">Fermer</button>
                    <button class="btn btn-primary" @click="printProjectDetails">
                        <i class="fas fa-print"></i>
                        Imprimer
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
                    projects: [],
                    filteredProjects: [],
                    availableLines: [],
                    newLineName: '',
                    newProject: {
                        id: null,
                        name: '',
                        total_budget: 0,
                        description: ''
                    },
                    projectLines: [],
                    modals: {
                        line: false,
                        project: false,
                        detail: false
                    },
                    isEditMode: false,
                    selectedProject: {},
                    searchQuery: '',
                    budgetFilter: '',
                    sortBy: 'name',
                    sortAsc: true,
                    stats: {
                        totalProjects: 0,
                        totalBudget: 0,
                        totalSpent: 0,
                        totalRemaining: 0,
                        spentPercentage: 0,
                        projectGrowth: 0
                    },
                    chart: null
                };
            },
            mounted() {
                this.fetchProjects();
                this.fetchBudgetLines();
            },
            methods: {
                // API Calls with base URL
                async fetchProjects() {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getProjects`);
                        const data = await response.json();
                        this.projects = data.data || [];
                        this.filteredProjects = this.projects;
                        this.calculateStats();
                    } catch (error) {
                        console.error('[v0] Error fetching projects:', error);
                    }
                },
                async fetchBudgetLines() {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getBudgetLines`);
                        const data = await response.json();
                        this.availableLines = data.data || [];
                    } catch (error) {
                        console.error('[v0] Error fetching budget lines:', error);
                    }
                },
                async createLine() {
                    if (!this.newLineName.trim()) {
                        alert('Veuillez entrer un nom pour la ligne budgétaire');
                        return;
                    }
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=createBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.newLineName
                            })
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.closeLineModal();
                        this.fetchBudgetLines();
                    } catch (error) {
                        console.error('[v0] Error creating line:', error);
                    }
                },
                async updateLine(line) {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=updateBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: line.id,
                                name: line.name
                            })
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.fetchBudgetLines();
                    } catch (error) {
                        console.error('[v0] Error updating line:', error);
                    }
                },
                async deleteLine(id) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette ligne budgétaire ?')) return;
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=deleteBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.fetchBudgetLines();
                    } catch (error) {
                        console.error('[v0] Error deleting line:', error);
                    }
                },
                async saveProject() {
                    if (!this.newProject.name || !this.newProject.total_budget) {
                        alert('Veuillez remplir tous les champs obligatoires');
                        return;
                    }
                    const action = this.isEditMode ? 'updateProject' : 'createProject';
                    const payload = {
                        ...this.newProject,
                        lines: this.projectLines.filter(l => l.budget_line_id)
                    };
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=${action}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.closeProjectModal();
                        this.fetchProjects();
                    } catch (error) {
                        console.error('[v0] Error saving project:', error);
                    }
                },
                async editProject(project) {
                    this.isEditMode = true;
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getProjectDetails&id=${project.id}`);
                        const data = await response.json();
                        const projectData = data.data || {};
                        this.newProject = {
                            id: projectData.id,
                            name: projectData.name,
                            total_budget: projectData.total_budget,
                            description: projectData.description
                        };
                        this.projectLines = (projectData.lines || []).map(l => ({
                            budget_line_id: l.budget_line_id,
                            allocated_amount: l.allocated_amount,
                            name: l.name
                        }));
                        this.modals.project = true;
                    } catch (error) {
                        console.error('[v0] Error fetching project details:', error);
                    }
                },
                async deleteProject(id) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')) return;
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=deleteProject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        });
                        const data = await response.json();
                        alert(data.message);
                        this.fetchProjects();
                    } catch (error) {
                        console.error('[v0] Error deleting project:', error);
                    }
                },
                async viewProject(project) {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getProjectDetails&id=${project.id}`);
                        const data = await response.json();
                        this.selectedProject = data.data || {};
                        this.modals.detail = true;
                        this.$nextTick(() => {
                            this.renderChart();
                        });
                    } catch (error) {
                        console.error('[v0] Error fetching project details:', error);
                    }
                },
                // Modal Management
                openLineModal() {
                    this.newLineName = '';
                    this.modals.line = true;
                },
                closeLineModal() {
                    this.modals.line = false;
                },
                openProjectModal() {
                    this.isEditMode = false;
                    this.newProject = {
                        id: null,
                        name: '',
                        total_budget: 0,
                        description: ''
                    };
                    this.projectLines = [];
                    this.modals.project = true;
                },
                closeProjectModal() {
                    this.modals.project = false;
                },
                closeDetailModal() {
                    this.modals.detail = false;
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                },
                // Project Lines Management
                addProjectLine() {
                    this.projectLines.push({
                        budget_line_id: '',
                        allocated_amount: 0,
                        name: ''
                    });
                },
                removeProjectLine(index) {
                    this.projectLines.splice(index, 1);
                },
                availableLinesFiltered(currentId = '') {
                    return this.availableLines.filter(l =>
                        !this.projectLines.some(pl => pl.budget_line_id === l.id && l.id !== currentId)
                    );
                },
                updateLineName(line) {
                    const selected = this.availableLines.find(l => l.id == line.budget_line_id);
                    if (selected) line.name = selected.name;
                },
                // Filters and Sorting
                filterProjects() {
                    let filtered = this.projects;

                    // Search filter
                    if (this.searchQuery) {
                        filtered = filtered.filter(p =>
                            p.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                        );
                    }

                    // Budget filter
                    if (this.budgetFilter === 'remaining') {
                        filtered = filtered.filter(p => p.remaining >= 0);
                    } else if (this.budgetFilter === 'over') {
                        filtered = filtered.filter(p => p.remaining < 0);
                    }

                    this.filteredProjects = filtered;
                    this.sortProjects();
                },
                setSortBy(key) {
                    if (this.sortBy === key) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortBy = key;
                        this.sortAsc = true;
                    }
                    this.sortProjects();
                },
                sortProjects() {
                    this.filteredProjects.sort((a, b) => {
                        let aVal = a[this.sortBy];
                        let bVal = b[this.sortBy];

                        if (typeof aVal === 'string') {
                            aVal = aVal.toLowerCase();
                            bVal = bVal.toLowerCase();
                        }

                        if (this.sortAsc) {
                            return aVal > bVal ? 1 : -1;
                        } else {
                            return aVal < bVal ? 1 : -1;
                        }
                    });
                },
                // Stats Calculation
                calculateStats() {
                    this.stats.totalProjects = this.projects.length;
                    this.stats.totalBudget = this.projects.reduce((sum, p) => sum + parseFloat(p.total_budget || 0), 0);
                    this.stats.totalSpent = this.projects.reduce((sum, p) => sum + parseFloat(p.total_spent || 0), 0);
                    this.stats.totalRemaining = this.stats.totalBudget - this.stats.totalSpent;
                    this.stats.spentPercentage = this.stats.totalBudget > 0 ?
                        Math.round((this.stats.totalSpent / this.stats.totalBudget) * 100) :
                        0;
                    this.stats.projectGrowth = 12; // Mock data
                },
                // Chart Rendering
                renderChart() {
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const ctx = this.$refs.projectChart;
                    if (!ctx) return;

                    const budget = parseFloat(this.selectedProject.total_budget || 0);
                    const spent = parseFloat(this.selectedProject.total_spent || 0);
                    const remaining = budget - spent;

                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Dépensé', 'Restant'],
                            datasets: [{
                                data: [spent, remaining > 0 ? remaining : 0],
                                backgroundColor: [
                                    'rgba(0, 112, 243, 0.8)',
                                    'rgba(0, 230, 118, 0.8)'
                                ],
                                borderColor: [
                                    '#0070f3',
                                    '#00e676'
                                ],
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
                                        padding: 20,
                                        font: {
                                            size: 14
                                        }
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
                getProgress(project) {
                    const budget = parseFloat(project.total_budget || 0);
                    const spent = parseFloat(project.total_spent || 0);
                    if (budget === 0) return 0;
                    return Math.min(100, Math.round((spent / budget) * 100));
                },
                printProjectDetails() {
                    window.print();
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