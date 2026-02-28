<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'consultant';
$user_id   = $_SESSION['user_id']   ?? null;
$canEdit   = in_array($user_role, ['admin', 'utilisateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrizonPlus • Fournisseurs</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary:     #0a0a0a;
            --bg-secondary:   #111111;
            --bg-tertiary:    #1a1a1a;
            --border-color:   #2a2a2a;
            --text-primary:   #ededed;
            --text-secondary: #a0a0a0;
            --accent-blue:    #0070f3;
            --accent-cyan:    #00d4ff;
            --accent-green:   #00e676;
            --accent-red:     #ff3b3b;
            --accent-yellow:  #ffb800;
            --gradient-2:     linear-gradient(135deg, #0070f3 0%, #00d4ff 100%);
            --shadow-lg:      0 20px 60px rgba(0,0,0,0.5);
            --radius:         12px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ── Header ── */
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            position: sticky; top: 0; z-index: 100;
        }
        .header-content {
            max-width: 1400px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
        }
        .logo {
            font-size: 1.5rem; font-weight: 700;
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--gradient-2);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-menu { display: flex; gap: 1rem; list-style: none; }
        .nav-link {
            padding: 0.5rem 1rem; border-radius: var(--radius);
            text-decoration: none; color: var(--text-secondary);
            transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem;
        }
        .nav-link:hover, .nav-link.active { color: var(--text-primary); background: var(--bg-tertiary); }
        .hamburger-btn {
            display: none; background: none; border: none;
            color: var(--text-primary); font-size: 1.5rem; cursor: pointer; padding: 0.5rem;
        }
        .notif-badge {
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--accent-red); color: #fff;
            font-size: 0.65rem; font-weight: 700; min-width: 18px; height: 18px;
            border-radius: 999px; padding: 0 4px; margin-left: 4px;
        }

        /* ── Layout ── */
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }

        /* ── Stats ── */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem; margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: var(--radius); padding: 1.5rem;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 100%; height: 3px; background: var(--gradient-2);
            transform: scaleX(0); transition: transform 0.3s ease;
        }
        .stat-card:hover::before { transform: scaleX(1); }
        .stat-card:hover { transform: translateY(-4px); border-color: var(--accent-blue); box-shadow: var(--shadow-lg); }
        .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .stat-label { color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-value { font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; }
        .stat-change { font-size: 0.875rem; color: var(--text-secondary); }

        /* ── Section card ── */
        .section-card {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: var(--radius); margin-bottom: 2rem; overflow: hidden;
        }
        .section-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
        }
        .section-title { font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }

        /* ── Filters ── */
        .filters {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color);
            display: flex; gap: 1rem; flex-wrap: wrap;
        }
        .search-box { flex: 1; min-width: 240px; position: relative; }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); pointer-events: none; }
        .search-input {
            width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem;
            background: var(--bg-tertiary); border: 1px solid var(--border-color);
            border-radius: var(--radius); color: var(--text-primary); font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .search-input:focus { outline: none; border-color: var(--accent-blue); }

        /* ── Table ── */
        .table-container { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table thead { background: var(--bg-tertiary); }
        .table th {
            padding: 0.9rem 1rem; text-align: left; font-weight: 600;
            font-size: 0.78rem; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 0.4px;
            border-bottom: 1px solid var(--border-color); white-space: nowrap;
        }
        .table td {
            padding: 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle;
        }
        .table tbody tr { transition: background 0.15s; }
        .table tbody tr:hover { background: var(--bg-tertiary); }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ── Supplier name cell ── */
        .supplier-name-cell { display: flex; align-items: center; gap: 0.75rem; }
        .supplier-avatar-sm {
            width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
            background: var(--gradient-2); display: flex; align-items: center;
            justify-content: center; font-size: 0.9rem; font-weight: 800;
            color: white; text-transform: uppercase;
        }
        .supplier-name-main { font-weight: 600; }
        .supplier-id-sub    { font-size: 0.72rem; color: var(--text-secondary); }

        /* ── Progress bar ── */
        .progress-wrap { min-width: 90px; }
        .progress-track {
            height: 5px; background: var(--border-color);
            border-radius: 99px; overflow: hidden; margin-bottom: 0.25rem;
        }
        .progress-fill {
            height: 100%; border-radius: 99px;
            background: var(--gradient-2); transition: width 0.5s ease;
        }
        .progress-pct { font-size: 0.7rem; color: var(--text-secondary); text-align: right; }

        /* ── Badge ── */
        .badge {
            display: inline-block; padding: 0.2rem 0.65rem;
            border-radius: 20px; font-size: 0.72rem; font-weight: 600; white-space: nowrap;
        }
        .badge-success { background: rgba(0,230,118,0.15);  color: var(--accent-green); }
        .badge-warning { background: rgba(255,184,0,0.15);  color: var(--accent-yellow); }
        .badge-danger  { background: rgba(255,59,59,0.15);  color: var(--accent-red); }
        .badge-blue    { background: rgba(0,112,243,0.15);  color: var(--accent-blue); }

        /* ── Action buttons ── */
        .action-buttons { display: flex; gap: 0.4rem; }
        .btn {
            padding: 0.5rem 1rem; border-radius: 8px; border: none;
            font-size: 0.82rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex; align-items: center; gap: 0.4rem;
        }
        .btn-icon { padding: 0.45rem 0.6rem; }
        .btn-sm   { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        .btn-primary   { background: var(--accent-blue); color: white; }
        .btn-primary:hover { background: #0060df; transform: translateY(-1px); }
        .btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); }
        .btn-secondary:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
        .btn-warning   { background: rgba(255,184,0,0.12); color: var(--accent-yellow); border: 1px solid rgba(255,184,0,0.25); }
        .btn-warning:hover { background: rgba(255,184,0,0.22); }

        /* ── Empty / loading ── */
        .empty-state {
            display: flex; flex-direction: column; align-items: center;
            padding: 3rem; color: var(--text-secondary); gap: 0.75rem;
        }
        .empty-state i { font-size: 2.5rem; opacity: 0.35; }

        /* ── Pagination ── */
        .pagination-wrap {
            padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: center; gap: 0.35rem; flex-wrap: wrap;
        }
        .pagination-wrap button {
            min-width: 34px; height: 34px; padding: 0 0.55rem;
            border: 1px solid var(--border-color); background: var(--bg-tertiary);
            color: var(--text-primary); border-radius: 8px; cursor: pointer;
            transition: all 0.2s; font-weight: 600; font-size: 0.82rem;
        }
        .pagination-wrap button:hover:not(:disabled) { border-color: var(--accent-blue); background: var(--accent-blue); color: white; }
        .pagination-wrap button.active { background: var(--accent-blue); color: white; border-color: var(--accent-blue); }
        .pagination-wrap button:disabled { opacity: 0.3; cursor: not-allowed; }
        .pg-ellipsis { color: var(--text-secondary); font-size: 0.85rem; padding: 0 0.15rem; }
        .pg-info { color: var(--text-secondary); font-size: 0.78rem; padding: 0 0.5rem; white-space: nowrap; }
        .pg-goto { display: flex; align-items: center; gap: 0.3rem; font-size: 0.78rem; color: var(--text-secondary); margin-left: 0.5rem; }
        .pg-goto input {
            width: 48px; height: 34px; padding: 0 0.4rem;
            background: var(--bg-tertiary); border: 1px solid var(--border-color);
            border-radius: 8px; color: var(--text-primary); font-size: 0.82rem; text-align: center;
        }
        .pg-goto input:focus { outline: none; border-color: var(--accent-blue); }
        .pg-goto button { min-width: unset; padding: 0 0.6rem; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.85);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; opacity: 0; visibility: hidden;
            transition: all 0.25s ease; backdrop-filter: blur(4px);
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: var(--radius); width: 90%; max-height: 90vh; overflow-y: auto;
            transform: scale(0.93); transition: transform 0.25s ease;
        }
        .modal-overlay.active .modal { transform: scale(1); }
        .modal-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; background: var(--bg-secondary); z-index: 10;
        }
        .modal-title { font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .modal-close { background: none; border: none; color: var(--text-secondary); font-size: 1.4rem; cursor: pointer; line-height: 1; }
        .modal-close:hover { color: var(--text-primary); }
        .modal-body   { padding: 1.5rem; }
        .modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 0.75rem; }

        /* ── Detail modal internals ── */
        .detail-hero {
            display: flex; align-items: center; gap: 1rem;
            padding: 1.25rem; background: var(--bg-tertiary);
            border-radius: var(--radius); margin-bottom: 1.5rem;
        }
        .detail-avatar {
            width: 56px; height: 56px; border-radius: 12px; flex-shrink: 0;
            background: var(--gradient-2); display: flex; align-items: center;
            justify-content: center; font-size: 1.5rem; font-weight: 800;
            color: white; text-transform: uppercase;
        }
        .detail-name { font-size: 1.3rem; font-weight: 700; }
        .detail-sub  { font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.15rem; }

        .detail-summary {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;
        }
        .summary-card {
            background: var(--bg-tertiary); border: 1px solid var(--border-color);
            border-radius: var(--radius); padding: 1rem; text-align: center;
        }
        .summary-label { font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.4rem; }
        .summary-value { font-size: 1.15rem; font-weight: 800; }

        .detail-progress { margin-bottom: 1.5rem; }
        .detail-progress-label {
            display: flex; justify-content: space-between;
            font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.4rem;
        }
        .detail-progress-track { height: 8px; background: var(--border-color); border-radius: 99px; overflow: hidden; }
        .detail-progress-fill  { height: 100%; border-radius: 99px; background: var(--gradient-2); transition: width 0.5s; }

        .section-sub-title {
            font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: var(--text-secondary);
            margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem;
        }

        /* ── Expense rows in detail modal ── */
        .exp-row {
            background: var(--bg-tertiary); border: 1px solid var(--border-color);
            border-radius: var(--radius); padding: 1rem 1.1rem;
            margin-bottom: 0.65rem; transition: border-color 0.2s;
        }
        .exp-row:hover { border-color: var(--accent-blue); }
        .exp-row-top {
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.75rem;
        }
        .exp-row-project { font-weight: 700; font-size: 0.9rem; }
        .exp-row-meta    { font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.1rem; }
        .exp-amounts {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;
        }
        .exp-amount-block { background: var(--bg-secondary); border-radius: 8px; padding: 0.5rem 0.75rem; }
        .exp-amount-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-secondary); margin-bottom: 0.15rem; }
        .exp-amount-value { font-size: 0.88rem; font-weight: 700; }

        /* ── Form ── */
        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; margin-bottom: 0.4rem; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); }
        .form-input {
            width: 100%; padding: 0.7rem 1rem;
            background: var(--bg-tertiary); border: 1px solid var(--border-color);
            border-radius: var(--radius); color: var(--text-primary); font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .form-input:focus { outline: none; border-color: var(--accent-blue); }

        /* ── Footer ── */
        .footer { background: var(--bg-secondary); border-top: 1px solid var(--border-color); padding: 1.5rem 2rem; margin-top: 3rem; }
        .footer-content { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .footer-info { font-size: 0.75rem; color: var(--text-secondary); }

        /* ── Print button ── */
        .btn-print { background: rgba(0,112,243,0.12); color: var(--accent-blue); border: 1px solid rgba(0,112,243,0.3); }
        .btn-print:hover { background: rgba(0,112,243,0.25); }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hamburger-btn { display: block; }
            .nav-menu {
                position: fixed; top: 73px; left: 0; right: 0;
                background: var(--bg-secondary); border-bottom: 1px solid var(--border-color);
                flex-direction: column; gap: 0; padding: 1rem 0;
                transform: translateX(-100%); transition: transform 0.3s ease; z-index: 99;
            }
            .nav-menu.active { transform: translateX(0); }
            .nav-link { padding: 1rem 2rem; border-radius: 0; }

            .table thead { display: none; }
            .table tbody tr {
                display: block; margin-bottom: 1rem;
                border: 1px solid var(--border-color); border-radius: var(--radius);
                padding: 0.5rem; background: var(--bg-secondary);
            }
            .table tbody td {
                display: flex; justify-content: space-between; align-items: center;
                padding: 0.65rem 0.75rem; border-bottom: 1px solid var(--border-color);
                text-align: right; font-size: 0.85rem;
            }
            .table tbody td:last-child { border-bottom: none; }
            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600; color: var(--text-secondary); text-align: left;
                margin-right: 1rem; flex-shrink: 0; font-size: 0.75rem;
                text-transform: uppercase; letter-spacing: 0.3px;
            }
            .table tbody td .action-buttons { justify-content: flex-end; }
            .supplier-id-sub { display: none; }
            .detail-summary { grid-template-columns: 1fr 1fr; }
            .exp-amounts    { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div id="app">

    <!-- ── Header ── -->
    <header class="header">
        <div class="header-content">
            <div class="logo"><i class="fas fa-chart-line"></i><span>OrizonPlus</span></div>
            <ul class="nav-menu" :class="{ active: menuOpen }">
                <li><a href="index.php"     class="nav-link"><i class="fas fa-folder-open"></i> Projets</a></li>
                <li><a href="expenses.php"  class="nav-link"><i class="fas fa-wallet"></i> Dépenses</a></li>
                <li><a href="suppliers.php" class="nav-link active"><i class="fas fa-truck"></i> Fournisseurs</a></li>
                <li v-if="user_role === 'admin'">
                    <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Utilisateurs</a>
                </li>
                <li v-if="user_role === 'admin' || user_role === 'utilisateur'">
                    <a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i> Notifications
                        <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                    </a>
                </li>
                <li v-if="user_role === 'utilisateur' || user_role === 'consultant'">
                    <a href="parameters.php" class="nav-link"><i class="fas fa-cog"></i> Paramètres</a>
                </li>
                <li>
                    <a href="api/index.php?action=logout" class="nav-link" style="color:var(--accent-red);">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </li>
            </ul>
            <button class="hamburger-btn" @click="menuOpen = !menuOpen"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <main class="container">

        <p style="margin-bottom:1.5rem;color:var(--text-secondary);">
            Bonjour <strong style="color:var(--text-primary);"><?= ucfirst($_SESSION['user_name']) ?></strong>,
            voici la liste de vos fournisseurs.
        </p>

        <!-- ── KPIs ── -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Fournisseurs</span>
                    <div class="stat-icon" style="background:rgba(0,112,243,0.15);color:var(--accent-blue);">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="stat-value">{{ suppliers.length }}</div>
                <div class="stat-change">Enregistrés</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total commandé</span>
                    <div class="stat-icon" style="background:rgba(255,184,0,0.15);color:var(--accent-yellow);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="stat-value" style="font-size:1.35rem;">{{ formatCurrency(globalTotalAmount) }}</div>
                <div class="stat-change">Toutes dépenses fournisseurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total payé</span>
                    <div class="stat-icon" style="background:rgba(0,230,118,0.15);color:var(--accent-green);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value" style="font-size:1.35rem;">{{ formatCurrency(globalTotalPaid) }}</div>
                <div class="stat-change">Règlements effectués</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Reste à payer</span>
                    <div class="stat-icon" style="background:rgba(255,59,59,0.15);color:var(--accent-red);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-value" style="font-size:1.35rem;">{{ formatCurrency(globalTotalAmount - globalTotalPaid) }}</div>
                <div class="stat-change">Solde dû</div>
            </div>
        </div>

        <!-- ── Tableau fournisseurs ── -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-truck" style="color:var(--accent-cyan);"></i> Fournisseurs
                </h2>
                <div style="display:flex;gap:0.5rem;">
                    <button class="btn btn-print btn-sm" @click="printAllSuppliers" title="Imprimer tous les fournisseurs">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <button class="btn btn-secondary btn-sm" @click="fetchAll">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
            </div>

            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" v-model="searchQuery" placeholder="Rechercher un fournisseur…">
                </div>
            </div>

            <div class="table-container">
                <div v-if="loading" class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i><p>Chargement…</p>
                </div>
                <div v-else-if="filteredSuppliers.length === 0" class="empty-state">
                    <i class="fas fa-building"></i><p>Aucun fournisseur trouvé</p>
                </div>

                <table v-else class="table">
                    <thead>
                        <tr>
                            <th>Fournisseur</th>
                            <th>Dépenses</th>
                            <th>Total commandé</th>
                            <th>Total payé</th>
                            <th>Reste à payer</th>
                            <th>Règlement</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in paginatedSuppliers" :key="s.id">

                            <td data-label="Fournisseur">
                                <div class="supplier-name-cell">
                                    <div class="supplier-avatar-sm">{{ s.name.charAt(0) }}</div>
                                    <div>
                                        <div class="supplier-name-main">{{ s.name }}</div>
                                        <div class="supplier-id-sub">#{{ s.id }}</div>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Dépenses">
                                <span class="badge badge-blue">{{ supplierExpenseCount(s.id) }}</span>
                            </td>

                            <td data-label="Total commandé" style="color:var(--accent-yellow);font-weight:700;">
                                {{ formatCurrency(supplierTotalAmount(s.id)) }}
                            </td>

                            <td data-label="Total payé" style="color:var(--accent-green);font-weight:700;">
                                {{ formatCurrency(supplierTotalPaid(s.id)) }}
                            </td>

                            <td data-label="Reste à payer" :style="{color: supplierBalance(s.id) > 0 ? 'var(--accent-red)' : 'var(--accent-green)', fontWeight:'700'}">
                                {{ formatCurrency(supplierBalance(s.id)) }}
                            </td>

                            <td data-label="Règlement">
                                <div class="progress-wrap">
                                    <div class="progress-track">
                                        <div class="progress-fill" :style="{width: supplierPaymentRate(s.id) + '%'}"></div>
                                    </div>
                                    <div class="progress-pct">{{ supplierPaymentRate(s.id) }}%</div>
                                </div>
                            </td>

                            <td data-label="Statut">
                                <span class="badge" :class="supplierBalance(s.id) <= 0 ? 'badge-success' : 'badge-warning'">
                                    {{ supplierBalance(s.id) <= 0 ? 'Soldé' : 'En cours' }}
                                </span>
                            </td>

                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <button class="btn btn-secondary btn-icon" @click="openDetail(s)" title="Voir les dépenses">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-print btn-icon" @click="printSupplierHistory(s)" title="Imprimer l'historique">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button v-if="canEdit" class="btn btn-warning btn-icon" @click="openEdit(s)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrap" v-if="totalPages > 1">
                <button @click="currentPage--" :disabled="currentPage === 1"><i class="fas fa-chevron-left"></i></button>
                <template v-for="p in getVisiblePages(currentPage, totalPages)" :key="p">
                    <span v-if="p === '...'" class="pg-ellipsis">…</span>
                    <button v-else @click="currentPage = p" :class="{ active: currentPage === p }">{{ p }}</button>
                </template>
                <button @click="currentPage++" :disabled="currentPage === totalPages"><i class="fas fa-chevron-right"></i></button>
                <span class="pg-info">{{ currentPage }} / {{ totalPages }}</span>
                <span class="pg-goto">
                    Aller à
                    <input type="number" v-model="goToPage" :min="1" :max="totalPages"
                        @keydown.enter="handleGoToPage" placeholder="n°">
                    <button @click="handleGoToPage"><i class="fas fa-arrow-right"></i></button>
                </span>
            </div>

        </div>

    </main>

    <!-- ── Footer ── -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-info">© 2026 OrizonPlus • système de gestion | Version 1.0.0</div>
            <div class="footer-info">
                Built with Blood, Sweat and Tears by
                <a href="https://rachad-alabi-adekambi.github.io/portfolio/" style="color:white;font-weight:bold;text-decoration:none;">RA</a>
            </div>
        </div>
    </footer>

    <!-- ════════════════════════════════
         Modal — Détail fournisseur
    ════════════════════════════════ -->
    <div class="modal-overlay" :class="{ active: modals.detail }" @click.self="closeDetail">
        <div class="modal" style="max-width:760px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-building" style="color:var(--accent-cyan);"></i> Fiche Fournisseur
                </h3>
                <button class="modal-close" @click="closeDetail"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body" v-if="detailSupplier">

                <div class="detail-hero">
                    <div class="detail-avatar">{{ detailSupplier.name.charAt(0) }}</div>
                    <div>
                        <div class="detail-name">{{ detailSupplier.name }}</div>
                        <div class="detail-sub">#{{ detailSupplier.id }} · {{ supplierExpenseCount(detailSupplier.id) }} dépense(s)</div>
                    </div>
                </div>

                <div class="detail-summary">
                    <div class="summary-card">
                        <div class="summary-label">Total commandé</div>
                        <div class="summary-value" style="color:var(--accent-yellow);">{{ formatCurrency(supplierTotalAmount(detailSupplier.id)) }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Total payé</div>
                        <div class="summary-value" style="color:var(--accent-green);">{{ formatCurrency(supplierTotalPaid(detailSupplier.id)) }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Reste à payer</div>
                        <div class="summary-value" :style="{color: supplierBalance(detailSupplier.id) > 0 ? 'var(--accent-red)' : 'var(--accent-green)'}">
                            {{ formatCurrency(supplierBalance(detailSupplier.id)) }}
                        </div>
                    </div>
                </div>

                <div class="detail-progress">
                    <div class="detail-progress-label">
                        <span>Taux de règlement</span>
                        <strong style="color:var(--text-primary);">{{ supplierPaymentRate(detailSupplier.id) }}%</strong>
                    </div>
                    <div class="detail-progress-track">
                        <div class="detail-progress-fill" :style="{width: supplierPaymentRate(detailSupplier.id) + '%'}"></div>
                    </div>
                </div>

                <div class="section-sub-title"><i class="fas fa-receipt"></i> Dépenses associées</div>

                <div v-if="supplierExpenses(detailSupplier.id).length === 0" class="empty-state" style="padding:1.5rem;">
                    <i class="fas fa-inbox"></i><p>Aucune dépense enregistrée</p>
                </div>
                <div v-else>
                    <div v-for="exp in paginatedDetailExpenses" :key="exp.id" class="exp-row">
                        <div class="exp-row-top">
                            <div>
                                <div class="exp-row-project">{{ exp.project_name }}</div>
                                <div class="exp-row-meta">
                                    {{ exp.budget_line_name }} · {{ formatDate(exp.expense_date) }}
                                    <span v-if="exp.description && exp.description.trim()"> · <em>{{ exp.description }}</em></span>
                                </div>
                            </div>
                            <span class="badge" :class="getPaymentBadge(exp)">{{ getPaymentLabel(exp) }}</span>
                        </div>
                        <div class="exp-amounts">
                            <div class="exp-amount-block">
                                <div class="exp-amount-label">Montant dépense</div>
                                <div class="exp-amount-value" style="color:var(--accent-yellow);">{{ formatCurrency(exp.amount) }}</div>
                            </div>
                            <div class="exp-amount-block">
                                <div class="exp-amount-label">Montant payé</div>
                                <div class="exp-amount-value" style="color:var(--accent-green);">
                                    {{ exp.paid_amount ? formatCurrency(exp.paid_amount) : '—' }}
                                </div>
                            </div>
                            <div class="exp-amount-block">
                                <div class="exp-amount-label">Reste à payer</div>
                                <div class="exp-amount-value" :style="{color: getRemainingAmount(exp) > 0 ? 'var(--accent-red)' : 'var(--accent-green)'}">
                                    {{ formatCurrency(getRemainingAmount(exp)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pagination-wrap" v-if="detailTotalPages > 1">
                        <button @click="detailPage--" :disabled="detailPage === 1"><i class="fas fa-chevron-left"></i></button>
                        <template v-for="p in getVisiblePages(detailPage, detailTotalPages)" :key="p">
                            <span v-if="p === '...'" class="pg-ellipsis">…</span>
                            <button v-else @click="detailPage = p" :class="{ active: detailPage === p }">{{ p }}</button>
                        </template>
                        <button @click="detailPage++" :disabled="detailPage === detailTotalPages"><i class="fas fa-chevron-right"></i></button>
                        <span class="pg-info">{{ detailPage }} / {{ detailTotalPages }}</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button @click="closeDetail" class="btn btn-secondary">Fermer</button>
                <button v-if="canEdit && detailSupplier" class="btn btn-warning" @click="openEdit(detailSupplier); closeDetail();">
                    <i class="fas fa-edit"></i> Modifier
                </button>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════
         Modal — Modifier fournisseur
    ════════════════════════════════ -->
    <div class="modal-overlay" :class="{ active: modals.edit }" @click.self="closeEdit">
        <div class="modal" style="max-width:400px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit" style="color:var(--accent-blue);"></i> Modifier le fournisseur</h3>
                <button class="modal-close" @click="closeEdit"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" v-if="editSupplier">
                <div class="form-group">
                    <label class="form-label">Nom du fournisseur *</label>
                    <input type="text" class="form-input" v-model="editSupplier.name"
                        placeholder="Nom du fournisseur" @keydown.enter="saveEdit">
                </div>
            </div>
            <div class="modal-footer">
                <button @click="closeEdit" class="btn btn-secondary">Annuler</button>
                <button @click="saveEdit" class="btn btn-primary" :disabled="isSaving">
                    <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                    {{ isSaving ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </div>
        </div>
    </div>

</div>
<script>
const API_BASE_URL = 'api/index.php';
const { createApp } = Vue;

createApp({
    data() {
        return {
            canEdit:        <?= $canEdit ? 'true' : 'false' ?>,
            user_role:      '<?= htmlspecialchars($_SESSION['user_role'] ?? 'consultant') ?>',
            user_id:        <?= (int)$user_id ?>,
            unreadCount:    0,
            menuOpen:       false,
            loading:        true,
            isSaving:       false,
            suppliers:      [],
            expenses:       [],
            searchQuery:    '',
            currentPage:    1,
            perPage:        10,
            goToPage:       '',
            modals:         { detail: false, edit: false },
            detailSupplier: null,
            editSupplier:   null,
            detailPage:     1,
            detailPerPage:  5,
        };
    },

    computed: {
        filteredSuppliers() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) return this.suppliers;
            return this.suppliers.filter(s => s.name.toLowerCase().includes(q));
        },
        paginatedSuppliers() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredSuppliers.slice(start, start + this.perPage);
        },
        totalPages() {
            return Math.max(1, Math.ceil(this.filteredSuppliers.length / this.perPage));
        },
        supplierIds() {
            return new Set(this.suppliers.map(s => String(s.id)));
        },
        supplierExpensesOnly() {
            return this.expenses.filter(e => e.supplier_id && this.supplierIds.has(String(e.supplier_id)));
        },
        globalTotalAmount() {
            return this.supplierExpensesOnly.reduce((s, e) => s + parseFloat(e.amount || 0), 0);
        },
        globalTotalPaid() {
            return this.supplierExpensesOnly.reduce((s, e) => s + parseFloat(e.paid_amount || 0), 0);
        },
        paginatedDetailExpenses() {
            if (!this.detailSupplier) return [];
            const list  = this.supplierExpenses(this.detailSupplier.id);
            const start = (this.detailPage - 1) * this.detailPerPage;
            return list.slice(start, start + this.detailPerPage);
        },
        detailTotalPages() {
            if (!this.detailSupplier) return 1;
            return Math.max(1, Math.ceil(this.supplierExpenses(this.detailSupplier.id).length / this.detailPerPage));
        },
    },

    watch: {
        searchQuery() { this.currentPage = 1; }
    },

    mounted() {
        this.fetchAll();
        this.fetchNotifications();
    },

    methods: {
        /* ── Data ── */
        async fetchAll() {
            this.loading = true;
            await Promise.all([this.fetchSuppliers(), this.fetchExpenses()]);
            this.loading = false;
        },
        async fetchSuppliers() {
            try {
                const res  = await fetch(`${API_BASE_URL}?action=getSuppliers`);
                const data = await res.json();
                this.suppliers = data.data || [];
            } catch(e) { console.error('fetchSuppliers', e); }
        },
        async fetchExpenses() {
            try {
                const res  = await fetch(`${API_BASE_URL}?action=getExpenses`);
                const data = await res.json();
                this.expenses = data.data || [];
            } catch(e) { console.error('fetchExpenses', e); }
        },
        async fetchNotifications() {
            try {
                const res  = await fetch(`${API_BASE_URL}?action=getNotifications`);
                const data = await res.json();
                if (!data.success) return;
                const notifs = data.data || [];
                this.unreadCount = this.user_role === 'admin'
                    ? notifs.filter(n => n.is_read == 0 && n.user_name === 'admin').length
                    : notifs.filter(n => n.is_read == 0 && n.user_id == this.user_id).length;
            } catch(e) { /* silent */ }
        },

        /* ── Supplier helpers ── */
        supplierExpenses(id)    { return this.expenses.filter(e => String(e.supplier_id) === String(id)); },
        supplierExpenseCount(id){ return this.supplierExpenses(id).length; },
        supplierTotalAmount(id) { return this.supplierExpenses(id).reduce((s,e) => s + parseFloat(e.amount     || 0), 0); },
        supplierTotalPaid(id)   { return this.supplierExpenses(id).reduce((s,e) => s + parseFloat(e.paid_amount || 0), 0); },
        supplierBalance(id)     { return this.supplierTotalAmount(id) - this.supplierTotalPaid(id); },
        supplierPaymentRate(id) {
            const total = this.supplierTotalAmount(id);
            return total <= 0 ? 0 : Math.min(100, Math.round((this.supplierTotalPaid(id) / total) * 100));
        },

        /* ── Expense helpers ── */
        getRemainingAmount(exp) { return Math.max(0, parseFloat(exp.amount||0) - parseFloat(exp.paid_amount||0)); },
        getPaymentBadge(exp) {
            const rem = this.getRemainingAmount(exp);
            if (rem <= 0) return 'badge-success';
            if (exp.paid_amount && parseFloat(exp.paid_amount) > 0) return 'badge-warning';
            return 'badge-danger';
        },
        getPaymentLabel(exp) {
            const rem = this.getRemainingAmount(exp);
            if (rem <= 0) return 'Soldé';
            if (exp.paid_amount && parseFloat(exp.paid_amount) > 0) return 'Partiel';
            return 'Non payé';
        },

        /* ── Modals ── */
        openDetail(s) { this.detailSupplier = s; this.detailPage = 1; this.modals.detail = true; },
        closeDetail()  { this.modals.detail = false; this.detailSupplier = null; },
        openEdit(s)   { this.editSupplier = { ...s }; this.modals.edit = true; },
        closeEdit()    { this.modals.edit = false; this.editSupplier = null; },

        async saveEdit() {
            if (!this.editSupplier?.name?.trim()) { alert('Le nom est obligatoire.'); return; }
            this.isSaving = true;
            try {
                const fd = new FormData();
                fd.append('id',   this.editSupplier.id);
                fd.append('name', this.editSupplier.name.trim());
                const res  = await fetch(`${API_BASE_URL}?action=updateSupplier`, { method:'POST', body:fd });
                const data = await res.json();
                if (!data.success) { alert(data.message || 'Erreur'); return; }
                const idx = this.suppliers.findIndex(s => s.id === this.editSupplier.id);
                if (idx !== -1) this.suppliers[idx].name = this.editSupplier.name.trim();
                this.closeEdit();
            } catch(e) { alert('Erreur réseau'); }
            finally     { this.isSaving = false; }
        },

        /* ── Pagination ── */
        getVisiblePages(current, total) {
            const pages = [];
            if (total <= 1) return pages;
            pages.push(1);
            if (current - 1 > 2) pages.push('...');
            for (let p = Math.max(2, current-1); p <= Math.min(total-1, current+1); p++) pages.push(p);
            if (current + 1 < total - 1) pages.push('...');
            if (total > 1) pages.push(total);
            return pages;
        },
        handleGoToPage() {
            const n = parseInt(this.goToPage);
            if (!isNaN(n) && n >= 1 && n <= this.totalPages) this.currentPage = n;
            this.goToPage = '';
        },

        /* ── Print ── */
        _buildPrintHeader(title, subtitle) {
            return `
            <div style="
                display:flex; align-items:center; justify-content:space-between;
                background:#2d3b8e; color:#fff;
                padding:22px 32px; margin-bottom:28px;
                border-radius:12px;
                font-family:Arial,sans-serif;
                min-height:90px;
            ">
                <!-- Gauche : titre + sous-titre -->
                <div style="flex:1;">
                    <div style="font-size:22px;font-weight:800;color:#fff;line-height:1.2;letter-spacing:0.2px;">${title}</div>
                    <div style="font-size:13px;color:rgba(255,255,255,0.80);margin-top:6px;font-weight:400;">${subtitle}</div>
                </div>
                <!-- Centre-droite : logo sur fond blanc arrondi -->
                <div style="
                    background:#fff; border-radius:10px;
                    padding:8px 14px; margin: 0 28px;
                    display:flex; align-items:center; justify-content:center;
                ">
                    <img src="images/logo_kamus.png" alt="KAM US"
                         style="height:52px;width:auto;object-fit:contain;display:block;">
                </div>
                <!-- Droite : nom société -->
                <div style="
                    font-size:12px;font-weight:700;color:rgba(255,255,255,0.90);
                    letter-spacing:0.5px; text-align:right; white-space:nowrap;
                ">KAM UNITED SOCIETY</div>
            </div>`;
        },

        _openPrintWindow(htmlBody) {
            const now = new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });
            const footer = `
                <div style="
                    border-top:1px solid #e5e7eb; margin-top:40px; padding-top:18px;
                    display:flex; justify-content:space-between; align-items:flex-start;
                    font-family:Arial,sans-serif;
                ">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#111;">KAM UNITED SOCIETY</div>
                        <div style="font-size:11px;color:#555;margin-top:3px;">Document généré le ${now}</div>
                        <div style="font-size:11px;color:#555;font-style:italic;margin-top:2px;">Ce document est confidentiel.</div>
                    </div>
                    <img src="images/logo_kamus.png" alt="KAM US" style="height:44px;width:auto;object-fit:contain;">
                </div>`;

            const win = window.open('', '_blank', 'width=1100,height=800');
            win.document.write(`<!DOCTYPE html><html lang="fr"><head>
                <meta charset="UTF-8">
                <title>Impression — OrizonPlus</title>
                <style>
                    * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; box-sizing:border-box; margin:0; padding:0; }
                    body { font-family:Arial,sans-serif; background:#fff; color:#111; padding:28px 32px; }
                    table { width:100%; border-collapse:collapse; font-size:11.5px; margin-top:8px; }
                    th { background:#2d3b8e !important; color:#fff !important; padding:8px 10px; text-align:left; font-weight:700; border:1px solid #1e2d7a; font-size:11px; text-transform:uppercase; letter-spacing:0.3px; }
                    td { padding:7px 10px; border:1px solid #e5e7eb; vertical-align:middle; }
                    tr:nth-child(even) td { background:#f5f7ff; }
                    .summary { display:flex; gap:14px; margin-bottom:20px; flex-wrap:wrap; }
                    .sum-card { background:#f0f4ff; border-left:4px solid #2d3b8e; border-radius:0 8px 8px 0; padding:10px 16px; flex:1; min-width:130px; }
                    .sum-label { font-size:9.5px; text-transform:uppercase; color:#666; letter-spacing:0.5px; margin-bottom:4px; }
                    .sum-val { font-size:15px; font-weight:800; color:#1e2d7a; }
                    .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:10px; font-weight:700; }
                    .b-green  { background:#d1fae5; color:#065f46; }
                    .b-yellow { background:#fef3c7; color:#92400e; }
                    .b-red    { background:#fee2e2; color:#991b1b; }
                    h2 { font-size:13px; font-weight:700; text-transform:uppercase; color:#2d3b8e;
                         letter-spacing:0.5px; margin:22px 0 10px; border-bottom:2px solid #2d3b8e; padding-bottom:5px; }
                    .print-btn {
                        display:block; margin:28px auto 0; background:#2d3b8e; color:#fff;
                        border:none; padding:11px 32px; border-radius:8px;
                        font-size:14px; font-weight:700; cursor:pointer; letter-spacing:0.3px;
                    }
                    .print-btn:hover { background:#1e2d7a; }
                    @media print { .print-btn { display:none !important; } }
                </style>
            </head><body>
                ${htmlBody}
                ${footer}
                <button class="print-btn" onclick="window.print()">🖨 Imprimer / Enregistrer en PDF</button>
            </body></html>`);
            win.document.close();
        },

        printAllSuppliers() {
            const header = this._buildPrintHeader('Rapport global', 'État de tous les fournisseurs');

            let rows = this.filteredSuppliers.map(s => {
                const bal = this.supplierBalance(s.id);
                const rate = this.supplierPaymentRate(s.id);
                const badgeClass = bal <= 0 ? 'b-green' : 'b-yellow';
                const badgeLabel = bal <= 0 ? 'Soldé' : 'En cours';
                return `<tr>
                    <td>${s.name}</td>
                    <td style="text-align:center;">${this.supplierExpenseCount(s.id)}</td>
                    <td style="text-align:right;color:#b45309;font-weight:700;">${this.formatCurrency(this.supplierTotalAmount(s.id))}</td>
                    <td style="text-align:right;color:#065f46;font-weight:700;">${this.formatCurrency(this.supplierTotalPaid(s.id))}</td>
                    <td style="text-align:right;color:${bal > 0 ? '#991b1b' : '#065f46'};font-weight:700;">${this.formatCurrency(bal)}</td>
                    <td style="text-align:center;">${rate}%</td>
                    <td style="text-align:center;"><span class="badge ${badgeClass}">${badgeLabel}</span></td>
                </tr>`;
            }).join('');

            if (!rows) rows = '<tr><td colspan="7" style="text-align:center;color:#888;">Aucun fournisseur</td></tr>';

            const html = `
                ${header}
                <div class="summary">
                    <div class="sum-card"><div class="sum-label">Fournisseurs</div><div class="sum-val">${this.suppliers.length}</div></div>
                    <div class="sum-card"><div class="sum-label">Total commandé</div><div class="sum-val">${this.formatCurrency(this.globalTotalAmount)}</div></div>
                    <div class="sum-card"><div class="sum-label">Total payé</div><div class="sum-val">${this.formatCurrency(this.globalTotalPaid)}</div></div>
                    <div class="sum-card"><div class="sum-label">Reste à payer</div><div class="sum-val">${this.formatCurrency(this.globalTotalAmount - this.globalTotalPaid)}</div></div>
                </div>
                <h2>Liste des fournisseurs</h2>
                <table>
                    <thead><tr>
                        <th>Fournisseur</th><th>Dépenses</th><th>Total commandé</th>
                        <th>Total payé</th><th>Reste à payer</th><th>Règlement</th><th>Statut</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>`;
            this._openPrintWindow(html);
        },

        printSupplierHistory(s) {
            const header = this._buildPrintHeader('Rapport du fournisseur ' + s.name, 'Historique des dépenses');
            const exps = this.supplierExpenses(s.id);
            const totalCmd  = this.supplierTotalAmount(s.id);
            const totalPaid = this.supplierTotalPaid(s.id);
            const balance   = this.supplierBalance(s.id);
            const rate      = this.supplierPaymentRate(s.id);

            let rows = exps.map(exp => {
                const rem = this.getRemainingAmount(exp);
                const status = rem <= 0 ? 'Soldé' : (exp.paid_amount && parseFloat(exp.paid_amount) > 0 ? 'Partiel' : 'Non payé');
                const bc = rem <= 0 ? 'b-green' : (parseFloat(exp.paid_amount||0) > 0 ? 'b-yellow' : 'b-red');
                return `<tr>
                    <td>${exp.project_name || '—'}</td>
                    <td>${exp.budget_line_name || '—'}</td>
                    <td>${this.formatDate(exp.expense_date)}</td>
                    <td>${exp.description || '—'}</td>
                    <td style="text-align:right;color:#b45309;font-weight:700;">${this.formatCurrency(exp.amount)}</td>
                    <td style="text-align:right;color:#065f46;font-weight:700;">${exp.paid_amount ? this.formatCurrency(exp.paid_amount) : '—'}</td>
                    <td style="text-align:right;color:${rem > 0 ? '#991b1b' : '#065f46'};font-weight:700;">${this.formatCurrency(rem)}</td>
                    <td style="text-align:center;"><span class="badge ${bc}">${status}</span></td>
                </tr>`;
            }).join('');

            if (!rows) rows = '<tr><td colspan="8" style="text-align:center;color:#888;">Aucune dépense enregistrée</td></tr>';

            const html = `
                ${header}
                <div class="summary">
                    <div class="sum-card"><div class="sum-label">Total commandé</div><div class="sum-val">${this.formatCurrency(totalCmd)}</div></div>
                    <div class="sum-card"><div class="sum-label">Total payé</div><div class="sum-val">${this.formatCurrency(totalPaid)}</div></div>
                    <div class="sum-card"><div class="sum-label">Reste à payer</div><div class="sum-val">${this.formatCurrency(balance)}</div></div>
                    <div class="sum-card"><div class="sum-label">Taux de règlement</div><div class="sum-val">${rate}%</div></div>
                </div>
                <h2>Dépenses associées (${exps.length})</h2>
                <table>
                    <thead><tr>
                        <th>Projet</th><th>Ligne budgétaire</th><th>Date</th><th>Description</th>
                        <th>Montant</th><th>Payé</th><th>Reste</th><th>Statut</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>`;
            this._openPrintWindow(html);
        },

        /* ── Formatters ── */
        formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency', currency: 'XOF', minimumFractionDigits: 0
            }).format(parseFloat(value) || 0);
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('fr-FR');
        },
    }
}).mount('#app');
</script>
</body>
</html>