<?php
session_start();
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
    <title>OrizonPlus - Gestion de Projets</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
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

        /* ===== PRINT ===== */
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

            .modal {
                border: 1px solid #000;
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
            h3,
            h4 {
                color: black !important;
                page-break-after: avoid;
            }

            canvas {
                max-height: 400px !important;
                page-break-inside: avoid;
            }

            .chart-container {
                page-break-inside: avoid;
                height: auto !important;
                min-height: 300px;
            }
        }

        /* ===== HEADER / NAV ===== */
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

        /* ===== LAYOUT ===== */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* ===== STATS ===== */
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

        /* ===== CARDS ===== */
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

        /* ===== FILTERS ===== */
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

        /* ===== BUTTONS ===== */
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

        .btn-warning {
            background: var(--accent-yellow);
            color: #111;
        }

        .btn-danger {
            background: var(--accent-red);
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

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            justify-content: center;
        }

        /* ===== TABLE ===== */
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
        }

        .table th:hover {
            color: var(--text-primary);
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

        .table tbody tr.budget-exceeded {
            background: rgba(255, 59, 59, 0.1);
            border-left: 3px solid var(--accent-red);
        }

        .table tbody tr.budget-warning {
            background: rgba(255, 184, 0, 0.1);
            border-left: 3px solid var(--accent-yellow);
        }

        /* ===== BADGES ===== */
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

        .badge-info {
            background: rgba(0, 212, 255, 0.2);
            color: var(--accent-cyan);
        }

        /* ===== PROGRESS ===== */
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
        }

        .progress-bar.danger {
            background: linear-gradient(135deg, #ff3b3b, #ff6b6b);
        }

        .progress-bar.warning {
            background: linear-gradient(135deg, #ffb800, #ffd000);
        }

        /* ===== MODAL ===== */
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
            max-width: 900px;
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

        /* ===== FORMS ===== */
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

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* ===== CHARTS ===== */
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

        /* ===== MISC ===== */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .budget-line-row {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
        }

        .budget-line-row .line-name {
            flex: 2;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
            padding: 0.5rem 0;
        }

        .budget-line-row .form-input {
            flex: 1;
        }

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

        /* ===== PAGINATION ===== */
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

        /* ===== ALERTS ===== */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background: rgba(255, 59, 59, 0.2);
            color: var(--accent-red);
            border: 1px solid var(--accent-red);
        }

        .alert-warning {
            background: rgba(255, 184, 0, 0.2);
            color: var(--accent-yellow);
            border: 1px solid var(--accent-yellow);
        }

        /* ===== INFO GRID ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-box {
            background: var(--bg-tertiary);
            padding: 1rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
        }

        .info-box-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .info-box-value {
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* ===== TABS ===== */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .tab-btn.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* ===== SUMMARY ===== */
        .summary-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .summary-item .value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        /* ===== EXPENSE DOCS ===== */
        .expense-docs-btn {
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .expense-docs-btn:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        /* ===== DOC VIEWER MODAL ===== */
        .doc-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .doc-nav-pages {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* ===== DETAIL LINES TABLE ===== */
        .expense-detail-table td,
        .expense-detail-table th {
            font-size: 0.85rem;
            padding: 0.75rem;
        }

        .detail-lines-table .progress {
            height: 6px;
            margin-top: 0.25rem;
        }

        /* ===== PRINT FOOTER ===== */
        .print-footer {
            display: none;
        }

        /* ===== RESPONSIVE ===== */
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

            .container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
            }

            .modal {
                max-width: 95%;
                width: 95%;
            }

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
                min-width: 0;
            }

            .table tbody td>* {
                flex-shrink: 0;
                word-break: break-word;
                overflow-wrap: break-word;
            }

            .table tbody td strong {
                word-break: break-word;
                overflow-wrap: break-word;
                max-width: 100%;
            }

            .badge {
                word-break: normal;
                white-space: nowrap;
            }

            .table tbody td .progress {
                width: 100px;
                margin-top: 0;
            }

            .table tbody td .action-buttons {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .budget-line-row {
                flex-direction: column;
                align-items: stretch;
            }

            .budget-line-row .line-name {
                padding: 0.5rem 0;
            }

            .budget-line-input {
                flex-direction: column;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .chart-container {
                height: 300px;
                min-height: 250px;
            }
        }

        @media (max-width: 1024px) {
            .chart-container {
                height: 350px;
                min-height: 300px;
            }
        }
    </style>
</head>

<body>
    <div id="app">

        <!-- ===== HEADER ===== -->
        <header class="header no-print">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php"><img src="logo.png" alt="OrizonPlus"></a>
                </div>
                <button class="hamburger-btn" @click="toggleMobileMenu" aria-label="Toggle menu">
                    <i class="fas" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
                <ul class="nav-menu" :class="{ active: mobileMenuOpen }">
                    <li><a href="index.php" class="nav-link active" @click="closeMobileMenu"><i class="fas fa-folder-open"></i> Projets</a></li>
                    <li><a href="expenses.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-receipt"></i> Dépenses</a></li>
                    <li v-if="user_role=='admin'"><a href="users.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-users"></i> Utilisateurs</a></li>
                    <li v-if="user_role=='admin' || user_role=='utilisateur'"><a href="notifications.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li v-if="user_role=='utilisateur' || user_role=='consultant'"><a href="parameters.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-cog"></i> Paramètres</a></li>
                    <li><a href="api/index.php?action=logout" class="nav-link" style="color:var(--accent-red);" @click="closeMobileMenu"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </div>
        </header>

        <div class="container">

            <!-- Salutation -->
            <p style="margin-bottom:5px">Bonjour <?= ucfirst($_SESSION['user_name']) ?>, vous êtes connecté en tant que <strong>{{ user_role }}</strong></p>

            <!-- ===== STATS ===== -->
            <div v-if="!showBudgetLines" class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Projets</span>
                        <div class="stat-icon" style="background:rgba(0,112,243,0.2);color:var(--accent-blue);"><i class="fas fa-folder"></i></div>
                    </div>
                    <div class="stat-value">{{ stats.totalProjects }}</div>
                    <div class="stat-change positive"><i class="fas fa-check-circle"></i> Projets actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Budget Total</span>
                        <div class="stat-icon" style="background:rgba(124,58,237,0.2);color:var(--accent-purple);"><i class="fas fa-wallet"></i></div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalBudget) }}</div>
                    <div class="stat-change positive"><i class="fas fa-dollar-sign"></i> Budget alloué</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Réalisation</span>
                        <div class="stat-icon" style="background:rgba(0,230,118,0.2);color:var(--accent-green);"><i class="fas fa-chart-pie"></i></div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalSpent) }}</div>
                    <div class="stat-change" :class="stats.spentPercentage>80?'negative':'positive'">{{ stats.spentPercentage.toFixed(1) }}% utilisé</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Écart</span>
                        <div class="stat-icon" style="background:rgba(0,212,255,0.2);color:var(--accent-cyan);"><i class="fas fa-piggy-bank"></i></div>
                    </div>
                    <div class="stat-value">{{ formatCurrency(stats.totalRemaining) }}</div>
                    <div class="stat-change" :class="stats.totalRemaining<0?'negative':'positive'">
                        <i :class="stats.totalRemaining<0?'fas fa-exclamation-triangle':'fas fa-check-circle'"></i>
                        {{ stats.totalRemaining<0?'Budget dépassé':'Dans le budget' }}
                    </div>
                </div>
            </div>

            <!-- ===== GRAPHIQUES GLOBAUX ===== -->
            <div v-if="!showBudgetLines" class="section-card no-print">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-bar"></i> Visualisations</h2>
                    <button class="btn btn-primary btn-sm" @click="printChartsPage"><i class="fas fa-print"></i> Imprimer Graphiques</button>
                </div>
                <div class="section-content">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:2rem;width:100%;">
                        <div>
                            <h3 style="margin-bottom:1rem;color:var(--text-secondary);font-size:1rem;">Répartition des Budgets</h3>
                            <div class="chart-container"><canvas ref="budgetPieChart" role="img" aria-label="Répartition des budgets"></canvas></div>
                        </div>
                        <div>
                            <h3 style="margin-bottom:1rem;color:var(--text-secondary);font-size:1rem;">Budget vs Réalisations par Projet</h3>
                            <div class="chart-container"><canvas ref="progressBarChart" role="img" aria-label="Budget vs Réalisations"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== ALERTES BUDGET ===== -->
            <div v-if="!showBudgetLines && budgetAlerts.length > 0" class="no-print" style="margin-bottom:1.5rem;">
                <div v-for="al in budgetAlerts" :key="al.id" class="alert" :class="al.type==='exceeded'?'alert-danger':'alert-warning'">
                    <i :class="al.type==='exceeded'?'fas fa-exclamation-circle':'fas fa-exclamation-triangle'"></i>
                    <strong>{{ al.name }}</strong> : {{ al.type==='exceeded'?'Budget dépassé de '+formatCurrency(Math.abs(al.remaining)):'Budget critique ('+formatCurrency(al.remaining)+' restant)' }}
                </div>
            </div>

            <!-- ===== PROJETS ===== -->
            <div v-if="!showBudgetLines" class="section-card">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-folder"></i> Projets ({{ filteredProjects.length }})</h2>
                    <div class="action-buttons no-print">
                        <button v-if="user_role==='admin'" class="btn btn-warning btn-sm" @click="toggleBudgetLines"><i class="fas fa-list"></i> Gérer Lignes</button>
                        <button class="btn btn-success btn-sm" @click="printProjectsPage"><i class="fas fa-print"></i> Imprimer</button>
                        <button class="btn btn-secondary btn-sm" @click="exportAll"><i class="fas fa-download"></i> Exporter</button>
                        <button v-if="user_role==='admin'" class="btn btn-primary" @click="openProjectModal"><i class="fas fa-plus"></i> Nouveau Projet</button>
                    </div>
                </div>
                <div class="section-content">
                    <div class="filters no-print">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="search-input" placeholder="Rechercher un projet..." v-model="searchQuery" @input="filterProjects">
                        </div>
                        <select class="filter-select" v-model="sectorFilter" @change="filterProjects">
                            <option value="">Tous les secteurs</option>
                            <option value="Electricité">Electricité</option>
                            <option value="Télécommunication">Télécommunication</option>
                            <option value="Génie Civil">Génie Civil</option>
                            <option value="AEP">AEP</option>
                        </select>
                        <select class="filter-select" v-model="budgetFilter" @change="filterProjects">
                            <option value="">Tous les projets</option>
                            <option value="remaining">Budget restant</option>
                            <option value="over">Budget dépassé</option>
                            <option value="warning">Budget critique (&gt;80%)</option>
                        </select>
                        <select class="filter-select" v-model="sortBy" @change="sortProjects">
                            <option value="name">Trier par nom</option>
                            <option value="allocated_amount">Trier par budget</option>
                            <option value="spent">Trier par réalisation</option>
                            <option value="remaining">Trier par écart</option>
                            <option value="date_of_creation">Trier par date</option>
                            <option value="department">Trier par secteur</option>
                        </select>
                        <input type="date" class="filter-input" v-model="dateFrom" @change="filterProjects">
                        <input type="date" class="filter-input" v-model="dateTo" @change="filterProjects">
                    </div>

                    <div v-if="paginatedProjects.length===0" class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>Aucun projet trouvé</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th @click="setSortBy('name')">Projet <i v-if="sortBy==='name'" :class="sortAsc?'fas fa-arrow-up':'fas fa-arrow-down'"></i></th>
                                    <th @click="setSortBy('department')">Secteur <i v-if="sortBy==='department'" :class="sortAsc?'fas fa-arrow-up':'fas fa-arrow-down'"></i></th>
                                    <th>Localisation</th>
                                    <th @click="setSortBy('allocated_amount')">Budget Alloué <i v-if="sortBy==='allocated_amount'" :class="sortAsc?'fas fa-arrow-up':'fas fa-arrow-down'"></i></th>
                                    <th @click="setSortBy('spent')">Réalisations <i v-if="sortBy==='spent'" :class="sortAsc?'fas fa-arrow-up':'fas fa-arrow-down'"></i></th>
                                    <th @click="setSortBy('remaining')">Écart <i v-if="sortBy==='remaining'" :class="sortAsc?'fas fa-arrow-up':'fas fa-arrow-down'"></i></th>
                                    <th>Progression</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="project in paginatedProjects" :key="project.id" :class="getProjectRowClass(project)">
                                    <td data-label="Projet"><strong>{{ project.name }}</strong></td>
                                    <td data-label="Secteur">{{ project.department || '-' }}</td>
                                    <td data-label="Localisation">{{ project.location || '-' }}</td>
                                    <td data-label="Budget Alloué">{{ formatCurrency(getProjectAllocatedFromLines(project)) }}</td>
                                    <td data-label="Réalisations">{{ formatCurrency(project.spent) }}</td>
                                    <td data-label="Écart">
                                        <span class="badge" :class="getBadgeClass(getProjectRemaining(project))">{{ formatCurrency(getProjectRemaining(project)) }}</span>
                                    </td>
                                    <td data-label="Progression">
                                        <div>{{ getSpentPercentage(project).toFixed(1) }}%</div>
                                        <div class="progress">
                                            <div class="progress-bar" :class="getSpentPercentage(project)>100?'danger':getSpentPercentage(project)>80?'warning':''" :style="{width:Math.min(getSpentPercentage(project),100)+'%'}"></div>
                                        </div>
                                    </td>
                                    <td class="no-print" data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn btn-secondary btn-sm" @click="viewProject(project)" title="Voir détails"><i class="fas fa-eye"></i></button>
                                            <button v-if="user_role==='admin' && project.project_status==='Déverrouillé'" class="btn btn-primary btn-sm" @click="editProject(project)" title="Modifier"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-warning btn-sm" @click="printProjectDetails(project)" title="Imprimer"><i class="fas fa-print"></i></button>
                                            <button v-if="user_role==='admin' && project.project_status==='Déverrouillé'" class="btn btn-danger btn-sm" @click="deleteProject(project.id)" title="Supprimer"><i class="fas fa-trash"></i></button>
                                            <button v-if="user_role==='admin' && project.project_status==='Déverrouillé'" class="btn btn-secondary btn-sm" @click="lockProject(project.id)" title="Clôturer"><i class="fas fa-unlock"></i></button>
                                            <button v-if="user_role==='admin' && project.project_status==='Verrouillé'" class="btn btn-success btn-sm" @click="unlockProject(project.id)" title="Ouvrir"><i class="fas fa-lock-open"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pagination no-print">
                            <button @click="prevPage" :disabled="currentPage===1"><i class="fas fa-chevron-left"></i></button>
                            <button v-for="page in totalPages" :key="page" @click="currentPage=page" :class="{active:currentPage===page}">{{ page }}</button>
                            <button @click="nextPage" :disabled="currentPage===totalPages"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== LIGNES BUDGETAIRES ===== -->
            <div v-if="showBudgetLines" class="section-card">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i> Lignes Budgétaires</h2>
                    <div class="action-buttons no-print">
                        <button class="btn btn-primary btn-sm" @click="openLineModal"><i class="fas fa-plus"></i> Ajouter</button>
                        <button class="btn btn-success btn-sm" @click="printBudgetLinesPage"><i class="fas fa-print"></i> Imprimer</button>
                        <button class="btn btn-warning btn-sm" @click="toggleBudgetLines"><i class="fas fa-folder"></i> Gérer Projets</button>
                    </div>
                </div>
                <div class="section-content">
                    <div v-if="availableLines.length===0" class="empty-state"><i class="fas fa-inbox"></i>
                        <p>Aucune ligne budgétaire</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in availableLines" :key="line.id">
                                    <td data-label="Nom">
                                        <input v-if="editingLine===line.id" v-model="line.name" class="form-input" @keyup.enter="updateLine(line)" @blur="editingLine=null" />
                                        <span v-else @dblclick="editingLine=line.id">{{ line.name }}</span>
                                    </td>
                                    <td class="no-print" data-label="Actions">
                                        <button class="btn btn-success btn-sm" @click="updateLine(line)"><i class="fas fa-save"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /container -->

        <div class="pagefooter">
            <p style="text-align:center;margin-top:1rem;color:var(--text-secondary);font-size:0.8rem;">
                &copy; OrizonPlus 2026 &mdash; Built with Blood, Sweat and Tears by
                <a href="https://rachad-alabi-adekambi.github.io/portfolio/" style="color:white;font-weight:bold;text-decoration:none;">RA</a>
            </p>
        </div>

        <!-- ===== MODAL: Ligne Budgétaire ===== -->
        <div class="modal-overlay" :class="{active:modals.line}" @click.self="closeLineModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-tag"></i> Nouvelle Ligne Budgétaire</h3>
                    <button class="modal-close" @click="closeLineModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom de la ligne</label>
                        <input type="text" class="form-input" v-model="newLineName" placeholder="Ex: Salaires, Marketing, Infrastructure..." />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeLineModal">Annuler</button>
                    <button class="btn btn-primary" @click="createLine"><i class="fas fa-check"></i> Créer</button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL: Créer / Modifier Projet ===== -->
        <div class="modal-overlay" :class="{active:modals.project}" @click.self="closeProjectModal">
            <div class="modal" style="max-width:900px;">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-folder-plus"></i> {{ isEditMode?'Modifier le Projet':'Nouveau Projet' }}</h3>
                    <button class="modal-close" @click="closeProjectModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom du projet</label>
                        <input type="text" class="form-input" v-model="newProject.name" placeholder="Ex: Projet A" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-input" v-model="newProject.description" placeholder="Description du projet..." rows="3"></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Secteur</label>
                            <select class="form-input" v-model="newProject.department">
                                <option value="">Sélectionner un secteur</option>
                                <option value="Electricité">Electricité</option>
                                <option value="Télécommunication">Télécommunication</option>
                                <option value="Génie Civil">Génie Civil</option>
                                <option value="AEP">AEP</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lieu</label>
                            <input type="text" class="form-input" v-model="newProject.location" placeholder="Lieu du projet" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de création</label>
                            <input type="date" class="form-input" v-model="newProject.date_of_creation" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-paperclip" style="margin-right:0.5rem;"></i> Documents (Images ou PDF – Max 15)</label>
                        <input type="file" class="form-input" multiple accept="image/*,application/pdf" @change="handleFileUpload" style="padding:0.5rem;" />
                        <div v-if="newProject.documents && newProject.documents.length>0" style="margin-top:1rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:0.5rem;">
                            <div v-for="(doc,idx) in newProject.documents" :key="idx" style="position:relative;border:1px solid var(--border-color);border-radius:8px;padding:0.5rem;text-align:center;background:var(--bg-tertiary);">
                                <i :class="getDocIcon(doc)" style="font-size:2rem;color:var(--accent-blue);"></i>
                                <div style="font-size:0.7rem;margin-top:0.25rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ getDocName(doc) }}</div>
                                <button v-if="user_role==='admin'" @click="removeDocument(idx)" style="position:absolute;top:-8px;right:-8px;width:20px;height:20px;border-radius:50%;background:var(--accent-red);color:white;border:none;cursor:pointer;font-size:0.7rem;display:flex;align-items:center;justify-content:center;">×</button>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-file-contract" style="margin-right:0.5rem;"></i> N° Bon de commande</label>
                            <input type="text" class="form-input" v-model="newProject.contract_number" placeholder="Ex: BC-2024-001" />
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-handshake" style="margin-right:0.5rem;"></i> Montant du marché HT</label>
                            <input type="number" class="form-input" v-model.number="newProject.contract_amount_ht" placeholder="0" min="0" />
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-tools" style="margin-right:0.5rem;"></i> Budget d'exécution HT</label>
                            <input type="number" class="form-input" v-model.number="newProject.execution_budget_ht" placeholder="0" min="0" />
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-cash-register" style="margin-right:0.5rem;"></i> Montant encaissé HT</label>
                            <input type="number" class="form-input" v-model.number="newProject.collected_amount_ht" placeholder="0" min="0" />
                        </div>
                    </div>

                    <hr style="border-color:var(--border-color);margin:1.5rem 0;">

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-list" style="margin-right:0.5rem;"></i> Lignes budgétaires
                            <span v-if="projectLinesTotal>0" style="color:var(--accent-blue);margin-left:1rem;font-weight:700;">Total: {{ formatCurrency(projectLinesTotal) }}</span>
                        </label>

                        <!-- Lignes existantes (mode édition) -->
                        <div v-if="isEditMode && currentProjectLines.length>0" style="margin-bottom:1.5rem;">
                            <h4 style="color:var(--text-secondary);font-size:0.875rem;margin-bottom:1rem;"><i class="fas fa-layer-group" style="margin-right:0.5rem;"></i> Lignes existantes ({{ currentProjectLines.length }})</h4>
                            <div v-for="(line,index) in currentProjectLines" :key="'current-'+index" class="budget-line-row">
                                <div class="line-name"><i class="fas fa-tag" style="color:var(--accent-blue);margin-right:0.5rem;font-size:0.8rem;"></i>{{ line.line_name||line.name }}</div>
                                <input type="number" class="form-input" v-model.number="line.allocated_amount" @input="updateProjectLinesTotal" placeholder="Montant" style="flex:1;max-width:200px;" />
                                <button class="btn btn-danger btn-sm btn-icon" @click="deleteExistingLine(line)" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>

                        <!-- Nouvelles lignes -->
                        <div v-if="projectLines.length>0" style="margin-bottom:1rem;">
                            <h4 style="color:var(--text-secondary);font-size:0.875rem;margin-bottom:1rem;"><i class="fas fa-plus-circle" style="margin-right:0.5rem;"></i> Nouvelles lignes</h4>
                            <div v-for="(line,index) in projectLines" :key="'new-'+index" class="budget-line-input">
                                <select class="form-select" v-model="line.budget_line_id" @change="updateLineName(line)" style="flex:2;">
                                    <option value="">Sélectionner une ligne</option>
                                    <option v-for="availLine in availableLinesForNewLine(line.budget_line_id)" :key="availLine.id" :value="availLine.id">{{ availLine.name }}</option>
                                </select>
                                <input type="number" class="form-input" v-model.number="line.allocated_amount" @input="updateProjectLinesTotal" placeholder="Montant" />
                                <button class="btn btn-danger btn-sm" @click="removeProjectLine(index)"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>

                        <button v-if="availableLinesForNewLine('').length>0" class="btn btn-secondary btn-sm" @click="addProjectLine"><i class="fas fa-plus"></i> Ajouter une ligne</button>
                        <p v-else style="color:var(--text-secondary);font-size:0.875rem;margin-top:0.5rem;"><i class="fas fa-info-circle" style="margin-right:0.25rem;"></i> Toutes les lignes budgétaires sont utilisées</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeProjectModal">Annuler</button>
                    <button class="btn btn-success" @click="saveProject"><i class="fas fa-check"></i> {{ isEditMode?'Enregistrer':'Créer' }}</button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL: Détail Projet ===== -->
        <div class="modal-overlay" :class="{active:modals.detail}" @click.self="closeDetailModal">
            <div class="modal print-area" style="max-width:1100px;">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-eye"></i> Détails du Projet</h3>
                    <button class="modal-close no-print" @click="closeDetailModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedProject">
                        <!-- Titre + badges secteur/lieu -->
                        <div style="margin-bottom:1.5rem;">
                            <h3 style="font-size:1.5rem;margin-bottom:0.5rem;">{{ selectedProject.name }}</h3>
                            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                                <span v-if="selectedProject.department" class="badge badge-info"><i class="fas fa-industry" style="margin-right:0.3rem;"></i>{{ selectedProject.department }}</span>
                                <span v-if="selectedProject.location" class="badge badge-info"><i class="fas fa-map-marker-alt" style="margin-right:0.3rem;"></i>{{ selectedProject.location }}</span>
                                <span v-if="selectedProject.date_of_creation" class="badge badge-info"><i class="fas fa-calendar" style="margin-right:0.3rem;"></i>{{ formatDate(selectedProject.date_of_creation) }}</span>
                            </div>
                        </div>

                        <!-- Info Cards -->
                        <div class="info-grid">
                            <div class="info-box">
                                <div class="info-box-label">Budget Global (Alloué)</div>
                                <div class="info-box-value" style="color:var(--accent-blue);">{{ formatCurrency(selectedProjectLinesAllocatedTotal) }}</div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Montant Dépensé</div>
                                <div class="info-box-value" style="color:var(--accent-yellow);">
                                    {{ formatCurrency(selectedProjectLinesSpentTotal) }}
                                    <div style="font-size:0.75rem;color:var(--text-secondary);">{{ selectedProjectLinesAllocatedTotal>0?((selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}% du budget</div>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Montant Restant</div>
                                <div class="info-box-value" :style="{color:(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal)<0?'var(--accent-red)':'var(--accent-green)'}">
                                    {{ formatCurrency(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal) }}
                                    <div style="font-size:0.75rem;color:var(--text-secondary);">{{ selectedProjectLinesAllocatedTotal>0?(((selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal)/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}% du budget</div>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Progression</div>
                                <div class="info-box-value" :style="{color:(selectedProjectLinesAllocatedTotal>0?(selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal*100):0)>80?'var(--accent-red)':'var(--accent-green)'}">
                                    {{ selectedProjectLinesAllocatedTotal>0?((selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}%
                                </div>
                            </div>
                            <div class="info-box" v-if="selectedProject.contract_number">
                                <div class="info-box-label"><i class="fas fa-file-contract" style="margin-right:0.3rem;"></i> N° Bon de commande</div>
                                <div class="info-box-value" style="color:var(--accent-cyan);font-size:1rem;">{{ selectedProject.contract_number }}</div>
                            </div>
                            <div class="info-box" v-if="selectedProject.contract_amount_ht">
                                <div class="info-box-label"><i class="fas fa-handshake" style="margin-right:0.3rem;"></i> Montant du marché HT</div>
                                <div class="info-box-value" style="color:var(--accent-purple);">{{ formatCurrency(selectedProject.contract_amount_ht) }}</div>
                            </div>
                            <div class="info-box" v-if="selectedProject.execution_budget_ht">
                                <div class="info-box-label"><i class="fas fa-tools" style="margin-right:0.3rem;"></i> Budget d'exécution HT</div>
                                <div class="info-box-value" style="color:var(--accent-blue);">{{ formatCurrency(selectedProject.execution_budget_ht) }}</div>
                            </div>
                            <div class="info-box" v-if="selectedProject.collected_amount_ht">
                                <div class="info-box-label"><i class="fas fa-cash-register" style="margin-right:0.3rem;"></i> Montant encaissé HT</div>
                                <div class="info-box-value" style="color:var(--accent-green);">{{ formatCurrency(selectedProject.collected_amount_ht) }}</div>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div style="margin-bottom:2rem;">
                            <div class="progress" style="height:12px;">
                                <div class="progress-bar"
                                    :class="(selectedProjectLinesAllocatedTotal>0?selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal*100:0)>100?'danger':(selectedProjectLinesAllocatedTotal>0?selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal*100:0)>80?'warning':''"
                                    :style="{width:Math.min((selectedProjectLinesAllocatedTotal>0?selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal*100:0),100)+'%'}"></div>
                            </div>
                        </div>

                        <!-- TABS -->
                        <div class="tabs no-print">
                            <button class="tab-btn" :class="{active:detailTab==='lines'}" @click="detailTab='lines'"><i class="fas fa-list"></i> Lignes Budgétaires</button>
                            <button class="tab-btn" :class="{active:detailTab==='expenses'}" @click="detailTab='expenses'"><i class="fas fa-receipt"></i> Réalisations ({{ projectExpenses.length }})</button>
                            <button class="tab-btn" :class="{active:detailTab==='documents'}" @click="detailTab='documents'"><i class="fas fa-file-alt"></i> Documents projet</button>
                            <button class="tab-btn" :class="{active:detailTab==='charts'}" @click="detailTab='charts';$nextTick(()=>{renderProjectChart();renderProjectLinesChart();renderProjectExpensesByLineChart();renderProjectTimelineChart();})"><i class="fas fa-chart-pie"></i> Graphiques</button>
                            <button class="tab-btn" :class="{active:detailTab==='summary'}" @click="detailTab='summary'"><i class="fas fa-file-alt"></i> Résumé</button>
                        </div>

                        <!-- TAB: Lignes Budgétaires -->
                        <div v-if="detailTab==='lines'" class="tab-content active">
                            <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;" class="no-print">
                                <button class="btn btn-success btn-sm" @click="printSection('lines')"><i class="fas fa-print"></i> Imprimer</button>
                            </div>
                            <div v-if="selectedProjectLines.length>0">
                                <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-list"></i> Lignes Budgétaires ({{ selectedProjectLines.length }})</h4>
                                <div class="table-container">
                                    <table class="table detail-lines-table">
                                        <thead>
                                            <tr>
                                                <th>Ligne</th>
                                                <th>Alloué</th>
                                                <th>Dépensé</th>
                                                <th>Restant</th>
                                                <th>% Utilisé</th>
                                                <th>Progression</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="line in selectedProjectLines" :key="line.project_budget_line_id">
                                                <td data-label="Ligne"><strong>{{ line.name||line.line_name }}</strong></td>
                                                <td data-label="Alloué">{{ formatCurrency(line.allocated_amount) }}</td>
                                                <td data-label="Dépensé">{{ formatCurrency(getLineSpent(line)) }}</td>
                                                <td data-label="Restant">
                                                    <span class="badge" :class="getLineRemaining(line)<0?'badge-danger':'badge-success'">{{ formatCurrency(getLineRemaining(line)) }}</span>
                                                </td>
                                                <td data-label="% Utilisé">
                                                    <span class="badge" :class="getLinePercentage(line)>80?'badge-danger':getLinePercentage(line)>50?'badge-warning':'badge-info'">{{ getLinePercentage(line).toFixed(1) }}%</span>
                                                </td>
                                                <td data-label="Progression" style="min-width:120px;">
                                                    <div class="progress" style="height:6px;">
                                                        <div class="progress-bar" :class="getLinePercentage(line)>100?'danger':getLinePercentage(line)>80?'warning':''" :style="{width:Math.min(getLinePercentage(line),100)+'%'}"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="background:var(--bg-tertiary);font-weight:700;">
                                                <td data-label="">TOTAL</td>
                                                <td data-label="Alloué">{{ formatCurrency(selectedProjectLinesAllocatedTotal) }}</td>
                                                <td data-label="Dépensé">{{ formatCurrency(selectedProjectLinesSpentTotal) }}</td>
                                                <td data-label="Restant">{{ formatCurrency(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal) }}</td>
                                                <td data-label="% Utilisé"><span class="badge badge-info">{{ selectedProjectLinesAllocatedTotal>0?((selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}%</span></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="empty-state"><i class="fas fa-inbox"></i>
                                <p>Aucune ligne budgétaire définie</p>
                            </div>
                        </div>

                        <!-- TAB: Réalisations (dépenses + leurs documents) -->
                        <div v-if="detailTab==='expenses'" class="tab-content active">
                            <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;" class="no-print">
                                <button class="btn btn-success btn-sm" @click="printSection('expenses')"><i class="fas fa-print"></i> Imprimer Réalisations</button>
                            </div>
                            <div v-if="projectExpenses.length>0">
                                <div class="summary-card">
                                    <div class="summary-grid">
                                        <div class="summary-item">
                                            <div class="label">Total réalisations</div>
                                            <div class="value" style="color:var(--accent-yellow);">{{ formatCurrency(expensesTotal) }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Nombre de dépenses</div>
                                            <div class="value" style="color:var(--accent-blue);">{{ projectExpenses.length }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Dépense moyenne</div>
                                            <div class="value" style="color:var(--accent-cyan);">{{ formatCurrency(projectExpenses.length>0?expensesTotal/projectExpenses.length:0) }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Dépense max</div>
                                            <div class="value" style="color:var(--accent-red);">{{ formatCurrency(Math.max(...projectExpenses.map(e=>parseFloat(e.amount||0)),0)) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-container">
                                    <table class="table expense-detail-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Ligne Budgétaire</th>
                                                <th>Description</th>
                                                <th>Montant</th>
                                                <th>Date</th>
                                                <th class="no-print">Documents</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(exp,i) in projectExpenses" :key="exp.id">
                                                <td data-label="#">{{ i+1 }}</td>
                                                <td data-label="Ligne Budgétaire"><strong>{{ exp.budget_line_name||'N/A' }}</strong></td>
                                                <td data-label="Description">{{ exp.description||'-' }}</td>
                                                <td data-label="Montant">{{ formatCurrency(exp.amount) }}</td>
                                                <td data-label="Date">{{ formatDate(exp.expense_date) }}</td>
                                                <td class="no-print" data-label="Documents">
                                                    <div v-if="getExpenseDocs(exp).length>0">
                                                        <button class="expense-docs-btn" @click="openExpenseDocsViewer(exp)">
                                                            <i class="fas fa-paperclip"></i> {{ getExpenseDocs(exp).length }} doc(s)
                                                        </button>
                                                    </div>
                                                    <span v-else style="color:var(--text-secondary);font-size:0.8rem;">—</span>
                                                </td>
                                            </tr>
                                            <tr style="background:var(--bg-tertiary);font-weight:700;">
                                                <td data-label="" colspan="3">TOTAL</td>
                                                <td data-label="Montant">{{ formatCurrency(expensesTotal) }}</td>
                                                <td></td>
                                                <td class="no-print"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="empty-state"><i class="fas fa-inbox"></i>
                                <p>Aucune dépense enregistrée pour ce projet</p>
                            </div>
                        </div>

                        <!-- TAB: Documents projet -->
                        <div v-if="detailTab==='documents'" class="tab-content active">
                            <!-- Documents du projet -->
                            <div v-if="selectedProject.documents && selectedProject.documents.length>0" style="margin-bottom:2rem;">
                                <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-paperclip"></i> Documents du projet ({{ selectedProject.documents.length }})</h4>
                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem;">
                                    <div v-for="(doc,idx) in selectedProject.documents" :key="'proj-'+idx"
                                        style="position:relative;border:1px solid var(--border-color);border-radius:var(--radius);padding:1rem;text-align:center;background:var(--bg-tertiary);cursor:pointer;transition:all 0.3s ease;"
                                        @mouseover="$event.currentTarget.style.borderColor='var(--accent-blue)'"
                                        @mouseout="$event.currentTarget.style.borderColor='var(--border-color)'"
                                        @click="viewDocument(doc)">
                                        <i :class="doc.type==='pdf'?'fas fa-file-pdf':'fas fa-image'" style="font-size:3rem;margin-bottom:0.5rem;" :style="{color:doc.type==='pdf'?'var(--accent-red)':'var(--accent-blue)'}"></i>
                                        <div style="font-size:0.875rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ doc.name }}</div>
                                        <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.25rem;">{{ doc.size }}</div>
                                        <button v-if="user_role==='admin'" @click.stop="deleteDocument(idx)" style="position:absolute;top:8px;right:8px;width:24px;height:24px;border-radius:50%;background:var(--accent-red);color:white;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-trash" style="font-size:0.75rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents des dépenses -->
                            <div>
                                <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-receipt"></i> Documents des dépenses</h4>
                                <div v-if="projectExpenses.some(e=>getExpenseDocs(e).length>0)">
                                    <div v-for="exp in projectExpenses.filter(e=>getExpenseDocs(e).length>0)" :key="'docs-'+exp.id" style="margin-bottom:1.5rem;padding:1rem;background:var(--bg-tertiary);border-radius:var(--radius);border:1px solid var(--border-color);">
                                        <div style="margin-bottom:0.75rem;font-size:0.875rem;">
                                            <strong>{{ exp.budget_line_name||'N/A' }}</strong>
                                            <span style="color:var(--text-secondary);margin:0 0.5rem;">—</span>
                                            <span>{{ exp.description||'-' }}</span>
                                            <span class="badge badge-warning" style="margin-left:0.5rem;">{{ formatCurrency(exp.amount) }}</span>
                                            <span style="color:var(--text-secondary);margin-left:0.5rem;font-size:0.8rem;">{{ formatDate(exp.expense_date) }}</span>
                                        </div>
                                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:0.75rem;">
                                            <div v-for="(doc,di) in getExpenseDocs(exp)" :key="'edoc-'+di"
                                                style="border:1px solid var(--border-color);border-radius:8px;padding:0.75rem;text-align:center;background:var(--bg-secondary);cursor:pointer;transition:all 0.2s;"
                                                @mouseover="$event.currentTarget.style.borderColor='var(--accent-blue)'"
                                                @mouseout="$event.currentTarget.style.borderColor='var(--border-color)'"
                                                @click="openDocAt(exp,di)">
                                                <i :class="isImage(doc)?'fas fa-file-image':'fas fa-file-pdf'" style="font-size:2rem;margin-bottom:0.3rem;" :style="{color:isImage(doc)?'var(--accent-blue)':'var(--accent-red)'}"></i>
                                                <div style="font-size:0.7rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:0.25rem;">{{ doc }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="empty-state" style="padding:1.5rem;"><i class="fas fa-file-slash"></i>
                                    <p>Aucun document attaché aux dépenses</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Graphiques -->
                        <div v-if="detailTab==='charts'" class="tab-content active">
                            <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;" class="no-print">
                                <button class="btn btn-success btn-sm" @click="printSection('charts')"><i class="fas fa-print"></i> Imprimer Graphiques</button>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem;">
                                <div>
                                    <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-chart-pie" style="margin-right:0.5rem;"></i> Budget : Dépense vs Restant</h4>
                                    <div class="chart-container"><canvas ref="projectChart"></canvas></div>
                                </div>
                                <div v-if="selectedProjectLines.length>0">
                                    <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-chart-bar" style="margin-right:0.5rem;"></i> Répartition par Ligne</h4>
                                    <div class="chart-container"><canvas ref="projectLinesChart"></canvas></div>
                                </div>
                                <div v-if="selectedProjectLines.length>0">
                                    <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-balance-scale" style="margin-right:0.5rem;"></i> Alloué vs Dépensé par Ligne</h4>
                                    <div class="chart-container"><canvas ref="projectExpensesByLineChart"></canvas></div>
                                </div>
                                <div v-if="projectExpenses.length>0">
                                    <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-calendar-alt" style="margin-right:0.5rem;"></i> Évolution des Dépenses</h4>
                                    <div class="chart-container"><canvas ref="projectTimelineChart"></canvas></div>
                                </div>
                            </div>
                            <div style="margin-top:2rem;">
                                <h4 style="margin-bottom:1rem;color:var(--text-secondary);"><i class="fas fa-chart-line" style="margin-right:0.5rem;"></i> Statistiques détaillées</h4>
                                <div class="summary-card">
                                    <div class="summary-grid">
                                        <div class="summary-item">
                                            <div class="label">Budget Global</div>
                                            <div class="value" style="color:var(--accent-blue);">{{ formatCurrency(selectedProjectLinesAllocatedTotal) }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Total dépensé</div>
                                            <div class="value" style="color:var(--accent-yellow);">{{ formatCurrency(selectedProjectLinesSpentTotal) }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Restant</div>
                                            <div class="value" :style="{color:(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal)<0?'var(--accent-red)':'var(--accent-green)'}">{{ formatCurrency(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal) }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Nb lignes</div>
                                            <div class="value" style="color:var(--accent-cyan);">{{ selectedProjectLines.length }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Nb dépenses</div>
                                            <div class="value" style="color:var(--accent-purple);">{{ projectExpenses.length }}</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="label">Dépense moy.</div>
                                            <div class="value" style="color:var(--accent-cyan);">{{ formatCurrency(projectExpenses.length>0?expensesTotal/projectExpenses.length:0) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Résumé -->
                        <div v-if="detailTab==='summary'" class="tab-content active">
                            <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;" class="no-print">
                                <button class="btn btn-success btn-sm" @click="printSection('summary')"><i class="fas fa-print"></i> Imprimer Résumé Complet</button>
                            </div>
                            <div class="summary-card">
                                <h4 style="margin-bottom:1rem;color:var(--accent-blue);"><i class="fas fa-coins" style="margin-right:0.5rem;"></i> Résumé Financier</h4>
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <div class="label">Budget Global (100%)</div>
                                        <div class="value" style="color:var(--accent-blue);">{{ formatCurrency(selectedProjectLinesAllocatedTotal) }}</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Total dépensé ({{ selectedProjectLinesAllocatedTotal>0?((selectedProjectLinesSpentTotal/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}%)</div>
                                        <div class="value" style="color:var(--accent-yellow);">{{ formatCurrency(selectedProjectLinesSpentTotal) }}</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Restant ({{ selectedProjectLinesAllocatedTotal>0?(((selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal)/selectedProjectLinesAllocatedTotal)*100).toFixed(1):0 }}%)</div>
                                        <div class="value" :style="{color:(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal)<0?'var(--accent-red)':'var(--accent-green)'}">{{ formatCurrency(selectedProjectLinesAllocatedTotal-selectedProjectLinesSpentTotal) }}</div>
                                    </div>
                                    <div class="summary-item" v-if="selectedProject.contract_number">
                                        <div class="label"><i class="fas fa-file-contract"></i> N° Bon de commande</div>
                                        <div class="value" style="color:var(--accent-cyan);font-size:1rem;">{{ selectedProject.contract_number }}</div>
                                    </div>
                                    <div class="summary-item" v-if="selectedProject.contract_amount_ht">
                                        <div class="label"><i class="fas fa-handshake"></i> Montant marché HT</div>
                                        <div class="value" style="color:var(--accent-purple);">{{ formatCurrency(selectedProject.contract_amount_ht) }}</div>
                                    </div>
                                    <div class="summary-item" v-if="selectedProject.execution_budget_ht">
                                        <div class="label"><i class="fas fa-tools"></i> Budget exécution HT</div>
                                        <div class="value" style="color:var(--accent-blue);">{{ formatCurrency(selectedProject.execution_budget_ht) }}</div>
                                    </div>
                                    <div class="summary-item" v-if="selectedProject.collected_amount_ht">
                                        <div class="label"><i class="fas fa-cash-register"></i> Montant encaissé HT</div>
                                        <div class="value" style="color:var(--accent-green);">{{ formatCurrency(selectedProject.collected_amount_ht) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="selectedProjectLines.length>0" class="summary-card">
                                <h4 style="margin-bottom:1rem;color:var(--accent-blue);"><i class="fas fa-list" style="margin-right:0.5rem;"></i> Résumé par Ligne Budgétaire</h4>
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Ligne</th>
                                                <th>Alloué</th>
                                                <th>Dépensé</th>
                                                <th>Restant</th>
                                                <th>% Utilisé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="line in selectedProjectLines" :key="'sum-'+line.project_budget_line_id">
                                                <td data-label="Ligne"><strong>{{ line.name||line.line_name }}</strong></td>
                                                <td data-label="Alloué">{{ formatCurrency(line.allocated_amount) }}</td>
                                                <td data-label="Dépensé">{{ formatCurrency(getLineSpent(line)) }}</td>
                                                <td data-label="Restant"><span :style="{color:getLineRemaining(line)<0?'var(--accent-red)':'var(--accent-green)'}">{{ formatCurrency(getLineRemaining(line)) }}</span></td>
                                                <td data-label="% Utilisé"><span class="badge" :class="getLinePercentage(line)>80?'badge-danger':'badge-info'">{{ getLinePercentage(line).toFixed(1) }}%</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-if="projectExpenses.length>0" class="summary-card">
                                <h4 style="margin-bottom:1rem;color:var(--accent-blue);"><i class="fas fa-receipt" style="margin-right:0.5rem;"></i> Dernières Dépenses</h4>
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Ligne</th>
                                                <th>Description</th>
                                                <th>Montant</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="exp in projectExpenses.slice(0,10)" :key="'sum-exp-'+exp.id">
                                                <td>{{ exp.budget_line_name }}</td>
                                                <td>{{ exp.description }}</td>
                                                <td>{{ formatCurrency(exp.amount) }}</td>
                                                <td>{{ formatDate(exp.expense_date) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div><!-- /selectedProject -->
                </div>
                <div class="modal-footer no-print">
                    <button class="btn btn-secondary" @click="closeDetailModal">Fermer</button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL: Visionneuse Documents de Dépense ===== -->
        <div class="modal-overlay" :class="{active:modals.expenseDocs}" @click.self="closeExpenseDocsViewer">
            <div class="modal" style="max-width:900px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-file-alt"></i> Documents de la dépense
                        <span v-if="viewingExpenseDocs.length>0" style="font-size:0.85rem;font-weight:400;color:var(--text-secondary);margin-left:0.5rem;">({{ viewingExpenseDocIndex+1 }} / {{ viewingExpenseDocs.length }})</span>
                    </h3>
                    <button class="modal-close" @click="closeExpenseDocsViewer">&times;</button>
                </div>
                <div class="modal-body">
                    <div v-if="viewingExpenseDocs.length>0">
                        <!-- Navigation -->
                        <div v-if="viewingExpenseDocs.length>1" class="doc-nav">
                            <button class="btn btn-secondary btn-sm" @click="viewingExpenseDocIndex=Math.max(0,viewingExpenseDocIndex-1)" :disabled="viewingExpenseDocIndex===0">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </button>
                            <div class="doc-nav-pages">
                                <button v-for="(d,idx) in viewingExpenseDocs" :key="idx" class="btn btn-sm" :class="viewingExpenseDocIndex===idx?'btn-primary':'btn-secondary'" @click="viewingExpenseDocIndex=idx" style="min-width:36px;">
                                    <i :class="isImage(d)?'fas fa-file-image':'fas fa-file-pdf'"></i> {{ idx+1 }}
                                </button>
                            </div>
                            <button class="btn btn-secondary btn-sm" @click="viewingExpenseDocIndex=Math.min(viewingExpenseDocs.length-1,viewingExpenseDocIndex+1)" :disabled="viewingExpenseDocIndex===viewingExpenseDocs.length-1">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <!-- Nom du fichier -->
                        <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:0.75rem;word-break:break-all;">
                            <i :class="isImage(viewingExpenseDocs[viewingExpenseDocIndex])?'fas fa-file-image':'fas fa-file-pdf'" :style="{color:isImage(viewingExpenseDocs[viewingExpenseDocIndex])?'var(--accent-blue)':'var(--accent-red)'}"></i>
                            &nbsp;{{ viewingExpenseDocs[viewingExpenseDocIndex] }}
                        </p>
                        <!-- Affichage -->
                        <div style="background:var(--bg-tertiary);border-radius:var(--radius);overflow:hidden;">
                            <img v-if="isImage(viewingExpenseDocs[viewingExpenseDocIndex])"
                                :src="buildDocUrl(viewingExpenseDocs[viewingExpenseDocIndex])"
                                style="width:100%;height:auto;max-height:65vh;object-fit:contain;display:block;" alt="Document">
                            <div v-else style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem;gap:1.5rem;text-align:center;">
                                <i class="fas fa-file-pdf" style="font-size:4rem;color:var(--accent-red);"></i>
                                <p style="color:var(--text-secondary);">{{ viewingExpenseDocs[viewingExpenseDocIndex] }}</p>
                                <a :href="buildDocUrl(viewingExpenseDocs[viewingExpenseDocIndex])" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Ouvrir le PDF
                                </a>
                                <a :href="buildDocUrl(viewingExpenseDocs[viewingExpenseDocIndex])" :download="viewingExpenseDocs[viewingExpenseDocIndex]" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state"><i class="fas fa-file-slash"></i>
                        <p>Aucun document disponible</p>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content:space-between;">
                    <a v-if="viewingExpenseDocs.length>0 && isImage(viewingExpenseDocs[viewingExpenseDocIndex])"
                        :href="buildDocUrl(viewingExpenseDocs[viewingExpenseDocIndex])" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fas fa-external-link-alt"></i> Ouvrir dans un nouvel onglet
                    </a>
                    <button @click="closeExpenseDocsViewer" class="btn btn-primary">Fermer</button>
                </div>
            </div>
        </div>

    </div><!-- /#app -->

    <script>
        const {
            createApp
        } = Vue;
        const API_BASE_URL = 'api/index.php';

        createApp({
            data() {
                return {
                    API_BASE_URL,
                    projects: [],
                    filteredProjects: [],
                    budgetLines: [],
                    allProjectLines: {},
                    availableLines: [],
                    selectedProject: null,
                    selectedProjectLines: [],
                    projectExpenses: [],
                    expensesTotal: 0,
                    editingProject: null,
                    newProject: {
                        name: '',
                        status: 'active'
                    },
                    editingLine: null,
                    newLine: {
                        name: '',
                        project_id: null,
                        allocated_amount: 0
                    },
                    newLineName: '',
                    editingExpense: null,
                    newExpense: {
                        project_id: null,
                        budget_line_id: null,
                        amount: 0,
                        description: '',
                        expense_date: ''
                    },
                    searchQuery: '',
                    statusFilter: 'all',
                    categoryFilter: '',
                    budgetPieChart: null,
                    progressBarChart: null,
                    budgetAlerts: [],
                    showBudgetLines: false,
                    showImportMenu: false,
                    showExportMenu: false,
                    projectExportMenuOpen: null,
                    isRenderingCharts: false,
                    projectLines: [],
                    currentProjectLines: [],
                    projectLinesTotal: 0,
                    isEditMode: false,
                    detailTab: 'lines',
                    modals: {
                        line: false,
                        project: false,
                        detail: false,
                        expenseDocs: false
                    },
                    stats: {
                        totalProjects: 0,
                        totalBudget: 0,
                        totalSpent: 0,
                        totalRemaining: 0,
                        spentPercentage: 0
                    },
                    budgetStats: {
                        totalAllocated: 0,
                        totalSpent: 0,
                        totalRemaining: 0,
                        overbudgetCount: 0
                    },
                    projectDetailChart: null,
                    projectLinesChart: null,
                    projectExpensesByLineChart: null,
                    projectTimelineChart: null,
                    mobileMenuOpen: false,
                    user_name: '<?php echo $_SESSION["user_name"] ?>',
                    user_role: '<?php echo $_SESSION["user_role"] ?? "user"; ?>',
                    sectorFilter: '',
                    budgetFilter: '',
                    sortBy: 'name',
                    sortOrder: 'asc',
                    sortAsc: true,
                    dateFrom: '',
                    dateTo: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    // Documents dépenses
                    viewingExpenseDocs: [],
                    viewingExpenseDocIndex: 0,
                };
            },
            computed: {
                paginatedProjects() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredProjects.slice(start, start + this.itemsPerPage);
                },
                totalPages() {
                    return Math.ceil(this.filteredProjects.length / this.itemsPerPage);
                },
                selectedProjectLinesAllocatedTotal() {
                    return this.selectedProjectLines.reduce((sum, l) => sum + parseFloat(l.allocated_amount || 0), 0);
                },
                selectedProjectLinesSpentTotal() {
                    return this.selectedProjectLines.reduce((sum, l) => sum + this.getLineSpent(l), 0);
                }
            },
            mounted() {
                this.loadData();
                document.addEventListener('click', (e) => {
                    if (this.projectExportMenuOpen !== null && !e.target.closest('.action-buttons')) {
                        this.projectExportMenuOpen = null;
                    }
                });
            },
            beforeDestroy() {
                [this.budgetPieChart, this.progressBarChart].forEach(c => {
                    try {
                        c && c.destroy();
                    } catch (e) {}
                });
            },
            methods: {

                // ==================== CHARGEMENT ====================
                async loadData() {
                    await this.fetchProjects();
                    await this.fetchLines();
                    await this.fetchAllProjectLines();
                },
                async fetchProjects() {
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=getProjects`)).json();
                        this.projects = data.data || [];
                        this.filteredProjects = this.projects;
                        this.calculateStats();
                        this.checkBudgetAlerts();
                        this.$nextTick(() => this.renderCharts());
                    } catch (e) {
                        console.error('[v0] fetchProjects:', e);
                    }
                },
                async fetchLines() {
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=getBudgetLines`)).json();
                        this.availableLines = data.data || [];
                    } catch (e) {
                        console.error('[v0] fetchLines:', e);
                    }
                },
                async fetchProjectLines(projectId) {
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=getProjectBudgetLines&project_id=${projectId}`)).json();
                        return data.data || [];
                    } catch (e) {
                        return [];
                    }
                },
                async fetchAllProjectLines() {
                    for (const project of this.projects) {
                        this.allProjectLines[project.id] = await this.fetchProjectLines(project.id);
                    }
                    this.calculateStats();
                    this.checkBudgetAlerts();
                },
                async fetchProjectExpenses(projectId) {
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=getExpenses`)).json();
                        if (data.success && data.data) {
                            this.projectExpenses = data.data.filter(exp => exp.project_id == projectId);
                            this.expensesTotal = this.projectExpenses.reduce((sum, exp) => sum + parseFloat(exp.amount || 0), 0);
                        }
                    } catch (e) {
                        console.error('[v0] fetchProjectExpenses:', e);
                    }
                },

                // ==================== DOCUMENTS DEPENSES ====================
                getExpenseDocs(exp) {
                    let docs = [];
                    if (exp.documents) {
                        try {
                            const parsed = typeof exp.documents === 'string' ? JSON.parse(exp.documents) : exp.documents;
                            if (Array.isArray(parsed)) docs = parsed.filter(Boolean);
                        } catch (e) {
                            if (typeof exp.documents === 'string' && exp.documents.trim()) docs = [exp.documents.trim()];
                        }
                    }
                    if (exp.document && exp.document.trim && exp.document.trim() && !docs.includes(exp.document.trim())) {
                        docs.unshift(exp.document.trim());
                    }
                    return docs;
                },
                isImage(filename) {
                    if (!filename) return false;
                    return /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(filename);
                },
                buildDocUrl(filename) {
                    if (!filename) return '';
                    const clean = filename.replace(/^\/+/, '');
                    const path = clean.startsWith('images/') ? clean : 'images/' + clean;
                    const base = window.location.pathname.split('/').slice(0, -1).join('/');
                    return window.location.origin + base + '/' + path;
                },
                openExpenseDocsViewer(exp, startIndex = 0) {
                    this.viewingExpenseDocs = this.getExpenseDocs(exp);
                    this.viewingExpenseDocIndex = startIndex;
                    this.modals.expenseDocs = true;
                },
                openDocAt(exp, index) {
                    this.openExpenseDocsViewer(exp, index);
                },
                closeExpenseDocsViewer() {
                    this.modals.expenseDocs = false;
                    this.viewingExpenseDocs = [];
                    this.viewingExpenseDocIndex = 0;
                },

                // ==================== CALCULS BUDGET ====================
                getProjectAllocatedFromLines(project) {
                    return (this.allProjectLines[project.id] || []).reduce((sum, l) => sum + parseFloat(l.allocated_amount || 0), 0);
                },
                getSpentPercentage(project) {
                    const allocated = this.getProjectAllocatedFromLines(project);
                    return allocated === 0 ? 0 : (parseFloat(project.spent || 0) / allocated) * 100;
                },
                getProjectRemaining(project) {
                    return this.getProjectAllocatedFromLines(project) - parseFloat(project.spent || 0);
                },
                getLineSpent(line) {
                    const id = line.project_budget_line_id || line.id;
                    return this.projectExpenses.filter(exp => exp.project_budget_line_id == id).reduce((sum, exp) => sum + parseFloat(exp.amount || 0), 0);
                },
                getLineRemaining(line) {
                    return parseFloat(line.allocated_amount || 0) - this.getLineSpent(line);
                },
                getLinePercentage(line) {
                    const a = parseFloat(line.allocated_amount || 0);
                    return a === 0 ? 0 : (this.getLineSpent(line) / a) * 100;
                },

                // ==================== STATS ====================
                calculateStats() {
                    this.stats.totalProjects = this.projects.length;
                    this.stats.totalBudget = this.projects.reduce((sum, p) => sum + this.getProjectAllocatedFromLines(p), 0);
                    this.stats.totalSpent = this.projects.reduce((sum, p) => sum + parseFloat(p.spent || 0), 0);
                    this.stats.totalRemaining = this.stats.totalBudget - this.stats.totalSpent;
                    this.stats.spentPercentage = this.stats.totalBudget > 0 ? (this.stats.totalSpent / this.stats.totalBudget) * 100 : 0;
                },
                checkBudgetAlerts() {
                    this.budgetAlerts = [];
                    this.projects.forEach(project => {
                        const pct = this.getSpentPercentage(project);
                        const rem = this.getProjectRemaining(project);
                        const allocated = this.getProjectAllocatedFromLines(project);
                        if (rem < 0) this.budgetAlerts.push({
                            id: project.id,
                            name: project.name,
                            type: 'exceeded',
                            remaining: rem,
                            allocated
                        });
                        else if (pct > 80 && pct <= 100) this.budgetAlerts.push({
                            id: project.id,
                            name: project.name,
                            type: 'warning',
                            remaining: rem,
                            allocated
                        });
                    });
                },

                // ==================== FILTRES / TRI ====================
                filterProjects() {
                    let filtered = this.projects;
                    if (this.searchQuery) filtered = filtered.filter(p => p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || (p.location && p.location.toLowerCase().includes(this.searchQuery.toLowerCase())));
                    if (this.sectorFilter) filtered = filtered.filter(p => p.department === this.sectorFilter);
                    if (this.budgetFilter === 'remaining') filtered = filtered.filter(p => this.getProjectRemaining(p) > 0);
                    else if (this.budgetFilter === 'over') filtered = filtered.filter(p => this.getProjectRemaining(p) < 0);
                    else if (this.budgetFilter === 'warning') filtered = filtered.filter(p => {
                        const pct = this.getSpentPercentage(p);
                        return pct > 80 && pct <= 100;
                    });
                    if (this.dateFrom) filtered = filtered.filter(p => {
                        const d = p.date_of_creation || p.created_at;
                        return !d || new Date(d) >= new Date(this.dateFrom);
                    });
                    if (this.dateTo) filtered = filtered.filter(p => {
                        const d = p.date_of_creation || p.created_at;
                        return !d || new Date(d) <= new Date(this.dateTo);
                    });
                    this.filteredProjects = filtered;
                    this.sortProjects();
                    this.currentPage = 1;
                },
                setSortBy(field) {
                    this.sortAsc = this.sortBy === field ? !this.sortAsc : true;
                    this.sortBy = field;
                    this.sortProjects();
                },
                sortProjects() {
                    this.filteredProjects.sort((a, b) => {
                        let av, bv;
                        if (this.sortBy === 'name' || this.sortBy === 'department') {
                            av = (a[this.sortBy] || '').toLowerCase();
                            bv = (b[this.sortBy] || '').toLowerCase();
                        } else if (this.sortBy === 'date_of_creation') {
                            av = new Date(a.date_of_creation || a.created_at || 0).getTime();
                            bv = new Date(b.date_of_creation || b.created_at || 0).getTime();
                        } else if (this.sortBy === 'allocated_amount') {
                            av = this.getProjectAllocatedFromLines(a);
                            bv = this.getProjectAllocatedFromLines(b);
                        } else if (this.sortBy === 'remaining') {
                            av = this.getProjectRemaining(a);
                            bv = this.getProjectRemaining(b);
                        } else {
                            av = parseFloat(a[this.sortBy] || 0);
                            bv = parseFloat(b[this.sortBy] || 0);
                        }
                        return this.sortAsc ? (av > bv ? 1 : -1) : (av < bv ? 1 : -1);
                    });
                },

                // ==================== CRUD LIGNES ====================
                openLineModal() {
                    this.newLineName = '';
                    this.modals.line = true;
                },
                closeLineModal() {
                    this.modals.line = false;
                },
                async createLine() {
                    if (!this.newLineName) {
                        alert('Veuillez entrer un nom');
                        return;
                    }
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=createSimpleBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.newLineName
                            })
                        })).json();
                        alert(data.message);
                        this.closeLineModal();
                        this.fetchLines();
                    } catch (e) {
                        console.error(e);
                    }
                },
                async updateLine(line) {
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=updateBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: line.id,
                                name: line.name
                            })
                        })).json();
                        alert(data.message);
                        this.editingLine = null;
                        this.fetchLines();
                    } catch (e) {
                        console.error(e);
                    }
                },

                // ==================== CRUD PROJETS ====================
                openProjectModal() {
                    this.isEditMode = false;
                    this.newProject = {
                        name: '',
                        description: '',
                        department: '',
                        location: '',
                        date_of_creation: new Date().toISOString().split('T')[0],
                        documents: [],
                        contract_number: '',
                        contract_amount_ht: null,
                        execution_budget_ht: null,
                        collected_amount_ht: null
                    };
                    this.projectLines = [];
                    this.currentProjectLines = [];
                    this.projectLinesTotal = 0;
                    this.modals.project = true;
                },
                async editProject(project) {
                    this.isEditMode = true;
                    this.newProject = {
                        ...project,
                        documents: project.documents || []
                    };
                    this.projectLines = [];
                    this.projectLinesTotal = 0;
                    this.currentProjectLines = await this.fetchProjectLines(project.id);
                    this.updateProjectLinesTotal();
                    this.modals.project = true;
                },
                closeProjectModal() {
                    this.modals.project = false;
                },
                addProjectLine() {
                    this.projectLines.push({
                        budget_line_id: '',
                        allocated_amount: 0,
                        name: ''
                    });
                },
                removeProjectLine(index) {
                    this.projectLines.splice(index, 1);
                    this.updateProjectLinesTotal();
                },
                updateLineName(line) {
                    const found = this.availableLines.find(l => l.id == line.budget_line_id);
                    if (found) line.name = found.name;
                    this.updateProjectLinesTotal();
                },
                availableLinesForNewLine(currentId) {
                    const usedByExisting = this.currentProjectLines.map(l => parseInt(l.budget_line_id));
                    const usedByNew = this.projectLines.filter(l => l.budget_line_id && l.budget_line_id != currentId).map(l => parseInt(l.budget_line_id));
                    return this.availableLines.filter(l => ![...usedByExisting, ...usedByNew].includes(parseInt(l.id)));
                },
                updateProjectLinesTotal() {
                    this.projectLinesTotal = this.projectLines.reduce((s, l) => s + parseFloat(l.allocated_amount || 0), 0) + this.currentProjectLines.reduce((s, l) => s + parseFloat(l.allocated_amount || 0), 0);
                },
                async deleteExistingLine(line) {
                    if (!confirm('Supprimer cette ligne budgétaire du projet ?')) return;
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=deleteProjectBudgetLine`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: line.project_budget_line_id
                            })
                        })).json();
                        if (data.success) {
                            this.currentProjectLines.splice(this.currentProjectLines.indexOf(line), 1);
                            this.updateProjectLinesTotal();
                            alert(data.message);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                async saveProject() {
                    if (!this.newProject.name) {
                        alert('Veuillez entrer un nom de projet');
                        return;
                    }

                    const action = this.isEditMode ? 'updateProject' : 'createProject';
                    const url = `${API_BASE_URL}?action=${action}`;

                    const formData = new FormData();

                    if (this.isEditMode) formData.append('id', this.newProject.id);
                    formData.append('name', this.newProject.name || '');
                    formData.append('description', this.newProject.description || '');
                    formData.append('department', this.newProject.department || '');
                    formData.append('location', this.newProject.location || '');
                    formData.append('date_of_creation', this.newProject.date_of_creation || '');
                    formData.append('contract_number', this.newProject.contract_number || '');
                    formData.append('contract_amount_ht', this.newProject.contract_amount_ht ?? '');
                    formData.append('execution_budget_ht', this.newProject.execution_budget_ht ?? '');
                    formData.append('collected_amount_ht', this.newProject.collected_amount_ht ?? '');

                    const filteredLines = this.projectLines.filter(
                        l => l.budget_line_id && l.allocated_amount > 0
                    );

                    formData.append('lines', JSON.stringify(filteredLines));

                    if (this.isEditMode) {
                        formData.append('updated_lines', JSON.stringify(this.currentProjectLines || []));
                        formData.append('deleted_lines', JSON.stringify(this.deletedProjectLines || []));
                    }

                    const existingDocs = (this.newProject.documents || [])
                        .filter(d => typeof d === 'string');

                    formData.append('existing_documents', JSON.stringify(existingDocs));

                    (this.newProject.documents || [])
                    .filter(d => d instanceof File)
                        .forEach(f => formData.append('documents[]', f));

                    /* ================= DEBUG ================= */

                    console.log('=== ROUTE ===');
                    console.log(url);

                    console.log('=== ACTION ===');
                    console.log(action);

                    console.log('=== PAYLOAD (FormData) ===');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, value);
                    }

                    /* ========================================= */

                    try {
                        const resp = await fetch(url, {
                            method: 'POST',
                            body: formData
                        });

                        console.log('=== RESPONSE STATUS ===');
                        console.log(resp.status);

                        const rawText = await resp.text();
                        console.log('=== RAW RESPONSE ===');
                        console.log(rawText);

                        let data;
                        try {
                            data = JSON.parse(rawText);
                        } catch (e) {
                            console.error('Erreur JSON.parse:', e);
                            throw new Error('Réponse non JSON');
                        }

                        console.log('=== PARSED RESPONSE ===');
                        console.log(data);

                        if (!resp.ok) throw new Error(data.message || 'Erreur serveur');

                        alert(data.message);

                        this.closeProjectModal();
                        await this.fetchProjects();
                        await this.fetchAllProjectLines();

                    } catch (e) {
                        console.error('=== ERROR ===');
                        console.error(e);
                        alert('Erreur lors de la sauvegarde');
                    }
                },
                async deleteProject(id) {
                    if (!confirm('Confirmer la suppression ?')) return;
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=deleteProject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        })).json();
                        alert(data.message);
                        await this.fetchProjects();
                        await this.fetchAllProjectLines();
                    } catch (e) {
                        console.error(e);
                    }
                },
                async lockProject(id) {
                    if (!confirm('Clôturer ce projet ?')) return;
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=lockProject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        })).json();
                        alert(data.message);
                        await this.fetchProjects();
                        await this.fetchAllProjectLines();
                    } catch (e) {
                        console.error(e);
                    }
                },
                async unlockProject(id) {
                    if (!confirm('Ouvrir de nouveau ce projet ?')) return;
                    try {
                        const data = await (await fetch(`${API_BASE_URL}?action=unlockProject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        })).json();
                        alert(data.message);
                        await this.fetchProjects();
                        await this.fetchAllProjectLines();
                    } catch (e) {
                        console.error(e);
                    }
                },

                // ==================== DETAIL PROJET ====================
                async viewProject(project) {
                    this.selectedProject = project;
                    this.selectedProjectLines = await this.fetchProjectLines(project.id);
                    await this.fetchProjectExpenses(project.id);
                    this.detailTab = 'lines';
                    this.modals.detail = true;
                    this.$nextTick(() => {
                        this.renderProjectChart();
                        this.renderProjectLinesChart();
                        this.renderProjectExpensesByLineChart();
                        this.renderProjectTimelineChart();
                    });
                },
                closeDetailModal() {
                    this.modals.detail = false;
                    [this.projectDetailChart, this.projectLinesChart, this.projectExpensesByLineChart, this.projectTimelineChart].forEach(c => {
                        try {
                            c && c.destroy();
                        } catch (e) {}
                    });
                },
                toggleBudgetLines() {
                    this.showBudgetLines = !this.showBudgetLines;
                },

                // ==================== DOCUMENTS PROJET ====================
                handleFileUpload(event) {
                    const files = Array.from(event.target.files);
                    if (!this.newProject.documents) this.newProject.documents = [];
                    const currentCount = this.newProject.documents.length;
                    if (currentCount + files.length > 15) {
                        alert('Maximum 15 documents autorisés');
                        event.target.value = '';
                        return;
                    }
                    const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                    files.forEach(file => {
                        if (!allowed.includes(file.type)) {
                            alert(`Type non autorisé : ${file.name}`);
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            alert(`Fichier trop volumineux : ${file.name}`);
                            return;
                        }
                        if (!this.newProject.documents.some(d => d instanceof File && d.name === file.name && d.size === file.size)) {
                            this.newProject.documents.push(file);
                        }
                    });
                    event.target.value = '';
                },
                removeDocument(index) {
                    this.newProject.documents.splice(index, 1);
                },
                viewDocument(doc) {
                    if (!doc) return;
                    window.open(this.buildDocUrl(doc.document || doc), '_blank');
                },
                deleteDocument(index) {
                    if (confirm('Supprimer ce document ?')) this.selectedProject.documents.splice(index, 1);
                },
                getDocIcon(doc) {
                    if (typeof doc === 'string') return doc.toLowerCase().endsWith('.pdf') ? 'fas fa-file-pdf' : 'fas fa-image';
                    return doc.type === 'application/pdf' ? 'fas fa-file-pdf' : 'fas fa-image';
                },
                getDocName(doc) {
                    return typeof doc === 'string' ? doc : (doc.name || 'Document');
                },

                // ==================== EXPORT ====================
                exportAll() {
                    window.open('api/export.php', '_blank');
                    alert('Exportation effectuée !');
                },
                exportToExcel() {
                    let html = '<table><thead><tr><th>Projet</th><th>Secteur</th><th>Lieu</th><th>Date création</th><th>Ligne Budgétaire</th><th>Budget Alloué</th><th>Réalisations</th><th>Écart</th></tr></thead><tbody>';
                    this.filteredProjects.forEach(project => {
                        const lines = this.allProjectLines[project.id] || [];
                        if (lines.length > 0) {
                            lines.forEach(line => {
                                html += `<tr><td>${project.name}</td><td>${project.department||'-'}</td><td>${project.location||'-'}</td><td>${project.date_of_creation||project.created_at||'-'}</td><td>${line.line_name||'-'}</td><td>${line.allocated_amount||0}</td><td>${line.spent||0}</td><td>${(parseFloat(line.allocated_amount||0)-parseFloat(line.spent||0))}</td></tr>`;
                            });
                        } else {
                            html += `<tr><td>${project.name}</td><td>${project.department||'-'}</td><td>${project.location||'-'}</td><td>${project.date_of_creation||project.created_at||'-'}</td><td>-</td><td>${project.allocated_amount||0}</td><td>${project.spent||0}</td><td>${project.remaining||0}</td></tr>`;
                        }
                    });
                    html += '</tbody></table>';
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(new Blob([html], {
                        type: 'application/vnd.ms-excel'
                    }));
                    a.download = `projets_${new Date().toISOString().split('T')[0]}.xls`;
                    a.click();
                },
                exportToCSV() {
                    let csv = 'Projet,Secteur,Lieu,Date création,Ligne Budgétaire,Budget Alloué,Réalisations,Écart\n';
                    this.filteredProjects.forEach(project => {
                        const lines = this.allProjectLines[project.id] || [];
                        if (lines.length > 0) {
                            lines.forEach(l => {
                                csv += `"${project.name}","${project.department||'-'}","${project.location||'-'}","${project.date_of_creation||project.created_at||'-'}","${l.line_name||'-'}",${l.allocated_amount||0},${l.spent||0},${(parseFloat(l.allocated_amount||0)-parseFloat(l.spent||0))}\n`;
                            });
                        } else {
                            csv += `"${project.name}","${project.department||'-'}","${project.location||'-'}","${project.date_of_creation||project.created_at||'-'}","-",${project.allocated_amount||0},${project.spent||0},${project.remaining||0}\n`;
                        }
                    });
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(new Blob([csv], {
                        type: 'text/csv;charset=utf-8;'
                    }));
                    a.download = `projets_${new Date().toISOString().split('T')[0]}.csv`;
                    a.click();
                },

                // ==================== FORMATAGE ====================
                formatCurrency(value) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'XOF',
                        minimumFractionDigits: 0
                    }).format(value || 0);
                },
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    return new Date(dateStr).toLocaleDateString('fr-FR');
                },
                getBadgeClass(remaining) {
                    return parseFloat(remaining) < 0 ? 'badge-danger' : 'badge-success';
                },
                getProjectRowClass(project) {
                    const pct = this.getSpentPercentage(project);
                    if (pct > 100) return 'budget-exceeded';
                    if (pct > 80) return 'budget-warning';
                    return '';
                },
                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                toggleMobileMenu() {
                    this.mobileMenuOpen = !this.mobileMenuOpen;
                },
                closeMobileMenu() {
                    this.mobileMenuOpen = false;
                },

                // ==================== IMPRESSION ====================
                buildPrintWindow(title, bodyContent) {
                    const pw = window.open('', '', 'width=1000,height=800');
                    const html = `<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>${title}</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; padding:30px; color:#1a1a1a; background:#fff; line-height:1.6; }
                .print-header { background:linear-gradient(135deg,#0070f3,#00d4ff); color:white; padding:25px 30px; border-radius:10px; margin-bottom:25px; }
                .print-header h1 { font-size:22px; font-weight:700; margin-bottom:4px; }
                .print-header .subtitle { font-size:13px; opacity:0.9; }
                .meta-row { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px; }
                .meta-box { background:#f0f4f8; padding:10px 16px; border-radius:8px; font-size:13px; display:flex; align-items:center; gap:8px; }
                .meta-box strong { color:#0070f3; }
                .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px; }
                .stat-box { background:#f8f9fa; padding:15px; border-radius:8px; border-left:4px solid #0070f3; }
                .stat-box.success { border-left-color:#00e676; }
                .stat-box.warning { border-left-color:#ffb800; }
                .stat-box.danger { border-left-color:#ff3b3b; }
                .stat-box .label { font-size:11px; color:#666; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; }
                .stat-box .val { font-size:18px; font-weight:700; color:#1a1a1a; }
                .section { background:white; border:1px solid #e0e0e0; border-radius:8px; padding:20px; margin-bottom:20px; }
                .section-title { font-size:16px; font-weight:600; color:#0070f3; margin-bottom:15px; padding-bottom:8px; border-bottom:2px solid #e8e8e8; }
                table { width:100%; border-collapse:collapse; }
                thead { background:#f0f4f8; }
                th { padding:10px 12px; text-align:left; font-weight:600; font-size:12px; color:#333; border-bottom:2px solid #d0d0d0; }
                td { padding:10px 12px; border-bottom:1px solid #eee; font-size:12px; }
                tbody tr:nth-child(even) { background:#fafbfc; }
                .total-row { background:#f0f4f8 !important; font-weight:700; }
                .badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:11px; font-weight:600; }
                .badge-ok { background:#d4f4dd; color:#00a152; }
                .badge-warn { background:#fff4e0; color:#f57c00; }
                .badge-err { background:#ffe0e0; color:#d32f2f; }
                .footer { margin-top:30px; padding-top:15px; border-top:2px solid #e0e0e0; text-align:center; font-size:11px; color:#888; }
                .footer strong { color:#0070f3; }
                @media print { body { padding:15px; } .section { break-inside:avoid; } table { break-inside:avoid; } }
            </style></head><body>
            ${bodyContent}
            <div class="footer">
                <p><strong>OrizonPlus</strong> &mdash; Système de Gestion de Projets</p>
                <p>Document généré le ${new Date().toLocaleString('fr-FR',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'})}</p>
                <p style="margin-top:5px;font-style:italic;color:#aaa;">Ce document est confidentiel.</p>
            </div></body></html>`;
                    pw.document.write(html);
                    pw.document.close();
                    setTimeout(() => {
                        pw.print();
                        pw.close();
                    }, 800);
                },

                printProjectsPage() {
                    let rows = '';
                    this.filteredProjects.forEach((p, i) => {
                        const allocated = this.getProjectAllocatedFromLines(p);
                        const spent = parseFloat(p.spent || 0);
                        const remaining = allocated - spent;
                        const spentPct = allocated > 0 ? ((spent / allocated) * 100).toFixed(1) : '0.0';
                        const remPct = allocated > 0 ? ((remaining / allocated) * 100).toFixed(1) : '0.0';
                        const bc = remaining < 0 ? 'badge-err' : parseFloat(spentPct) > 80 ? 'badge-warn' : 'badge-ok';
                        rows += `<tr><td>${i+1}</td><td><strong>${p.name}</strong></td><td>${p.department||'-'}</td><td>${p.location||'-'}</td><td style="text-align:right">${this.formatCurrency(allocated)}</td><td style="text-align:right">${this.formatCurrency(spent)} <small>(${spentPct}%)</small></td><td style="text-align:right">${this.formatCurrency(remaining)} <small>(${remPct}%)</small></td><td style="text-align:center"><span class="badge ${bc}">${remaining<0?'Dépassé':parseFloat(spentPct)>80?'Critique':'OK'}</span></td></tr>`;
                    });
                    const body = `
                <div class="print-header"><h1>Liste des Projets</h1><div class="subtitle">${this.filteredProjects.length} projet(s) — Budget total: ${this.formatCurrency(this.stats.totalBudget)}</div></div>
                <div class="stats-row">
                    <div class="stat-box"><div class="label">Total Projets</div><div class="val">${this.stats.totalProjects}</div></div>
                    <div class="stat-box"><div class="label">Budget Total</div><div class="val">${this.formatCurrency(this.stats.totalBudget)}</div></div>
                    <div class="stat-box warning"><div class="label">Total Dépensé</div><div class="val">${this.formatCurrency(this.stats.totalSpent)}</div></div>
                    <div class="stat-box ${this.stats.totalRemaining<0?'danger':'success'}"><div class="label">Restant</div><div class="val">${this.formatCurrency(this.stats.totalRemaining)}</div></div>
                </div>
                <div class="section">
                    <h2 class="section-title">Tableau des Projets</h2>
                    <table><thead><tr><th>#</th><th>Projet</th><th>Secteur</th><th>Lieu</th><th style="text-align:right">Budget</th><th style="text-align:right">Dépensé</th><th style="text-align:right">Restant</th><th style="text-align:center">Statut</th></tr></thead>
                    <tbody>${rows}<tr class="total-row"><td colspan="4"><strong>TOTAL</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.stats.totalBudget)}</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.stats.totalSpent)}</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.stats.totalRemaining)}</strong></td><td></td></tr></tbody>
                    </table>
                </div>`;
                    this.buildPrintWindow('Liste des Projets - OrizonPlus', body);
                },

                printBudgetLinesPage() {
                    let rows = '';
                    this.availableLines.forEach((l, i) => {
                        rows += `<tr><td>${i+1}</td><td><strong>${l.name}</strong></td><td>${l.created_at||'-'}</td></tr>`;
                    });
                    const body = `<div class="print-header"><h1>Lignes Budgétaires</h1><div class="subtitle">${this.availableLines.length} ligne(s)</div></div>
                <div class="section"><h2 class="section-title">Liste des Lignes Budgétaires</h2>
                <table><thead><tr><th>#</th><th>Nom</th><th>Date de création</th></tr></thead><tbody>${rows}</tbody></table></div>`;
                    this.buildPrintWindow('Lignes Budgétaires - OrizonPlus', body);
                },

                printChartsPage() {
                    const charts = [];
                    if (this.$refs.budgetPieChart) charts.push({
                        title: 'Répartition des Budgets',
                        img: this.$refs.budgetPieChart.toDataURL()
                    });
                    if (this.$refs.progressBarChart) charts.push({
                        title: 'Budget vs Réalisations par Projet',
                        img: this.$refs.progressBarChart.toDataURL()
                    });
                    const body = `<div class="print-header"><h1>Graphiques — Vue Globale</h1><div class="subtitle">Visualisation des données budgétaires</div></div>
                ${charts.map(c=>`<div class="section"><h2 class="section-title">${c.title}</h2><div style="text-align:center"><img src="${c.img}" style="max-width:100%;height:auto;"/></div></div>`).join('')}`;
                    this.buildPrintWindow('Graphiques - OrizonPlus', body);
                },

                async printProjectDetails(project) {
                    this.selectedProject = project;
                    this.selectedProjectLines = await this.fetchProjectLines(project.id);
                    await this.fetchProjectExpenses(project.id);

                    const allocated = this.getProjectAllocatedFromLines(project);
                    const spent = parseFloat(project.spent || 0);
                    const remaining = allocated - spent;
                    const spentPct = allocated > 0 ? ((spent / allocated) * 100).toFixed(1) : '0.0';
                    const remPct = allocated > 0 ? ((remaining / allocated) * 100).toFixed(1) : '0.0';

                    // Méta secteur / lieu
                    const metaHtml = `<div class="meta-row">
                ${project.department?`<div class="meta-box"><strong>Secteur</strong> ${project.department}</div>`:''}
                ${project.location?`<div class="meta-box"><strong>Lieu</strong> ${project.location}</div>`:''}
                ${project.date_of_creation?`<div class="meta-box"><strong>Date création</strong> ${this.formatDate(project.date_of_creation)}</div>`:''}
                ${project.contract_number?`<div class="meta-box"><strong>N° Bon de commande</strong> ${project.contract_number}</div>`:''}
            </div>
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr)">
                ${project.contract_amount_ht?`<div class="stat-box"><div class="label">Montant marché HT</div><div class="val">${this.formatCurrency(project.contract_amount_ht)}</div></div>`:''}
                ${project.execution_budget_ht?`<div class="stat-box"><div class="label">Budget exécution HT</div><div class="val">${this.formatCurrency(project.execution_budget_ht)}</div></div>`:''}
                ${project.collected_amount_ht?`<div class="stat-box success"><div class="label">Montant encaissé HT</div><div class="val">${this.formatCurrency(project.collected_amount_ht)}</div></div>`:''}
            </div>`;

                    // Lignes budgétaires
                    let linesHtml = '';
                    if (this.selectedProjectLines.length > 0) {
                        let lRows = '';
                        this.selectedProjectLines.forEach(line => {
                            const la = parseFloat(line.allocated_amount || 0);
                            const ls = this.getLineSpent(line);
                            const lr = la - ls;
                            const lp = la > 0 ? ((ls / la) * 100).toFixed(1) : '0.0';
                            const bc = lr < 0 ? 'badge-err' : parseFloat(lp) > 80 ? 'badge-warn' : 'badge-ok';
                            lRows += `<tr><td><strong>${line.name||line.line_name||'N/A'}</strong></td><td style="text-align:right">${this.formatCurrency(la)}</td><td style="text-align:right">${this.formatCurrency(ls)}</td><td style="text-align:right">${this.formatCurrency(lr)}</td><td style="text-align:center"><span class="badge ${bc}">${lp}%</span></td></tr>`;
                        });
                        linesHtml = `<div class="section"><h2 class="section-title">Lignes Budgétaires</h2>
                    <table><thead><tr><th>Ligne</th><th style="text-align:right">Alloué</th><th style="text-align:right">Dépensé</th><th style="text-align:right">Restant</th><th style="text-align:center">% Utilisé</th></tr></thead>
                    <tbody>${lRows}</tbody></table></div>`;
                    }

                    // Dépenses
                    let expHtml = '';
                    if (this.projectExpenses.length > 0) {
                        let eRows = '';
                        this.projectExpenses.forEach((exp, i) => {
                            const nbDocs = this.getExpenseDocs(exp).length;
                            eRows += `<tr><td>${i+1}</td><td><strong>${exp.budget_line_name||'N/A'}</strong></td><td>${exp.description||'-'}</td><td style="text-align:right">${this.formatCurrency(exp.amount)}</td><td style="text-align:center">${this.formatDate(exp.expense_date)}</td><td style="text-align:center">${nbDocs>0?nbDocs+' doc(s)':'-'}</td></tr>`;
                        });
                        expHtml = `<div class="section"><h2 class="section-title">Réalisations (${this.projectExpenses.length})</h2>
                    <table><thead><tr><th>#</th><th>Ligne</th><th>Description</th><th style="text-align:right">Montant</th><th style="text-align:center">Date</th><th style="text-align:center">Docs</th></tr></thead>
                    <tbody>${eRows}<tr class="total-row"><td colspan="3"><strong>TOTAL</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.expensesTotal)}</strong></td><td></td><td></td></tr></tbody></table></div>`;
                    }

                    const body = `
                <div class="print-header"><h1>Rapport du Projet</h1><div class="subtitle">${project.name}</div></div>
                ${metaHtml}
                <div class="stats-row">
                    <div class="stat-box"><div class="label">Budget Alloué (100%)</div><div class="val">${this.formatCurrency(allocated)}</div></div>
                    <div class="stat-box warning"><div class="label">Dépensé (${spentPct}%)</div><div class="val">${this.formatCurrency(spent)}</div></div>
                    <div class="stat-box ${remaining>=0?'success':'danger'}"><div class="label">Restant (${remPct}%)</div><div class="val">${this.formatCurrency(remaining)}</div></div>
                    <div class="stat-box"><div class="label">Nb Réalisations</div><div class="val">${this.projectExpenses.length}</div></div>
                </div>
                ${linesHtml}${expHtml}`;
                    this.buildPrintWindow(`Projet ${project.name} - OrizonPlus`, body);
                },

                printSection(section) {
                    const project = this.selectedProject;
                    const allocated = this.getProjectAllocatedFromLines(project);
                    const spent = parseFloat(project.spent || 0);
                    const remaining = allocated - spent;
                    const spentPct = allocated > 0 ? ((spent / allocated) * 100).toFixed(1) : '0.0';
                    const remPct = allocated > 0 ? ((remaining / allocated) * 100).toFixed(1) : '0.0';

                    const metaHtml = `<div class="meta-row">
                ${project.department?`<div class="meta-box"><strong>Secteur</strong> ${project.department}</div>`:''}
                ${project.location?`<div class="meta-box"><strong>Lieu</strong> ${project.location}</div>`:''}
                ${project.date_of_creation?`<div class="meta-box"><strong>Date création</strong> ${this.formatDate(project.date_of_creation)}</div>`:''}
                ${project.contract_number?`<div class="meta-box"><strong>N° Bon de commande</strong> ${project.contract_number}</div>`:''}
            </div>
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr)">
                ${project.contract_amount_ht?`<div class="stat-box"><div class="label">Montant marché HT</div><div class="val">${this.formatCurrency(project.contract_amount_ht)}</div></div>`:''}
                ${project.execution_budget_ht?`<div class="stat-box"><div class="label">Budget exécution HT</div><div class="val">${this.formatCurrency(project.execution_budget_ht)}</div></div>`:''}
                ${project.collected_amount_ht?`<div class="stat-box success"><div class="label">Montant encaissé HT</div><div class="val">${this.formatCurrency(project.collected_amount_ht)}</div></div>`:''}
            </div>`;

                    const statsHtml = `<div class="stats-row">
                <div class="stat-box"><div class="label">Budget (100%)</div><div class="val">${this.formatCurrency(allocated)}</div></div>
                <div class="stat-box warning"><div class="label">Dépensé (${spentPct}%)</div><div class="val">${this.formatCurrency(spent)}</div></div>
                <div class="stat-box ${remaining>=0?'success':'danger'}"><div class="label">Restant (${remPct}%)</div><div class="val">${this.formatCurrency(remaining)}</div></div>
                <div class="stat-box"><div class="label">Nb Réalisations</div><div class="val">${this.projectExpenses.length}</div></div>
            </div>`;

                    let sectionContent = '';

                    if (section === 'lines') {
                        if (this.selectedProjectLines.length > 0) {
                            let rows = '';
                            this.selectedProjectLines.forEach(line => {
                                const la = parseFloat(line.allocated_amount || 0);
                                const ls = this.getLineSpent(line);
                                const lr = la - ls;
                                const lp = la > 0 ? ((ls / la) * 100).toFixed(1) : '0.0';
                                const bc = lr < 0 ? 'badge-err' : parseFloat(lp) > 80 ? 'badge-warn' : 'badge-ok';
                                rows += `<tr><td><strong>${line.name||line.line_name}</strong></td><td style="text-align:right">${this.formatCurrency(la)}</td><td style="text-align:right">${this.formatCurrency(ls)}</td><td style="text-align:right">${this.formatCurrency(lr)}</td><td style="text-align:center"><span class="badge ${bc}">${lp}%</span></td></tr>`;
                            });
                            sectionContent = `<div class="section"><h2 class="section-title">Lignes Budgétaires (${this.selectedProjectLines.length})</h2>
                        <table><thead><tr><th>Ligne</th><th style="text-align:right">Alloué</th><th style="text-align:right">Dépensé</th><th style="text-align:right">Restant</th><th style="text-align:center">% Utilisé</th></tr></thead><tbody>${rows}</tbody></table></div>`;
                        } else sectionContent = '<div class="section"><p style="text-align:center;color:#999;">Aucune ligne budgétaire</p></div>';
                    }

                    if (section === 'expenses') {
                        if (this.projectExpenses.length > 0) {
                            let rows = '';
                            this.projectExpenses.forEach((exp, i) => {
                                const nbDocs = this.getExpenseDocs(exp).length;
                                rows += `<tr><td>${i+1}</td><td><strong>${exp.budget_line_name||'N/A'}</strong></td><td>${exp.description||'-'}</td><td style="text-align:right">${this.formatCurrency(exp.amount)}</td><td style="text-align:center">${this.formatDate(exp.expense_date)}</td><td style="text-align:center">${nbDocs>0?nbDocs+' doc(s)':'-'}</td></tr>`;
                            });
                            sectionContent = `<div class="section"><h2 class="section-title">Réalisations (${this.projectExpenses.length})</h2>
                        <table><thead><tr><th>#</th><th>Ligne</th><th>Description</th><th style="text-align:right">Montant</th><th style="text-align:center">Date</th><th style="text-align:center">Docs</th></tr></thead>
                        <tbody>${rows}<tr class="total-row"><td colspan="3"><strong>TOTAL</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.expensesTotal)}</strong></td><td></td><td></td></tr></tbody></table></div>`;
                        } else sectionContent = '<div class="section"><p style="text-align:center;color:#999;">Aucune dépense</p></div>';
                    }

                    if (section === 'charts') {
                        const charts = [];
                        if (this.$refs.projectChart) charts.push({
                            title: 'Budget: Dépense vs Restant',
                            img: this.$refs.projectChart.toDataURL()
                        });
                        if (this.$refs.projectLinesChart) charts.push({
                            title: 'Répartition par Ligne',
                            img: this.$refs.projectLinesChart.toDataURL()
                        });
                        if (this.$refs.projectExpensesByLineChart) charts.push({
                            title: 'Alloué vs Dépensé par Ligne',
                            img: this.$refs.projectExpensesByLineChart.toDataURL()
                        });
                        if (this.$refs.projectTimelineChart) charts.push({
                            title: 'Évolution des Dépenses',
                            img: this.$refs.projectTimelineChart.toDataURL()
                        });
                        sectionContent = charts.map(c => `<div class="section"><h2 class="section-title">${c.title}</h2><div style="text-align:center"><img src="${c.img}" style="max-width:100%;height:auto;"/></div></div>`).join('');
                    }

                    if (section === 'summary') {
                        let lr = '';
                        this.selectedProjectLines.forEach(line => {
                            const la = parseFloat(line.allocated_amount || 0);
                            const ls = this.getLineSpent(line);
                            const lrem = la - ls;
                            const lp = la > 0 ? ((ls / la) * 100).toFixed(1) : '0.0';
                            const bc = lrem < 0 ? 'badge-err' : parseFloat(lp) > 80 ? 'badge-warn' : 'badge-ok';
                            lr += `<tr><td><strong>${line.name||line.line_name}</strong></td><td style="text-align:right">${this.formatCurrency(la)}</td><td style="text-align:right">${this.formatCurrency(ls)}</td><td style="text-align:right">${this.formatCurrency(lrem)}</td><td style="text-align:center"><span class="badge ${bc}">${lp}%</span></td></tr>`;
                        });
                        const linesTable = this.selectedProjectLines.length > 0 ? `<div class="section"><h2 class="section-title">Lignes Budgétaires</h2><table><thead><tr><th>Ligne</th><th style="text-align:right">Alloué</th><th style="text-align:right">Dépensé</th><th style="text-align:right">Restant</th><th style="text-align:center">%</th></tr></thead><tbody>${lr}</tbody></table></div>` : '';
                        let er = '';
                        this.projectExpenses.forEach((exp, i) => {
                            const nbDocs = this.getExpenseDocs(exp).length;
                            er += `<tr><td>${i+1}</td><td>${exp.budget_line_name||'N/A'}</td><td>${exp.description||'-'}</td><td style="text-align:right">${this.formatCurrency(exp.amount)}</td><td style="text-align:center">${this.formatDate(exp.expense_date)}</td><td style="text-align:center">${nbDocs>0?nbDocs+' doc(s)':'-'}</td></tr>`;
                        });
                        const expTable = this.projectExpenses.length > 0 ? `<div class="section"><h2 class="section-title">Réalisations (${this.projectExpenses.length})</h2><table><thead><tr><th>#</th><th>Ligne</th><th>Description</th><th style="text-align:right">Montant</th><th style="text-align:center">Date</th><th style="text-align:center">Docs</th></tr></thead><tbody>${er}<tr class="total-row"><td colspan="3"><strong>TOTAL</strong></td><td style="text-align:right"><strong>${this.formatCurrency(this.expensesTotal)}</strong></td><td></td><td></td></tr></tbody></table></div>` : '';
                        sectionContent = linesTable + expTable;
                    }

                    const label = section === 'lines' ? 'Lignes Budgétaires' : section === 'expenses' ? 'Réalisations' : section === 'charts' ? 'Graphiques' : 'Résumé Complet';
                    const body = `
                <div class="print-header"><h1>${project.name}</h1><div class="subtitle">${label}</div></div>
                ${metaHtml}
                ${statsHtml}
                ${sectionContent}`;
                    this.buildPrintWindow(`${project.name} — ${label} — OrizonPlus`, body);
                },

                // ==================== GRAPHIQUES GLOBAUX ====================
                renderCharts() {
                    if (this.isRenderingCharts) return;
                    this.isRenderingCharts = true;
                    setTimeout(() => {
                        this.$nextTick(() => {
                            try {
                                [this.budgetPieChart, this.progressBarChart].forEach(c => {
                                    try {
                                        c && c.destroy();
                                    } catch (e) {}
                                });
                                this.budgetPieChart = null;
                                this.progressBarChart = null;
                                const pieCanvas = this.$refs.budgetPieChart;
                                const barCanvas = this.$refs.progressBarChart;
                                if (!pieCanvas || !barCanvas || !document.body.contains(pieCanvas) || this.projects.length === 0) {
                                    this.isRenderingCharts = false;
                                    return;
                                }
                                const colors = ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b', '#ff6b9d', '#10b981', '#f59e0b', '#8b5cf6'];
                                pieCanvas.parentElement && Object.assign(pieCanvas.parentElement.style, {
                                    position: 'relative',
                                    height: '400px',
                                    width: '100%'
                                });
                                this.budgetPieChart = new Chart(pieCanvas.getContext('2d'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: this.projects.map(p => p.name),
                                        datasets: [{
                                            data: this.projects.map(p => this.getProjectAllocatedFromLines(p)),
                                            backgroundColor: colors.slice(0, this.projects.length),
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
                                                backgroundColor: 'rgba(0,0,0,0.8)',
                                                callbacks: {
                                                    label: (ctx) => {
                                                        const v = ctx.parsed;
                                                        const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                        const p = t > 0 ? ((v / t) * 100).toFixed(1) : 0;
                                                        return `${ctx.label}: ${this.formatCurrency(v)} (${p}%)`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                                barCanvas.parentElement && Object.assign(barCanvas.parentElement.style, {
                                    position: 'relative',
                                    height: '400px',
                                    width: '100%'
                                });
                                this.progressBarChart = new Chart(barCanvas.getContext('2d'), {
                                    type: 'bar',
                                    data: {
                                        labels: this.projects.map(p => p.name.length > 15 ? p.name.substring(0, 12) + '...' : p.name),
                                        datasets: [{
                                            label: 'Budget Alloué',
                                            data: this.projects.map(p => this.getProjectAllocatedFromLines(p)),
                                            backgroundColor: 'rgba(0,112,243,0.8)',
                                            borderColor: '#0070f3',
                                            borderWidth: 2,
                                            borderRadius: 4
                                        }, {
                                            label: 'Réalisations',
                                            data: this.projects.map(p => parseFloat(p.spent || 0)),
                                            backgroundColor: 'rgba(255,184,0,0.8)',
                                            borderColor: '#ffb800',
                                            borderWidth: 2,
                                            borderRadius: 4
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
                                                    }
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
                                                backgroundColor: 'rgba(0,0,0,0.8)',
                                                padding: 12,
                                                cornerRadius: 8
                                            }
                                        }
                                    }
                                });
                                this.isRenderingCharts = false;
                            } catch (e) {
                                console.error('[v0]', e);
                                this.isRenderingCharts = false;
                            }
                        });
                    }, 200);
                },

                // ==================== GRAPHIQUES DETAIL ====================
                renderProjectChart() {
                    try {
                        this.projectDetailChart && this.projectDetailChart.destroy();
                    } catch (e) {}
                    if (!this.$refs.projectChart) return;
                    const allocated = this.selectedProjectLinesAllocatedTotal;
                    const spent = this.selectedProjectLinesSpentTotal;
                    this.projectDetailChart = new Chart(this.$refs.projectChart, {
                        type: 'doughnut',
                        data: {
                            labels: ['Dépensé', 'Restant'],
                            datasets: [{
                                data: [spent, Math.max(allocated - spent, 0)],
                                backgroundColor: ['#ffb800', '#00e676']
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
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const v = ctx.parsed;
                                            const p = allocated > 0 ? ((v / allocated) * 100).toFixed(1) : 0;
                                            return `${ctx.label}: ${this.formatCurrency(v)} (${p}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },
                renderProjectLinesChart() {
                    try {
                        this.projectLinesChart && this.projectLinesChart.destroy();
                    } catch (e) {}
                    if (!this.$refs.projectLinesChart || !this.selectedProjectLines.length) return;
                    const colors = ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b', '#ff6b9d', '#10b981'];
                    this.projectLinesChart = new Chart(this.$refs.projectLinesChart, {
                        type: 'pie',
                        data: {
                            labels: this.selectedProjectLines.map(l => l.name || l.line_name),
                            datasets: [{
                                data: this.selectedProjectLines.map(l => parseFloat(l.allocated_amount || 0)),
                                backgroundColor: colors.slice(0, this.selectedProjectLines.length)
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
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const v = ctx.parsed;
                                            const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            return `${ctx.label}: ${this.formatCurrency(v)} (${t>0?((v/t)*100).toFixed(1):0}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },
                renderProjectExpensesByLineChart() {
                    try {
                        this.projectExpensesByLineChart && this.projectExpensesByLineChart.destroy();
                    } catch (e) {}
                    if (!this.$refs.projectExpensesByLineChart || !this.selectedProjectLines.length) return;
                    this.projectExpensesByLineChart = new Chart(this.$refs.projectExpensesByLineChart, {
                        type: 'bar',
                        data: {
                            labels: this.selectedProjectLines.map(l => l.name || l.line_name),
                            datasets: [{
                                label: 'Alloué',
                                data: this.selectedProjectLines.map(l => parseFloat(l.allocated_amount || 0)),
                                backgroundColor: 'rgba(0,112,243,0.7)',
                                borderColor: '#0070f3',
                                borderWidth: 1
                            }, {
                                label: 'Dépensé',
                                data: this.selectedProjectLines.map(l => this.getLineSpent(l)),
                                backgroundColor: 'rgba(255,184,0,0.7)',
                                borderColor: '#ffb800',
                                borderWidth: 1
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
                                    },
                                    grid: {
                                        color: '#2a2a2a'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#ededed'
                                    },
                                    grid: {
                                        color: '#2a2a2a'
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
                },
                renderProjectTimelineChart() {
                    try {
                        this.projectTimelineChart && this.projectTimelineChart.destroy();
                    } catch (e) {}
                    if (!this.$refs.projectTimelineChart || !this.projectExpenses.length) return;
                    const grouped = {};
                    let cumul = 0;
                    [...this.projectExpenses].sort((a, b) => new Date(a.expense_date) - new Date(b.expense_date)).forEach(exp => {
                        const d = this.formatDate(exp.expense_date);
                        cumul += parseFloat(exp.amount || 0);
                        grouped[d] = cumul;
                    });
                    this.projectTimelineChart = new Chart(this.$refs.projectTimelineChart, {
                        type: 'line',
                        data: {
                            labels: Object.keys(grouped),
                            datasets: [{
                                label: 'Dépenses cumulées',
                                data: Object.values(grouped),
                                borderColor: '#00d4ff',
                                backgroundColor: 'rgba(0,212,255,0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#00d4ff',
                                pointBorderColor: '#fff',
                                pointRadius: 4
                            }, {
                                label: 'Budget alloué',
                                data: Object.keys(grouped).map(() => this.selectedProjectLinesAllocatedTotal),
                                borderColor: '#ff3b3b',
                                borderDash: [5, 5],
                                pointRadius: 0,
                                fill: false
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
                                    },
                                    grid: {
                                        color: '#2a2a2a'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#ededed'
                                    },
                                    grid: {
                                        color: '#2a2a2a'
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
                },
            },
            watch: {
                projects: {
                    handler() {
                        this.$nextTick(() => this.renderCharts());
                    },
                    deep: true
                }
            }
        }).mount('#app');
    </script>
</body>

</html>