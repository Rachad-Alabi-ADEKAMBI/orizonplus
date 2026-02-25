<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'consultant';
$user_id = $_SESSION['user_id'] ?? null;
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

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
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
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .pagination {
            display: flex;
            gap: 0.35rem;
            margin-top: 1.5rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .pagination button {
            min-width: 36px;
            height: 36px;
            padding: 0 0.6rem;
            border: 1px solid var(--border-color);
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .pagination button:hover:not(:disabled) {
            border-color: var(--accent-blue);
            background: var(--accent-blue);
            color: white;
        }

        .pagination button.active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .pagination button:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .pagination .pg-ellipsis {
            color: var(--text-secondary);
            font-size: 0.85rem;
            padding: 0 0.2rem;
            display: flex;
            align-items: center;
        }

        .pagination .pg-info {
            color: var(--text-secondary);
            font-size: 0.8rem;
            padding: 0 0.5rem;
            white-space: nowrap;
        }

        .pagination .pg-goto {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-left: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .pagination .pg-goto input {
            width: 52px;
            height: 36px;
            padding: 0 0.5rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 0.85rem;
            text-align: center;
        }

        .pagination .pg-goto input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .pagination .pg-goto button {
            min-width: unset;
            padding: 0 0.7rem;
            background: var(--bg-tertiary);
            font-size: 0.8rem;
        }

        .file-count-info {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .file-count-info.warning {
            color: var(--accent-yellow);
        }

        .file-count-info.danger {
            color: var(--accent-red);
        }

        .validation-badge {
            background: rgba(255, 184, 0, 0.2);
            color: var(--accent-yellow);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-locked {
            background: rgba(255, 59, 59, 0.15);
            color: var(--accent-red);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .locked-project-notice {
            background: rgba(255, 59, 59, 0.1);
            border: 1px solid rgba(255, 59, 59, 0.3);
            border-left: 3px solid var(--accent-red);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--accent-red);
            font-size: 0.9rem;
        }

        .locked-project-notice i {
            font-size: 1.25rem;
        }

        /* ── Supplier combo (select + input) ── */
        .supplier-combo {
            display: flex;
            gap: 0;
            align-items: stretch;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--bg-tertiary);
        }

        .supplier-combo-select {
            flex: 1;
            padding: 0.75rem;
            border-right: 1px solid var(--border-color);
        }

        .supplier-combo-new {
            flex: 1;
            padding: 0.75rem;
        }

        .supplier-combo-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .supplier-combo-sep {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.25rem;
            background: var(--bg-secondary);
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
        }

        .supplier-combo-sep span {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }

        .supplier-combo .form-select {
            background: var(--bg-secondary);
            border-color: var(--border-color);
            font-size: 0.82rem;
        }

        .supplier-combo .supplier-wrap input {
            background: var(--bg-secondary);
        }

        /* ── Success toast fournisseur ── */
        .supplier-success-toast {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            background: rgba(0, 230, 118, 0.12);
            border: 1px solid rgba(0, 230, 118, 0.4);
            border-left: 3px solid var(--accent-green);
            border-radius: var(--radius);
            color: var(--accent-green);
            font-size: 0.875rem;
            font-weight: 600;
            animation: toast-in 0.3s ease;
        }

        @keyframes toast-in {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Supplier autocomplete ── */
        .supplier-wrap {
            position: relative;
        }

        .supplier-wrap input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .supplier-wrap input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        .supplier-clear-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 0.9rem;
            line-height: 1;
            padding: 0;
        }

        .supplier-clear-btn:hover {
            color: var(--accent-red);
        }

        .supplier-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            z-index: 300;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
        }

        .supplier-item {
            padding: 0.65rem 1rem;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.15s;
        }

        .supplier-item:hover,
        .supplier-item.active {
            background: var(--bg-tertiary);
            color: var(--accent-blue);
        }

        .supplier-item.add-new {
            color: var(--accent-green);
            font-weight: 600;
            border-top: 1px solid var(--border-color);
        }

        .supplier-item.add-new:hover {
            background: rgba(0, 230, 118, 0.07);
            color: var(--accent-green);
        }

        .supplier-item.empty {
            color: var(--text-secondary);
            cursor: default;
            font-style: italic;
        }

        .supplier-selected {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            background: rgba(0, 112, 243, 0.12);
            border: 1px solid rgba(0, 112, 243, 0.3);
            border-radius: 20px;
            font-size: 0.82rem;
            color: var(--accent-blue);
            margin-top: 0.4rem;
        }

        .supplier-selected button {
            background: none;
            border: none;
            color: var(--accent-blue);
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            padding: 0;
            opacity: 0.7;
        }

        .supplier-selected button:hover {
            opacity: 1;
            color: var(--accent-red);
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

        .footer-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-stat {
            text-align: center;
        }

        .footer-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-blue);
        }

        .footer-stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-top: 0.25rem;
        }

        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 350px;
                min-height: 300px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
            }

            .filter-select,
            .filter-input,
            .search-input {
                width: 100%;
                max-width: 100% !important;
            }

            .search-box {
                min-width: 100%;
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
                background: var(--bg-secondary);
            }

            .table tbody tr.warning {
                border-left: 3px solid var(--accent-yellow);
                background: rgba(255, 184, 0, 0.05);
            }

            .table tbody tr.danger {
                border-left: 3px solid var(--accent-red);
                background: rgba(255, 59, 59, 0.05);
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 0.75rem;
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
                flex-shrink: 0;
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }

            .table tbody td .action-buttons {
                justify-content: flex-end;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-wrap: nowrap;
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
                    <li><a href="suppliers.php" class="nav-link"> <i class="fas fa-truck"></i> Fournisseurs</a></li>
                    <li v-if="user_role=='admin'">
                        <a href="users.php" class="nav-link" @click="closeMobileMenu">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>

                    <li v-if="user_role=='admin' || user_role=='utilisateur'"><a href="notifications.php" class="nav-link" @click="closeMobileMenu"><i class="fas fa-bell"></i> Notifications
                            <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                        </a></li>

                    <li v-if="user_role == 'utilisateur' || user_role == 'consultant'">
                        <a href="parameters.php" class="nav-link" @click="closeMobileMenu">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                    </li>
                    <li>
                        <a href="api/index.php?action=logout" class="nav-link" @click="closeMobileMenu" style="color: var(--accent-red);">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
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

                <div v-if="user_role == 'admin'" class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Validations En Attente</span>
                        <div class="stat-icon" style="background: rgba(255, 184, 0, 0.2); color: var(--accent-yellow);">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.pendingValidations }}</div>
                    <div class="stat-change">À traiter</div>
                </div>

                <div v-else class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Mes Validations En Attente</span>
                        <div class="stat-icon" style="background: rgba(255, 184, 0, 0.2); color: var(--accent-yellow);">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="stat-value">{{ stats.myPendingValidations }}</div>
                    <div class="stat-change">Demandes personnelles</div>
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

            <div class="section-card" v-show="!showValidationsSection">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-wallet"></i>
                        Gestion des Dépenses
                    </h2>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button v-if="canEdit" @click="toggleValidationsSection" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle"></i> Dépassements de Budget
                        </button>
                        <button v-if="canEdit" @click="openExpenseModal" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle Dépense
                        </button>
                    </div>
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
                            <option value="">Tous les projets</option>
                            <option v-for="project in availableProjects" :key="project.id" :value="project.id">
                                {{ reduceWord(project.name) }}
                            </option>
                        </select>
                        <select class="filter-select" v-model="supplierFilter" @change="filterExpenses"
                            style="max-width: 250px;">
                            <option value="">Tous les fournisseurs</option>
                            <option v-for="s in availableSuppliers" :key="s.id" :value="s.id">
                                {{ s.name }}
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
                                    <th>Montant payé</th>
                                    <th>Statut</th>
                                    <th>Enregistré par</th>
                                    <th>Documents</th>
                                    <th v-if="canEdit" class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in paginatedExpenses" :key="expense.id"
                                    :class="getRowClass(expense)">
                                    <td data-label="Date">{{ formatDate(expense.expense_date) }}</td>
                                    <td data-label="Projet">
                                        <strong>{{ reduceWord(expense.project_name) }}</strong>
                                        <span v-if="isProjectLocked(expense.project_id)" title="Projet verrouillé" style="margin-left: 0.35rem; color: var(--accent-red); font-size: 0.75rem;">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </td>
                                    <td data-label="Ligne Budgétaire">{{ expense.budget_line_name }}</td>
                                    <td data-label="Description" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ expense.description || '-' }}
                                    </td>
                                    <td data-label="Montant"><strong>{{ formatCurrencyExact(expense.amount) }}</strong></td>
                                    <td data-label="Montant payé">
                                        <span v-if="expense.paid_amount !== null && expense.paid_amount !== ''">
                                            {{ formatCurrencyExact(expense.paid_amount) }}
                                        </span>
                                        <span v-else style="color:var(--text-secondary);">-</span>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="badge"
                                            :class="getBadgeClass(expense.remaining, expense.allocated_amount)">
                                            {{ getUsagePercentage(expense) }}%
                                        </span>
                                    </td>
                                    <td data-label="Enregistré par">
                                        <span v-if="expense.user_name" style="display:inline-flex;align-items:center;gap:0.35rem;">

                                            {{ expense.user_name }}
                                        </span>
                                        <span v-else style="color:var(--text-secondary);font-size:0.85rem;">-</span>
                                    </td>
                                    <td data-label="Documents">
                                        <button v-if="getExpenseDocuments(expense).length > 0"
                                            @click="viewExpenseDocuments(expense)"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-file"></i> {{ getExpenseDocuments(expense).length }}
                                        </button>
                                        <span v-else style="color: var(--text-secondary);">-</span>
                                    </td>
                                    <td v-if="canEdit" class="no-print" data-label="Actions">
                                        <div class="action-buttons">
                                            <button @click="openExpenseDetail(expense)" class="btn btn-sm btn-secondary" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <template v-if="canEditExpense(expense)">
                                                <button @click="editExpense(expense)" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="deleteExpense(expense)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </template>
                                            <span v-else-if="isProjectLocked(expense.project_id)" class="badge badge-locked" title="Projet verrouillé">
                                                <i class="fas fa-lock"></i> Verrouillé
                                            </span>
                                            <span v-else class="text-muted" style="font-size: 0.85rem;" title="Seul l'auteur de la dépense peut la modifier">
                                                <i class="fas fa-user-lock"></i>
                                            </span>
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
                        <!-- Précédent -->
                        <button @click="prevPage" :disabled="currentPage === 1">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <!-- Pages visibles -->
                        <template v-for="p in getVisiblePages(currentPage, totalPages)" :key="p">
                            <span v-if="p === '...'" class="pg-ellipsis">…</span>
                            <button v-else @click="currentPage = p" :class="{ active: currentPage === p }">{{ p }}</button>
                        </template>
                        <!-- Suivant -->
                        <button @click="nextPage" :disabled="currentPage === totalPages">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <!-- Info + aller à -->
                        <span class="pg-info">{{ currentPage }} / {{ totalPages }}</span>
                        <span class="pg-goto">
                            Aller à
                            <input type="number" v-model="goToPageExpenses" :min="1" :max="totalPages"
                                @keydown.enter="handleGoToPage(goToPageExpenses, totalPages, p => { currentPage = p; goToPageExpenses = ''; })"
                                placeholder="n°" />
                            <button @click="handleGoToPage(goToPageExpenses, totalPages, p => { currentPage = p; goToPageExpenses = ''; })">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section Dépassements de Budget -->
            <div v-if="showValidationsSection && (user_role == 'admin' || expensesValidations.length > 0)" class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dépassements de Budget
                    </h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <button @click="toggleValidationsSection" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                        <button @click="fetchExpensesValidations" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>

                <div class="section-content">
                    <div v-if="user_role == 'admin'">
                        <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                            Toutes les demandes de dépassement de budget ({{ adminValidations.length }} au total)
                        </p>

                        <!-- Filtres pour dépassements -->
                        <div class="filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="search-input" v-model="overflowSearchQuery"
                                    @input="filterOverflowExpenses" style="max-width: 250px;"
                                    placeholder="Rechercher...">
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="table" v-if="paginatedAdminValidations.length > 0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Demandeur</th>
                                        <th>Projet</th>
                                        <th>Ligne Budgétaire</th>
                                        <th>Montant Demandé</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th>Documents</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="validation in paginatedAdminValidations" :key="validation.validation_id"
                                        :class="validation.status === 'en attente' ? 'warning' : ''">
                                        <td data-label="Date">{{ formatDate(validation.created_at) }}</td>
                                        <td data-label="Demandeur"><strong>{{ validation.user_name }}</strong></td>
                                        <td data-label="Projet">{{ reduceWord(validation.project_name) }}</td>
                                        <td data-label="Ligne Budgétaire">{{ validation.budget_line_name }}</td>
                                        <td data-label="Montant Demandé"><strong>{{ formatCurrencyExact(validation.requested_amount) }}</strong></td>
                                        <td data-label="Description" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ validation.description || '-' }}
                                        </td>
                                        <td data-label="Statut">
                                            <span class="validation-badge" v-if="validation.status === 'en attente'">
                                                <i class="fas fa-hourglass-half"></i> En attente
                                            </span>
                                            <span class="badge badge-success" v-else-if="validation.status === 'acceptée'">
                                                <i class="fas fa-check"></i> Acceptée
                                            </span>
                                            <span class="badge badge-danger" v-else>
                                                <i class="fas fa-times"></i> Refusée
                                            </span>
                                        </td>
                                        <td data-label="Documents">
                                            <button v-if="getValidationDocuments(validation).length > 0"
                                                @click="viewValidationDocuments(validation)"
                                                class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file"></i> {{ getValidationDocuments(validation).length }}
                                            </button>
                                            <span v-else style="color: var(--text-secondary);">-</span>
                                        </td>
                                        <td class="no-print" data-label="Actions">
                                            <div class="action-buttons">
                                                <button @click="openValidationDetail(validation)" class="btn btn-sm btn-secondary" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button v-if="validation.status === 'en attente'"
                                                    @click="acceptValidation(validation)"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                                <button v-if="validation.status === 'en attente'"
                                                    @click="rejectValidation(validation)"
                                                    class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>Aucune demande de dépassement</p>
                            </div>
                        </div>

                        <!-- Pagination Admin -->
                        <div class="pagination" v-if="totalAdminValidationsPages > 1">
                            <button @click="adminValidationsCurrentPage--" :disabled="adminValidationsCurrentPage === 1">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <template v-for="p in getVisiblePages(adminValidationsCurrentPage, totalAdminValidationsPages)" :key="p">
                                <span v-if="p === '...'" class="pg-ellipsis">…</span>
                                <button v-else @click="adminValidationsCurrentPage = p" :class="{ active: adminValidationsCurrentPage === p }">{{ p }}</button>
                            </template>
                            <button @click="adminValidationsCurrentPage++" :disabled="adminValidationsCurrentPage === totalAdminValidationsPages">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <span class="pg-info">{{ adminValidationsCurrentPage }} / {{ totalAdminValidationsPages }}</span>
                            <span class="pg-goto">
                                Aller à
                                <input type="number" v-model="goToPageAdmin" :min="1" :max="totalAdminValidationsPages"
                                    @keydown.enter="handleGoToPage(goToPageAdmin, totalAdminValidationsPages, p => { adminValidationsCurrentPage = p; goToPageAdmin = ''; })"
                                    placeholder="n°" />
                                <button @click="handleGoToPage(goToPageAdmin, totalAdminValidationsPages, p => { adminValidationsCurrentPage = p; goToPageAdmin = ''; })">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div v-else>
                        <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                            Vos demandes de dépassement de budget ({{ userValidations.length }} au total) - <strong style="color: var(--accent-yellow);">{{ stats.myPendingValidations }} en attente</strong>
                        </p>

                        <!-- Filtre de recherche utilisateur -->
                        <div class="filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="search-input" v-model="userOverflowSearchQuery"
                                    @input="filterOverflowExpenses" style="max-width: 300px;"
                                    placeholder="Rechercher par projet, ligne budgétaire...">
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="table" v-if="paginatedUserValidations.length > 0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Projet</th>
                                        <th>Ligne Budgétaire</th>
                                        <th>Montant Demandé</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th>Documents</th>
                                        <th class="no-print">Détail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="validation in paginatedUserValidations" :key="validation.validation_id"
                                        :class="validation.status === 'en attente' ? 'warning' : ''">
                                        <td data-label="Date">{{ formatDate(validation.created_at) }}</td>
                                        <td data-label="Projet">{{ reduceWord(validation.project_name) }}</td>
                                        <td data-label="Ligne Budgétaire">{{ validation.budget_line_name }}</td>
                                        <td data-label="Montant Demandé"><strong>{{ formatCurrencyExact(validation.requested_amount) }}</strong></td>
                                        <td data-label="Description" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ validation.description || '-' }}
                                        </td>
                                        <td data-label="Statut">
                                            <span class="validation-badge" v-if="validation.status === 'en attente'">
                                                <i class="fas fa-hourglass-half"></i> En attente
                                            </span>
                                            <span class="badge badge-success" v-else-if="validation.status === 'acceptée'">
                                                <i class="fas fa-check"></i> Acceptée
                                            </span>
                                            <span class="badge badge-danger" v-else>
                                                <i class="fas fa-times"></i> Refusée
                                            </span>
                                        </td>
                                        <td data-label="Documents">
                                            <button v-if="getValidationDocuments(validation).length > 0"
                                                @click="viewValidationDocuments(validation)"
                                                class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file"></i> {{ getValidationDocuments(validation).length }}
                                            </button>
                                            <span v-else style="color: var(--text-secondary);">-</span>
                                        </td>
                                        <td class="no-print" data-label="Détail">
                                            <button @click="openValidationDetail(validation)" class="btn btn-sm btn-secondary" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>Vous n'avez aucune demande de dépassement</p>
                            </div>
                        </div>

                        <!-- Pagination User Validations -->
                        <div class="pagination" v-if="totalUserValidationsPages > 1">
                            <button @click="userValidationsCurrentPage--" :disabled="userValidationsCurrentPage === 1">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <template v-for="p in getVisiblePages(userValidationsCurrentPage, totalUserValidationsPages)" :key="p">
                                <span v-if="p === '...'" class="pg-ellipsis">…</span>
                                <button v-else @click="userValidationsCurrentPage = p" :class="{ active: userValidationsCurrentPage === p }">{{ p }}</button>
                            </template>
                            <button @click="userValidationsCurrentPage++" :disabled="userValidationsCurrentPage === totalUserValidationsPages">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <span class="pg-info">{{ userValidationsCurrentPage }} / {{ totalUserValidationsPages }}</span>
                            <span class="pg-goto">
                                Aller à
                                <input type="number" v-model="goToPageUser" :min="1" :max="totalUserValidationsPages"
                                    @keydown.enter="handleGoToPage(goToPageUser, totalUserValidationsPages, p => { userValidationsCurrentPage = p; goToPageUser = ''; })"
                                    placeholder="n°" />
                                <button @click="handleGoToPage(goToPageUser, totalUserValidationsPages, p => { userValidationsCurrentPage = p; goToPageUser = ''; })">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

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
                    <div v-if="expense.project_id && isProjectLocked(expense.project_id)" class="locked-project-notice">
                        <i class="fas fa-lock"></i>
                        <span>Ce projet est verrouillé. Les modifications ne sont pas autorisées.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Projet *</label>
                        <select class="form-select" v-model="expense.project_id" @change="fetchLines" required>
                            <option value="">Sélectionner un projet</option>
                            <option v-for="project in unlockedProjects" :key="project.id" :value="project.id">
                                {{ project.name }}
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
                        <label class="form-label">Description</label>
                        <input type="text" class="form-input" v-model="expense.description"
                            placeholder="Description de la dépense">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Montant (FCFA) *</label>
                        <input type="number" class="form-input" v-model="expense.amount" step="0.01" required
                            placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-check-circle" style="margin-right:0.4rem;color:var(--accent-green);"></i>Montant payé (FCFA)</label>
                        <input type="number" class="form-input" v-model="expense.paid_amount" step="0.01" min="0"
                            placeholder="0.00">
                        <!-- Reste à payer calculé automatiquement -->
                        <div v-if="expense.paid_amount !== null && expense.paid_amount !== '' && expense.amount"
                            style="margin-top:0.5rem;padding:0.6rem 0.9rem;background:var(--bg-tertiary);border-radius:var(--radius);border:1px solid var(--border-color);display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;">
                            <i class="fas fa-info-circle" style="color:var(--accent-cyan);"></i>
                            <span style="color:var(--text-secondary);">Reste à payer :</span>
                            <strong :style="{color: (Number(expense.amount) - Number(expense.paid_amount)) <= 0 ? 'var(--accent-green)' : 'var(--accent-yellow)'}">
                                {{ formatCurrencyExact(Number(expense.amount) - Number(expense.paid_amount)) }}
                            </strong>
                        </div>
                    </div>

                    <!-- ── Fournisseur : select + saisie libre ── -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-truck" style="margin-right:0.4rem;color:var(--accent-cyan);"></i>
                            Fournisseur
                        </label>

                        <!-- Toast succès création fournisseur -->
                        <div v-if="supplierSuccessMsg" class="supplier-success-toast">
                            <i class="fas fa-check-circle"></i>
                            {{ supplierSuccessMsg }}
                        </div>

                        <!-- Badge fournisseur sélectionné -->
                        <div v-if="expense.supplier_id" class="supplier-selected">
                            <i class="fas fa-check-circle"></i>
                            {{ expense.supplier_name }}
                            <button type="button" @click="clearSupplier" title="Retirer">&times;</button>
                        </div>

                        <!-- Panneau select + saisie libre -->
                        <div v-else class="supplier-combo">
                            <!-- Colonne gauche : select liste existante -->
                            <div class="supplier-combo-select">
                                <div class="supplier-combo-label"><i class="fas fa-list"></i> Choisir un existant</div>
                                <select class="form-select" @change="onSupplierSelectChange" v-model="supplierSelectVal">
                                    <option value="">-- Sélectionner --</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>

                            <!-- Séparateur -->
                            <div class="supplier-combo-sep"><span>ou</span></div>

                            <!-- Colonne droite : saisie nouveau -->
                            <div class="supplier-combo-new">
                                <div class="supplier-combo-label"><i class="fas fa-plus"></i> Nouveau fournisseur</div>
                                <div class="supplier-wrap" v-click-outside="closeSupplierDropdown">
                                    <input
                                        type="text"
                                        v-model="supplierSearch"
                                        @input="onSupplierInput"
                                        @focus="showSupplierDropdown = true"
                                        @keydown.down.prevent="highlightNext"
                                        @keydown.up.prevent="highlightPrev"
                                        @keydown.enter.prevent="pickHighlighted"
                                        @keydown.esc="closeSupplierDropdown"
                                        placeholder="Taper un nom..."
                                        autocomplete="off" />
                                    <button v-if="supplierSearch" class="supplier-clear-btn" type="button"
                                        @click="supplierSearch=''; showSupplierDropdown=false">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <!-- Dropdown suggestions + ajout -->
                                    <div class="supplier-dropdown" v-if="showSupplierDropdown && (filteredSuppliers.length > 0 || canAddNewSupplier)">
                                        <div
                                            v-for="(s, idx) in filteredSuppliers"
                                            :key="s.id"
                                            class="supplier-item"
                                            :class="{ active: supplierHighlight === idx }"
                                            @mousedown.prevent="pickSupplier(s)">
                                            <i class="fas fa-building" style="font-size:0.7rem;opacity:0.5;"></i>
                                            {{ s.name }}
                                        </div>
                                        <div
                                            v-if="canAddNewSupplier"
                                            class="supplier-item add-new"
                                            :class="{ active: supplierHighlight === filteredSuppliers.length }"
                                            @mousedown.prevent="createAndPickSupplier">
                                            <i class="fas fa-plus-circle"></i>
                                            Enregistrer "{{ supplierSearch.trim() }}"
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Documents justificatifs (PDF, PNG, JPG - Max 15 fichiers, 5Mo par fichier)</label>

                        <!-- Documents existants (mode édition) -->
                        <div v-if="isEditMode && expense.documents && expense.documents.length > 0" style="margin-bottom: 1rem;">
                            <div v-for="(doc, index) in expense.documents" :key="index" class="attached-file" style="margin-bottom: 0.5rem;">
                                <i :class="getDocumentIcon(doc)"
                                    :style="{ color: getDocumentColor(doc) }"></i>
                                <span>Document {{ index + 1 }}</span>
                                <button @click="viewDocumentFromPath(doc)" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                                <button @click="removeExistingDocumentByIndex(index)" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Zone d'upload -->
                        <div class="file-upload-area" :class="{ dragover: isDragging }"
                            @click="$refs.fileInput.click()" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
                            @drop.prevent="onFileDrop">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--accent-blue);"></i>
                            <p style="margin-top: 1rem; color: var(--text-secondary);">
                                Cliquez ou glissez des fichiers ici (plusieurs fichiers possibles)
                            </p>
                            <input type="file" ref="fileInput" class="file-input" @change="onFileSelect"
                                accept=".pdf,.png,.jpg,.jpeg" multiple>
                        </div>

                        <div :class="['file-count-info', getTotalFilesClass()]">
                            {{ getTotalFilesCount() }} / 15 fichiers
                        </div>

                        <!-- Fichiers sélectionnés -->
                        <div v-if="selectedFiles.length > 0" style="margin-top: 1rem;">
                            <div v-for="(file, index) in selectedFiles" :key="index" class="file-preview" style="margin-bottom: 0.5rem;">
                                <div class="file-info">
                                    <i :class="getFileIcon(file)" :style="{ color: getFileColor(file) }"></i>
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="file-name">{{ file.name }}</div>
                                        <div class="file-size">{{ formatFileSize(file.size) }}</div>
                                    </div>
                                </div>
                                <button @click="removeFileByIndex(index)" class="btn btn-sm btn-danger btn-icon">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
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

        <!-- Modal Demande de Validation -->
        <div class="modal-overlay" :class="{ active: modals.validation }" @click.self="closeValidationModal">
            <div class="modal" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-exclamation-triangle" style="color: var(--accent-yellow);"></i>
                        Dépassement de Budget
                    </h3>
                    <button class="modal-close" @click="closeValidationModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="info-box danger">
                        <p style="margin-bottom: 1rem;">
                            <strong>Attention !</strong> Le montant de cette dépense dépasse le budget restant de la ligne budgétaire sélectionnée.
                        </p>
                        <div class="info-row">
                            <span>Budget Restant:</span>
                            <strong style="color: var(--accent-red);">{{ formatCurrency(selectedLine?.remaining || 0) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Montant de la Dépense:</span>
                            <strong>{{ formatCurrency(expense.amount) }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Dépassement:</span>
                            <strong style="color: var(--accent-red);">
                                {{ formatCurrency(Math.abs((selectedLine?.remaining || 0) - expense.amount)) }}
                            </strong>
                        </div>
                    </div>
                    <p style="margin-top: 1.5rem; color: var(--text-secondary);">
                        Voulez-vous soumettre cette dépense à l'administrateur pour validation ?
                    </p>
                </div>
                <div class="modal-footer">
                    <button @click="closeValidationModal" class="btn btn-secondary">Annuler</button>
                    <button @click="submitForValidation" class="btn btn-warning" :disabled="isSaving">
                        <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        {{ isSaving ? 'Envoi...' : 'Soumettre pour Validation' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Visualiseur de Documents d'une Dépense -->
        <div class="modal-overlay" :class="{ active: modals.expenseDocuments }" @click.self="closeExpenseDocuments">
            <div class="modal" style="max-width: 900px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-file"></i>
                        Documents de la Dépense
                    </h3>
                    <button class="modal-close" @click="closeExpenseDocuments">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <div v-if="viewingExpenseDocuments && viewingExpenseDocuments.length > 0">
                        <div v-for="(doc, index) in viewingExpenseDocuments" :key="index" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color);">
                            <h4 style="margin-bottom: 1rem;">Document {{ index + 1 }}: {{ doc }}</h4>
                            <div style="background: var(--bg-tertiary); border-radius: var(--radius); overflow: hidden; max-height: 500px;">
                                <iframe v-if="!isImage(doc)" :src="'images/' + doc"
                                    style="width: 100%; height: 500px; border: none;"></iframe>
                                <img v-else :src="'images/' + doc" style="width: 100%; height: auto; max-height: 500px;"
                                    alt="Document">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Visualiseur de Documents de Validation -->
        <div class="modal-overlay" :class="{ active: modals.validationDocuments }" @click.self="closeValidationDocuments">
            <div class="modal" style="max-width: 900px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-file"></i>
                        Documents de la Demande
                    </h3>
                    <button class="modal-close" @click="closeValidationDocuments">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <div v-if="viewingValidationDocuments && viewingValidationDocuments.length > 0">
                        <div v-for="(doc, index) in viewingValidationDocuments" :key="index" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color);">
                            <h4 style="margin-bottom: 1rem;">Document {{ index + 1 }}: {{ doc }}</h4>
                            <div style="background: var(--bg-tertiary); border-radius: var(--radius); overflow: hidden; max-height: 500px;">
                                <iframe v-if="!isImage(doc)" :src="'images/' + doc"
                                    style="width: 100%; height: 500px; border: none;"></iframe>
                                <img v-else :src="'images/' + doc" style="width: 100%; height: auto; max-height: 500px;"
                                    alt="Document">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Détail Dépense -->
        <div class="modal-overlay" :class="{ active: modals.expenseDetail }" @click.self="closeExpenseDetail">
            <div class="modal" style="max-width:640px;">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-receipt" style="color:var(--accent-blue);margin-right:0.5rem;"></i>Détail de la Dépense</h3>
                    <button class="modal-close" @click="closeExpenseDetail"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body" v-if="detailExpense">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                        <div>
                            <div class="form-label">Projet</div>
                            <div style="font-weight:600;">{{ detailExpense.project_name }}</div>
                        </div>
                        <div>
                            <div class="form-label">Ligne Budgétaire</div>
                            <div>{{ detailExpense.budget_line_name }}</div>
                        </div>
                        <div>
                            <div class="form-label">Date</div>
                            <div>{{ formatDate(detailExpense.expense_date) }}</div>
                        </div>
                        <div>
                            <div class="form-label">Enregistré par</div>
                            <div>{{ detailExpense.user_name || '-' }}</div>
                        </div>
                        <div>
                            <div class="form-label">Montant</div>
                            <div style="font-weight:700;font-size:1.1rem;color:var(--accent-yellow);">{{ formatCurrencyExact(detailExpense.amount) }}</div>
                        </div>
                        <div>
                            <div class="form-label">Montant payé</div>
                            <div :style="{fontWeight:'600',color:detailExpense.paid_amount?'var(--accent-green)':'var(--text-secondary)'}">
                                {{ detailExpense.paid_amount ? formatCurrencyExact(detailExpense.paid_amount) : '-' }}
                            </div>
                        </div>
                        <div v-if="detailExpense.paid_amount !== null && detailExpense.paid_amount !== '' && detailExpense.amount">
                            <div class="form-label">Reste à payer</div>
                            <div :style="{fontWeight:'600',color:(Number(detailExpense.amount)-Number(detailExpense.paid_amount))<=0?'var(--accent-green)':'var(--accent-red)'}">
                                {{ formatCurrencyExact(Number(detailExpense.amount) - Number(detailExpense.paid_amount)) }}
                            </div>
                        </div>
                        <div>
                            <div class="form-label">Fournisseur</div>
                            <div>
                                <i v-if="detailExpense.supplier_name" class="fas fa-building" style="color:var(--accent-cyan);margin-right:0.3rem;font-size:0.85rem;"></i>
                                {{ detailExpense.supplier_name || '-' }}
                            </div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <div class="form-label">Description</div>
                            <div style="color:var(--text-secondary);">{{ detailExpense.description || '-' }}</div>
                        </div>
                        <div>
                            <div class="form-label">Budget Alloué (ligne)</div>
                            <div>{{ formatCurrencyExact(detailExpense.allocated_amount) }}</div>
                        </div>
                        <div>
                            <div class="form-label">Total Dépensé (ligne)</div>
                            <div :style="{color:parseFloat(detailExpense.spent)>parseFloat(detailExpense.allocated_amount)?'var(--accent-red)':'var(--text-primary)'}">
                                {{ formatCurrencyExact(detailExpense.spent) }}
                            </div>
                        </div>
                        <div>
                            <div class="form-label">Utilisation Budget</div>
                            <span class="badge" :class="getBadgeClass(detailExpense.remaining, detailExpense.allocated_amount)">
                                {{ getUsagePercentage(detailExpense) }}%
                            </span>
                        </div>
                        <div>
                            <div class="form-label">Créé le</div>
                            <div style="color:var(--text-secondary);font-size:0.875rem;">{{ detailExpense.created_at ? formatDate(detailExpense.created_at) : '-' }}</div>
                        </div>
                        <div v-if="getExpenseDocuments(detailExpense).length > 0" style="grid-column:1/-1;">
                            <div class="form-label">Documents</div>
                            <button @click="viewExpenseDocuments(detailExpense); closeExpenseDetail();" class="btn btn-sm btn-secondary">
                                <i class="fas fa-file"></i> Voir {{ getExpenseDocuments(detailExpense).length }} document(s)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeExpenseDetail" class="btn btn-secondary">Fermer</button>
                    <button v-if="detailExpense && canEditExpense(detailExpense)" @click="editExpense(detailExpense); closeExpenseDetail();" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Détail Dépassement de Budget -->
        <div class="modal-overlay" :class="{ active: modals.validationDetail }" @click.self="closeValidationDetail">
            <div class="modal" style="max-width:580px;">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-exclamation-triangle" style="color:var(--accent-yellow);margin-right:0.5rem;"></i>Détail du Dépassement</h3>
                    <button class="modal-close" @click="closeValidationDetail"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body" v-if="detailValidation">
                    <div style="margin-bottom:1rem;">
                        <span class="validation-badge" v-if="detailValidation.status === 'en attente'"><i class="fas fa-hourglass-half"></i> En attente</span>
                        <span class="badge badge-success" v-else-if="detailValidation.status === 'acceptée'"><i class="fas fa-check"></i> Acceptée</span>
                        <span class="badge badge-danger" v-else><i class="fas fa-times"></i> Refusée</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                        <div>
                            <div class="form-label">Demandeur</div>
                            <div style="font-weight:600;">{{ detailValidation.user_name || '-' }}</div>
                        </div>
                        <div>
                            <div class="form-label">Date de la demande</div>
                            <div>{{ formatDate(detailValidation.created_at) }}</div>
                        </div>
                        <div>
                            <div class="form-label">Projet</div>
                            <div style="font-weight:600;">{{ detailValidation.project_name }}</div>
                        </div>
                        <div>
                            <div class="form-label">Ligne Budgétaire</div>
                            <div>{{ detailValidation.budget_line_name }}</div>
                        </div>
                        <div>
                            <div class="form-label">Montant Demandé</div>
                            <div style="font-weight:700;font-size:1.1rem;color:var(--accent-red);">{{ formatCurrencyExact(detailValidation.requested_amount) }}</div>
                        </div>
                        <div>
                            <div class="form-label">Fournisseur</div>
                            <div>
                                <i v-if="detailValidation.supplier_name" class="fas fa-building" style="color:var(--accent-cyan);margin-right:0.3rem;font-size:0.85rem;"></i>
                                {{ detailValidation.supplier_name || '-' }}
                            </div>
                        </div>
                        <div v-if="detailValidation.paid_amount !== null && detailValidation.paid_amount !== undefined">
                            <div class="form-label">Montant payé</div>
                            <div :style="{fontWeight:'600',color:detailValidation.paid_amount?'var(--accent-green)':'var(--text-secondary)'}">
                                {{ detailValidation.paid_amount ? formatCurrencyExact(detailValidation.paid_amount) : '-' }}
                            </div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <div class="form-label">Description</div>
                            <div style="color:var(--text-secondary);">{{ detailValidation.description || '-' }}</div>
                        </div>
                        <div v-if="getValidationDocuments(detailValidation).length > 0" style="grid-column:1/-1;">
                            <div class="form-label">Documents</div>
                            <button @click="viewValidationDocuments(detailValidation); closeValidationDetail();" class="btn btn-sm btn-secondary">
                                <i class="fas fa-file"></i> Voir {{ getValidationDocuments(detailValidation).length }} document(s)
                            </button>
                        </div>
                    </div>
                    <div v-if="user_role === 'admin' && detailValidation.status === 'en attente'" style="margin-top:1.5rem;display:flex;gap:1rem;">
                        <button @click="acceptValidation(detailValidation); closeValidationDetail();" class="btn btn-success" style="flex:1;">
                            <i class="fas fa-check"></i> Accepter
                        </button>
                        <button @click="rejectValidation(detailValidation); closeValidationDetail();" class="btn btn-danger" style="flex:1;">
                            <i class="fas fa-times"></i> Refuser
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeValidationDetail" class="btn btn-secondary">Fermer</button>
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
                    user_id: <?php echo $user_id; ?>,
                    user_name: '<?php echo $_SESSION["user_name"] ?>',
                    user_role: '<?php echo $_SESSION["user_role"] ?? "user"; ?>',
                    unreadCount: 0,
                    menuOpen: false,
                    expenses: [],
                    filteredExpenses: [],
                    expensesValidations: [],
                    projects: [],
                    lines: [],
                    selectedLine: null,
                    suppliers: [],
                    supplierSearch: '',
                    showSupplierDropdown: false,
                    supplierHighlight: 0,
                    supplierSuccessMsg: '',
                    supplierSelectVal: '',
                    expense: {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: 0,
                        paid_amount: null,
                        supplier_id: null,
                        supplier_name: '',
                        expense_date: new Date().toISOString().split('T')[0],
                        description: '',
                        document: null,
                        documents: []
                    },
                    selectedFile: null,
                    selectedFiles: [],
                    isDragging: false,
                    isEditMode: false,
                    isSaving: false,
                    searchQuery: '',
                    projectFilter: '',
                    supplierFilter: '',
                    departmentFilter: '',
                    locationFilter: '',
                    statusFilter: '',
                    dateFrom: '',
                    dateTo: '',
                    overflowSearchQuery: '',
                    userOverflowSearchQuery: '',
                    overflowDepartmentFilter: '',
                    overflowLocationFilter: '',
                    filteredAdminValidations: [],
                    filteredUserValidations: [],
                    currentPage: 1,
                    itemsPerPage: 10,
                    validationsCurrentPage: 1,
                    validationsItemsPerPage: 10,
                    adminValidationsCurrentPage: 1,
                    userValidationsCurrentPage: 1,
                    goToPageExpenses: '',
                    goToPageAdmin: '',
                    goToPageUser: '',
                    showValidationsSection: false,
                    modals: {
                        expense: false,
                        documentViewer: false,
                        validation: false,
                        validationDocuments: false,
                        expenseDocuments: false,
                        expenseDetail: false,
                        validationDetail: false
                    },
                    detailExpense: null,
                    detailValidation: null,
                    viewingDocument: null,
                    viewingValidationDocuments: null,
                    viewingExpenseDocuments: null,
                    viewingExpenseDocumentsIndex: 0,
                    stats: {
                        totalExpenses: 0,
                        totalAmount: 0,
                        thisMonth: 0,
                        overBudget: 0,
                        pendingValidations: 0,
                        myPendingValidations: 0
                    },
                    projectsChart: null,
                    evolutionChart: null,
                    isRenderingCharts: false
                };
            },
            computed: {
                filteredSuppliers() {
                    const q = this.supplierSearch.trim().toLowerCase();
                    if (!q) return this.suppliers;
                    return this.suppliers.filter(s => s.name.toLowerCase().includes(q));
                },
                canAddNewSupplier() {
                    const q = this.supplierSearch.trim().toLowerCase();
                    if (!q) return false;
                    return !this.suppliers.some(s => s.name.toLowerCase() === q);
                },
                paginatedExpenses() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredExpenses.slice(start, start + this.itemsPerPage);
                },
                totalPages() {
                    return Math.ceil(this.filteredExpenses.length / this.itemsPerPage);
                },
                uniqueDepartments() {
                    const departments = new Set();
                    this.projects.forEach(p => {
                        if (p.department) departments.add(p.department);
                    });
                    return Array.from(departments).sort();
                },
                uniqueLocationsFromProjects() {
                    const locations = new Set();
                    this.projects.forEach(p => {
                        if (p.location) locations.add(p.location);
                    });
                    return Array.from(locations).sort();
                },
                uniqueOverflowLocations() {
                    const locations = new Set();
                    this.projects.forEach(p => {
                        if (p.location) locations.add(p.location);
                    });
                    return Array.from(locations).sort();
                },
                availableSuppliers() {
                    const seen = new Map();
                    this.expenses.forEach(e => {
                        if (e.supplier_id && e.supplier_name && !seen.has(e.supplier_id)) {
                            seen.set(e.supplier_id, {
                                id: e.supplier_id,
                                name: e.supplier_name
                            });
                        }
                    });
                    return Array.from(seen.values()).sort((a, b) => a.name.localeCompare(b.name));
                },
                availableProjects() {
                    let filtered = this.projects;
                    if (this.departmentFilter) {
                        filtered = filtered.filter(p => p.department === this.departmentFilter);
                    }
                    if (this.locationFilter) {
                        filtered = filtered.filter(p => p.location === this.locationFilter);
                    }
                    return filtered;
                },
                unlockedProjects() {
                    return this.projects.filter(p => p.project_status !== 'Verrouillé');
                },
                adminValidations() {
                    return this.expensesValidations;
                },
                userValidations() {
                    return this.expensesValidations.filter(v => v.user_id == this.user_id);
                },
                paginatedAdminValidations() {
                    const start = (this.adminValidationsCurrentPage - 1) * this.validationsItemsPerPage;
                    return this.filteredAdminValidations.slice(start, start + this.validationsItemsPerPage);
                },
                totalAdminValidationsPages() {
                    return Math.ceil(this.filteredAdminValidations.length / this.validationsItemsPerPage);
                },
                paginatedUserValidations() {
                    const start = (this.userValidationsCurrentPage - 1) * this.validationsItemsPerPage;
                    return this.filteredUserValidations.slice(start, start + this.validationsItemsPerPage);
                },
                totalUserValidationsPages() {
                    return Math.ceil(this.filteredUserValidations.length / this.validationsItemsPerPage);
                },
                totalValidationsPages() {
                    const validations = this.user_role === 'admin' ? this.adminValidations : this.userValidations;
                    return Math.ceil(validations.length / this.validationsItemsPerPage);
                }
            },
            mounted() {
                this.fetchProjects();
                this.fetchExpenses();
                this.fetchExpensesValidations();
                this.fetchNotifications();
                this.fetchSuppliers();
            },
            methods: {
                logout() {
                    window.location.href = 'api/index.php?action=logout';
                },
                openExpenseDetail(expense) {
                    this.detailExpense = expense;
                    this.modals.expenseDetail = true;
                },
                closeExpenseDetail() {
                    this.modals.expenseDetail = false;
                    this.detailExpense = null;
                },
                openValidationDetail(validation) {
                    this.detailValidation = validation;
                    this.modals.validationDetail = true;
                },
                closeValidationDetail() {
                    this.modals.validationDetail = false;
                    this.detailValidation = null;
                },
                closeMobileMenu() {
                    this.menuOpen = false;
                },
                toggleValidationsSection() {
                    this.showValidationsSection = !this.showValidationsSection;
                    if (this.showValidationsSection) {
                        this.validationsCurrentPage = 1;
                        this.fetchExpensesValidations();
                    }
                },
                async fetchProjects() {
                    try {
                        const route = `${API_BASE_URL}?action=getProjects`;
                        console.log('[v0] Route:', route);

                        const response = await fetch(route);
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);
                        this.projects = data.data || [];
                    } catch (error) {
                        console.error('[v0] Error fetching projects:', error);
                    }
                },
                async fetchNotifications() {
                    try {
                        const response = await fetch(`${API_BASE_URL}?action=getNotifications`);
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
                async fetchExpenses() {
                    try {
                        const route = `${API_BASE_URL}?action=getExpenses`;
                        console.log('[v0] Route:', route);

                        const response = await fetch(route);
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);
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
                async fetchExpensesValidations() {
                    try {
                        const route = `${API_BASE_URL}?action=getAllExpensesValidations`;
                        console.log('[v0] Route:', route);

                        const response = await fetch(route);
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);
                        this.expensesValidations = data.data || [];
                        this.filterOverflowExpenses();
                        this.updateValidationStats();
                    } catch (error) {
                        console.error('[v0] Error fetching validations:', error);
                    }
                },
                updateValidationStats() {
                    this.stats.pendingValidations = this.expensesValidations.filter(v => v.status === 'en attente').length;
                    this.stats.myPendingValidations = this.expensesValidations.filter(
                        v => v.user_id == this.user_id && v.status === 'en attente'
                    ).length;
                },
                openExpensesValidations() {
                    this.fetchExpensesValidations();
                    window.location.href = '#budget-overruns';
                },
                async acceptValidation(validation) {
                    if (!confirm('Êtes-vous sûr d\'accepter cette demande de dépassement ?')) return;

                    try {
                        const route = `${API_BASE_URL}?action=acceptExpenseValidation&validation_id=${validation.validation_id}`;
                        console.log('[v0] Route:', route);
                        console.log('[v0] Payload: {}');

                        const response = await fetch(route, {
                            method: 'POST'
                        });
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);

                        if (data.success) {
                            alert('Demande acceptée avec succès');
                            this.fetchExpensesValidations();
                            this.fetchExpenses();
                        } else {
                            alert(data.message || 'Erreur lors de l\'acceptation');
                        }
                    } catch (error) {
                        console.error('[v0] Error accepting validation:', error);
                        alert('Erreur lors de l\'acceptation de la demande');
                    }
                },
                async rejectValidation(validation) {
                    if (!confirm('Êtes-vous sûr de refuser cette demande de dépassement ?')) return;

                    try {
                        const route = `${API_BASE_URL}?action=rejectExpenseValidation&validation_id=${validation.validation_id}`;
                        console.log('[v0] Route:', route);
                        console.log('[v0] Payload: {}');

                        const response = await fetch(route, {
                            method: 'POST'
                        });
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);

                        if (data.success) {
                            alert('Demande refusée');
                            this.fetchExpensesValidations();
                        } else {
                            alert(data.message || 'Erreur lors du refus');
                        }
                    } catch (error) {
                        console.error('[v0] Error rejecting validation:', error);
                        alert('Erreur lors du refus de la demande');
                    }
                },
                viewValidationDocuments(validation) {
                    try {
                        const docs = typeof validation.documents === 'string' ?
                            JSON.parse(validation.documents) :
                            validation.documents || [];
                        this.viewingValidationDocuments = docs;
                        this.modals.validationDocuments = true;
                    } catch (e) {
                        console.error('[v0] Error parsing documents:', e);
                        alert('Erreur lors du chargement des documents');
                    }
                },
                closeValidationDocuments() {
                    this.modals.validationDocuments = false;
                    this.viewingValidationDocuments = null;
                },
                reduceWord(text) {
                    if (!text) return '';
                    const str = String(text);
                    if (str.length <= 20) {
                        return str;
                    }
                    return str.substring(0, 20) + '...';
                },
                getTotalFilesCount() {
                    const existingCount = this.isEditMode && this.expense.documents ? this.expense.documents.length : 0;
                    return existingCount + this.selectedFiles.length;
                },
                getTotalFilesClass() {
                    const total = this.getTotalFilesCount();
                    if (total > 15) return 'danger';
                    if (total > 12) return 'warning';
                    return '';
                },
                async fetchLines() {
                    if (!this.expense.project_id) return;
                    try {
                        const route = `${API_BASE_URL}?action=getProjectBudgetLines&project_id=${this.expense.project_id}`;
                        console.log('[v0] Route:', route);

                        const response = await fetch(route);
                        const data = await response.json();

                        console.log('[v0] Server Response:', data);
                        this.lines = data.data || [];

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
                    const files = Array.from(event.target.files);
                    files.forEach(file => this.validateAndSetFile(file));
                },
                onDragOver(event) {
                    this.isDragging = true;
                },
                onDragLeave(event) {
                    this.isDragging = false;
                },
                onFileDrop(event) {
                    this.isDragging = false;
                    const files = Array.from(event.dataTransfer.files);
                    files.forEach(file => this.validateAndSetFile(file));
                },
                validateAndSetFile(file) {
                    if (!file) return;

                    const totalFiles = this.getTotalFilesCount();
                    if (totalFiles >= 15) {
                        alert('Vous ne pouvez pas ajouter plus de 15 fichiers');
                        return;
                    }

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

                    this.selectedFiles.push(file);
                },
                removeFileByIndex(index) {
                    this.selectedFiles.splice(index, 1);
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },
                removeFile() {
                    this.selectedFiles = [];
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },
                async removeExistingDocumentByIndex(index) {
                    if (!confirm('Voulez-vous vraiment supprimer ce document ?')) return;

                    try {
                        this.expense.documents.splice(index, 1);

                        if (this.isEditMode && this.expense.id) {
                            const route = `${API_BASE_URL}?action=updateExpenseDocuments&id=${this.expense.id}`;
                            const payload = {
                                documents: JSON.stringify(this.expense.documents)
                            };
                            console.log('[v0] Route:', route);
                            console.log('[v0] Payload:', payload);

                            const formData = new FormData();
                            formData.append('documents', JSON.stringify(this.expense.documents));

                            const response = await fetch(route, {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();
                            console.log('[v0] Server Response:', data);

                            if (data.success) {
                                alert('Document supprimé avec succès');
                                this.fetchExpenses();
                            } else {
                                alert(data.message || 'Erreur lors de la suppression du document');
                            }
                        }
                    } catch (error) {
                        console.error('[v0] Error removing document:', error);
                        alert('Erreur lors de la suppression du document');
                    }
                },
                viewDocumentFromPath(docPath) {
                    const path = docPath.startsWith('images/') ? docPath : 'images/' + docPath;
                    this.viewingDocument = path;
                    this.modals.documentViewer = true;
                },
                async saveExpense() {
                    if (!this.canEdit) return;

                    if (this.isProjectLocked(this.expense.project_id)) {
                        alert('Ce projet est verrouillé. Impossible d\'enregistrer la dépense.');
                        return;
                    }

                    if (
                        !this.expense.project_id ||
                        !this.expense.project_budget_line_id ||
                        !this.expense.amount ||
                        !this.expense.expense_date
                    ) {
                        alert('Veuillez remplir tous les champs obligatoires');
                        return;
                    }

                    // Vérifier le nombre de fichiers
                    if (this.getTotalFilesCount() > 15) {
                        alert('Vous ne pouvez pas ajouter plus de 15 fichiers');
                        return;
                    }

                    // Vérifier si le montant dépasse le budget restant
                    if (this.selectedLine) {
                        const amountToAdd = Number(this.expense.amount);
                        const allocated = Number(this.selectedLine.allocated_amount) || 0;

                        // Si en mode édition, soustraire l'ancien montant
                        let oldAmount = 0;
                        if (this.isEditMode) {
                            const oldExpense = this.expenses.find(e => e.id === this.expense.id);
                            if (oldExpense) {
                                oldAmount = Number(oldExpense.amount) || 0;
                            }
                        }

                        const spent = Number(this.selectedLine.spent) || 0;
                        const newSpent = spent - oldAmount + amountToAdd;
                        const newRemaining = allocated - newSpent;

                        // Si modification avec dépassement
                        if (this.isEditMode && newRemaining < 0) {
                            const oldExpense = this.expenses.find(e => e.id === this.expense.id);
                            const oldRemaining = allocated - oldExpense.spent;

                            if (oldRemaining >= 0) {
                                const errorMsg = 'Impossible de modifier cette dépense au montant de ' +
                                    this.formatCurrency(amountToAdd) + '.\n\n' +
                                    'Raison: Le budget restant est de ' + this.formatCurrency(oldRemaining) +
                                    ' et vous essayez de dépenser ' + this.formatCurrency(amountToAdd) +
                                    ', ce qui dépasserait de ' + this.formatCurrency(Math.abs(newRemaining)) + '.';
                                alert(errorMsg);
                                return;
                            }
                        }

                        // Si création avec dépassement
                        if (!this.isEditMode && newRemaining < 0) {
                            this.modals.validation = true;
                            return;
                        }
                    }

                    await this.saveExpenseDirectly();
                },
                async saveExpenseDirectly() {
                    this.isSaving = true;

                    try {
                        const formData = new FormData();

                        formData.append('project_id', Number(this.expense.project_id));
                        formData.append('project_budget_line_id', Number(this.expense.project_budget_line_id));
                        formData.append('amount', Number(this.expense.amount));
                        formData.append('paid_amount', this.expense.paid_amount !== null && this.expense.paid_amount !== '' ? Number(this.expense.paid_amount) : '');
                        formData.append('supplier_id', this.expense.supplier_id ?? '');
                        formData.append('expense_date', this.expense.expense_date);
                        formData.append('description', this.expense.description || '');

                        // Ajouter les fichiers multiples
                        if (this.selectedFiles.length > 0) {
                            this.selectedFiles.forEach((file, index) => {
                                formData.append(`documents[]`, file);
                            });
                        }

                        let route = `${API_BASE_URL}?action=createExpense`;
                        if (this.isEditMode && this.expense.id) {
                            route = `${API_BASE_URL}?action=updateExpense&id=${this.expense.id}`;
                        }

                        console.log('===== SAVE EXPENSE =====');
                        console.log('[v0] Route:', route);
                        console.log('[v0] Payload (FormData):');
                        for (let [key, value] of formData.entries()) {
                            console.log('[v0]', key, value);
                        }

                        const response = await fetch(route, {
                            method: 'POST',
                            body: formData
                        });

                        console.log('[v0] HTTP Status:', response.status);

                        const data = await response.json();

                        console.log('[v0] Server Response:', data);

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
                        console.error('[v0] Network / JS Error:', error);
                        alert('Erreur lors de l\'enregistrement de la dépense');

                    } finally {
                        this.isSaving = false;
                    }
                },
                async submitForValidation() {
                    this.isSaving = true;

                    try {
                        const formData = new FormData();

                        formData.append('project_id', Number(this.expense.project_id));
                        formData.append('project_budget_line_id', Number(this.expense.project_budget_line_id));
                        formData.append('amount', Number(this.expense.amount));
                        formData.append('paid_amount', this.expense.paid_amount !== null && this.expense.paid_amount !== '' ? Number(this.expense.paid_amount) : '');
                        formData.append('supplier_id', this.expense.supplier_id ?? '');
                        formData.append('expense_date', this.expense.expense_date);
                        formData.append('description', this.expense.description || '');

                        // Ajouter les fichiers multiples
                        if (this.selectedFiles.length > 0) {
                            this.selectedFiles.forEach((file, index) => {
                                formData.append(`documents[]`, file);
                            });
                        }

                        console.log('===== SUBMIT FOR VALIDATION =====');
                        console.log('[v0] Route:', `${API_BASE_URL}?action=newExpenseValidation`);
                        console.log('[v0] Payload (FormData):');
                        for (let [key, value] of formData.entries()) {
                            console.log('[v0]', key, value);
                        }

                        const response = await fetch(`${API_BASE_URL}?action=newExpenseValidation`, {
                            method: 'POST',
                            body: formData
                        });

                        console.log('[v0] HTTP Status:', response.status);

                        const data = await response.json();

                        console.log('[v0] Server Response:', data);

                        if (!data.success) {
                            alert(data.message || 'Erreur lors de la soumission');
                            return;
                        }

                        alert(data.message || 'Demande de validation envoyée avec succès à l\'administrateur');

                        this.closeValidationModal();
                        this.closeExpenseModal();
                        this.fetchExpenses();
                        this.fetchExpensesValidations();

                    } catch (error) {
                        console.error('[v0] Network / JS Error:', error);
                        alert('Erreur lors de la soumission de la demande');
                    } finally {
                        this.isSaving = false;
                    }
                },
                closeValidationModal() {
                    this.modals.validation = false;
                },
                openExpenseModal() {
                    if (!this.canEdit) return;

                    const unlockedProjects = this.projects.filter(p => p.project_status !== 'Verrouillé');
                    if (unlockedProjects.length === 0) {
                        alert('Tous les projets sont verrouillés. Impossible d\'ajouter une dépense.');
                        return;
                    }

                    this.isEditMode = false;
                    this.expense = {
                        id: null,
                        project_id: '',
                        project_budget_line_id: '',
                        amount: '',
                        paid_amount: null,
                        supplier_id: null,
                        supplier_name: '',
                        expense_date: new Date().toISOString().split('T')[0],
                        description: '',
                        document: null,
                        documents: []
                    };
                    this.selectedFile = null;
                    this.selectedFiles = [];
                    this.selectedLine = null;
                    this.lines = [];
                    this.supplierSearch = '';
                    this.showSupplierDropdown = false;
                    this.supplierHighlight = 0;
                    this.supplierSelectVal = '';
                    this.supplierSuccessMsg = '';
                    this.modals.expense = true;
                },
                async editExpense(expense) {
                    if (!this.canEditExpense(expense)) {
                        alert('Vous ne pouvez modifier que les dépenses que vous avez insérées.');
                        return;
                    }
                    if (this.isProjectLocked(expense.project_id)) {
                        alert('Ce projet est verrouillé. Impossible de modifier cette dépense.');
                        return;
                    }

                    this.isEditMode = true;

                    // Parser les documents JSON s'ils existent
                    let documents = [];
                    if (expense.documents) {
                        try {
                            documents = typeof expense.documents === 'string' ?
                                JSON.parse(expense.documents) :
                                expense.documents;
                        } catch (e) {
                            console.error('[v0] Error parsing documents:', e);
                            documents = [];
                        }
                    }

                    this.expense = {
                        id: expense.id,
                        project_id: expense.project_id,
                        project_budget_line_id: expense.project_budget_line_id,
                        amount: expense.amount,
                        paid_amount: expense.paid_amount ?? null,
                        supplier_id: expense.supplier_id ?? null,
                        supplier_name: expense.supplier_name ?? '',
                        expense_date: expense.expense_date,
                        description: expense.description,
                        document: expense.document,
                        documents: documents
                    };
                    this.selectedFile = null;
                    this.selectedFiles = [];
                    this.supplierSearch = '';
                    this.showSupplierDropdown = false;
                    this.supplierHighlight = 0;
                    this.supplierSelectVal = '';
                    this.supplierSuccessMsg = '';

                    await this.fetchLines();

                    this.$nextTick(() => {
                        this.updateLineInfo();
                    });

                    this.modals.expense = true;
                },
                async deleteExpense(expense) {
                    if (!this.canEdit) return;
                    if (!this.canEditExpense(expense)) {
                        alert('Vous ne pouvez supprimer que les dépenses que vous avez insérées.');
                        return;
                    }
                    if (this.isProjectLocked(expense.project_id)) {
                        alert('Ce projet est verrouillé. Impossible de supprimer cette dépense.');
                        return;
                    }

                    if (!confirm(`Êtes-vous sûr de vouloir supprimer cette dépense de ${this.formatCurrency(expense.amount)} ?`)) {
                        return;
                    }

                    try {
                        const route = `${API_BASE_URL}?action=deleteExpense&id=${expense.id}`;
                        console.log('[v0] Route:', route);

                        const response = await fetch(route, {
                            method: 'DELETE'
                        });

                        const data = await response.json();
                        console.log('[v0] Server Response:', data);

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
                    this.selectedFiles = [];
                },
                viewDocument(expense) {
                    if (!expense.document) return;

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
                getValidationDocuments(validation) {
                    try {
                        const docs = typeof validation.documents === 'string' ?
                            JSON.parse(validation.documents) :
                            validation.documents || [];
                        return Array.isArray(docs) ? docs.filter(Boolean) : [];
                    } catch (e) {
                        return [];
                    }
                },
                getExpenseDocuments(expense) {
                    let docs = [];

                    if (expense.documents !== undefined && expense.documents !== null && expense.documents !== '') {
                        try {
                            let parsed = expense.documents;
                            // Si c'est une string JSON, on parse
                            if (typeof parsed === 'string') {
                                parsed = JSON.parse(parsed);
                            }
                            // Si c'est un tableau, on filtre les valeurs vides
                            if (Array.isArray(parsed)) {
                                docs = parsed.filter(d => d && d.trim && d.trim() !== '');
                            }
                        } catch (e) {
                            // Si le parse échoue et que c'est une string non-JSON (nom de fichier direct)
                            if (typeof expense.documents === 'string' && expense.documents.trim()) {
                                docs = [expense.documents.trim()];
                            }
                        }
                    }

                    // Champ legacy "document" (singulier)
                    if (expense.document && expense.document.trim && expense.document.trim() !== '' && !docs.includes(expense.document)) {
                        docs.unshift(expense.document.trim());
                    }

                    return docs;
                },
                viewExpenseDocuments(expense) {
                    try {
                        const docs = this.getExpenseDocuments(expense);
                        this.viewingExpenseDocuments = docs;
                        this.viewingExpenseDocumentsIndex = 0;
                        this.modals.expenseDocuments = true;
                    } catch (e) {
                        console.error('[v0] Error parsing documents:', e);
                        alert('Erreur lors du chargement des documents');
                    }
                },
                closeExpenseDocuments() {
                    this.modals.expenseDocuments = false;
                    this.viewingExpenseDocuments = null;
                    this.viewingExpenseDocumentsIndex = 0;
                },
                isProjectLocked(projectId) {
                    const project = this.projects.find(p => p.id == projectId);
                    return project && project.project_status === 'Verrouillé';
                },
                /** true si l'utilisateur peut modifier/supprimer cette dépense (admin peut tout faire, sinon créateur ou dépense sans user_id) */
                canEditExpense(expense) {
                    if (!this.canEdit || !expense) return false;
                    if (this.isProjectLocked(expense.project_id)) return false;
                    if (this.user_role === 'admin') return true;
                    const raw = expense.user_id != null ? expense.user_id : expense.created_by;
                    const hasCreator = raw !== null && raw !== undefined && raw !== '';
                    const creatorId = hasCreator ? Number(raw) : null;
                    const myId = Number(this.user_id);
                    if (!Number.isFinite(myId)) return creatorId === null;
                    return creatorId === null || creatorId === myId;
                },
                getProjectName(projectId) {
                    const project = this.projects.find(p => p.id == projectId);
                    return project ? project.name : '';
                },
                filterExpenses() {
                    let filtered = this.expenses;

                    if (this.searchQuery) {
                        filtered = filtered.filter(e =>
                            (e.description && e.description.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                            (e.project_name && e.project_name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                            (e.budget_line_name && e.budget_line_name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                            (e.user_name && e.user_name.toLowerCase().includes(this.searchQuery.toLowerCase()))
                        );
                    }

                    if (this.projectFilter) {
                        filtered = filtered.filter(e => e.project_id == this.projectFilter);
                    }

                    if (this.supplierFilter) {
                        filtered = filtered.filter(e => String(e.supplier_id) === String(this.supplierFilter));
                    }

                    if (this.departmentFilter) {
                        const projectIdsInDept = this.projects
                            .filter(p => p.department === this.departmentFilter)
                            .map(p => p.id);
                        filtered = filtered.filter(e => projectIdsInDept.includes(e.project_id));
                    }

                    if (this.locationFilter) {
                        const projectIdsInLoc = this.projects
                            .filter(p => p.location === this.locationFilter)
                            .map(p => p.id);
                        filtered = filtered.filter(e => projectIdsInLoc.includes(e.project_id));
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
                filterOverflowExpenses() {
                    // Filtrage admin
                    let filtered = this.adminValidations;

                    if (this.overflowSearchQuery) {
                        filtered = filtered.filter(v =>
                            (v.user_name && v.user_name.toLowerCase().includes(this.overflowSearchQuery.toLowerCase())) ||
                            (v.project_name && v.project_name.toLowerCase().includes(this.overflowSearchQuery.toLowerCase())) ||
                            (v.budget_line_name && v.budget_line_name.toLowerCase().includes(this.overflowSearchQuery.toLowerCase())) ||
                            (v.description && v.description.toLowerCase().includes(this.overflowSearchQuery.toLowerCase()))
                        );
                    }

                    if (this.overflowDepartmentFilter) {
                        const projectIdsInDept = this.projects
                            .filter(p => p.department === this.overflowDepartmentFilter)
                            .map(p => p.id);
                        filtered = filtered.filter(v => projectIdsInDept.includes(v.project_id));
                    }

                    if (this.overflowLocationFilter) {
                        const projectIdsInLoc = this.projects
                            .filter(p => p.location === this.overflowLocationFilter)
                            .map(p => p.id);
                        filtered = filtered.filter(v => projectIdsInLoc.includes(v.project_id));
                    }

                    this.filteredAdminValidations = filtered;
                    this.adminValidationsCurrentPage = 1;

                    // Filtrage utilisateur
                    let userFiltered = this.userValidations;

                    if (this.userOverflowSearchQuery) {
                        userFiltered = userFiltered.filter(v =>
                            (v.project_name && v.project_name.toLowerCase().includes(this.userOverflowSearchQuery.toLowerCase())) ||
                            (v.budget_line_name && v.budget_line_name.toLowerCase().includes(this.userOverflowSearchQuery.toLowerCase())) ||
                            (v.description && v.description.toLowerCase().includes(this.userOverflowSearchQuery.toLowerCase()))
                        );
                    }

                    this.filteredUserValidations = userFiltered;
                    this.userValidationsCurrentPage = 1;
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
                getVisiblePages(current, total) {
                    const pages = [];
                    if (total <= 1) return pages;
                    // Always first
                    pages.push(1);
                    // Ellipsis before window
                    if (current - 1 > 2) pages.push('...');
                    // Window: prev, current, next
                    for (let p = Math.max(2, current - 1); p <= Math.min(total - 1, current + 1); p++) {
                        pages.push(p);
                    }
                    // Ellipsis after window
                    if (current + 1 < total - 1) pages.push('...');
                    // Always last
                    if (total > 1) pages.push(total);
                    return pages;
                },
                handleGoToPage(pageStr, total, setter) {
                    const n = parseInt(pageStr);
                    if (!isNaN(n) && n >= 1 && n <= total) setter(n);
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
                formatCurrencyExact(value) {
                    const num = parseFloat(value) || 0;
                    // Détecter si la valeur a des décimales significatives
                    const hasDecimals = num !== Math.floor(num);
                    return num.toLocaleString('fr-FR', {
                        style: 'currency',
                        currency: 'XOF',
                        minimumFractionDigits: hasDecimals ? 2 : 0,
                        maximumFractionDigits: hasDecimals ? 2 : 0,
                        useGrouping: true
                    });
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
                // ── Supplier methods ──────────────────────────────
                async fetchSuppliers() {
                    try {
                        const res = await fetch(`${API_BASE_URL}?action=getSuppliers`);
                        const data = await res.json();
                        this.suppliers = data.data || [];
                    } catch (e) {
                        console.error('[v0] fetchSuppliers error:', e);
                    }
                },
                onSupplierInput() {
                    this.showSupplierDropdown = true;
                    this.supplierHighlight = 0;
                },
                closeSupplierDropdown() {
                    this.showSupplierDropdown = false;
                },
                highlightNext() {
                    const max = this.filteredSuppliers.length + (this.canAddNewSupplier ? 1 : 0) - 1;
                    this.supplierHighlight = Math.min(this.supplierHighlight + 1, max);
                },
                highlightPrev() {
                    this.supplierHighlight = Math.max(this.supplierHighlight - 1, 0);
                },
                pickHighlighted() {
                    if (this.supplierHighlight < this.filteredSuppliers.length) {
                        this.pickSupplier(this.filteredSuppliers[this.supplierHighlight]);
                    } else if (this.canAddNewSupplier) {
                        this.createAndPickSupplier();
                    }
                },
                pickSupplier(s) {
                    this.expense.supplier_id = s.id;
                    this.expense.supplier_name = s.name;
                    this.supplierSearch = '';
                    this.showSupplierDropdown = false;
                },
                clearSupplier() {
                    this.expense.supplier_id = null;
                    this.expense.supplier_name = '';
                    this.supplierSearch = '';
                },
                onSupplierSelectChange(e) {
                    const id = this.supplierSelectVal;
                    if (!id) return;
                    const found = this.suppliers.find(s => String(s.id) === String(id));
                    if (found) this.pickSupplier(found);
                    this.supplierSelectVal = '';
                },
                async createAndPickSupplier() {
                    const name = this.supplierSearch.trim();
                    if (!name) return;
                    try {
                        const fd = new FormData();
                        fd.append('name', name);
                        const res = await fetch(`${API_BASE_URL}?action=createSupplier`, {
                            method: 'POST',
                            body: fd
                        });
                        const data = await res.json();
                        if (!data.success) {
                            alert(data.message || 'Erreur création fournisseur');
                            return;
                        }
                        this.suppliers.push(data.data);
                        this.pickSupplier(data.data);
                        // Toast succès
                        this.supplierSuccessMsg = `✓ Fournisseur "${data.data.name}" enregistré avec succès !`;
                        setTimeout(() => {
                            this.supplierSuccessMsg = '';
                        }, 4000);
                    } catch (e) {
                        console.error('[v0] createAndPickSupplier:', e);
                        alert('Erreur lors de la création du fournisseur');
                    }
                },
                // ────────────────────────────────────────────────────
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

                                if (!document.body.contains(projectsCanvas) || !document.body.contains(evolutionCanvas)) {
                                    console.error('[v0] Les canvas ne sont pas dans le DOM');
                                    this.isRenderingCharts = false;
                                    return;
                                }

                                const colors = ['#0070f3', '#00d4ff', '#00e676', '#ffb800', '#7c3aed', '#ff3b3b', '#ff6b9d', '#10b981'];

                                // Graphique par projet
                                const projectsData = {};
                                this.expenses.forEach(e => {
                                    const projectName = e.project_name || 'Inconnu';
                                    projectsData[projectName] = (projectsData[projectName] || 0) + parseFloat(e.amount || 0);
                                });

                                this.projectsChart = new Chart(projectsCanvas, {
                                    type: 'doughnut',
                                    data: {
                                        labels: Object.keys(projectsData),
                                        datasets: [{
                                            data: Object.values(projectsData),
                                            backgroundColor: colors.slice(0, Object.keys(projectsData).length),
                                            borderColor: '#111111',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    color: '#ededed',
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });

                                // Graphique évolution
                                const dailyData = {};
                                this.expenses.forEach(e => {
                                    const date = new Date(e.expense_date).toLocaleDateString('fr-FR');
                                    dailyData[date] = (dailyData[date] || 0) + parseFloat(e.amount || 0);
                                });

                                const sortedDates = Object.keys(dailyData).sort((a, b) => new Date(a) - new Date(b));
                                const cumulativeAmounts = [];
                                let sum = 0;
                                sortedDates.forEach(date => {
                                    sum += dailyData[date];
                                    cumulativeAmounts.push(sum);
                                });

                                this.evolutionChart = new Chart(evolutionCanvas, {
                                    type: 'line',
                                    data: {
                                        labels: sortedDates,
                                        datasets: [{
                                            label: 'Cumul des Dépenses',
                                            data: cumulativeAmounts,
                                            borderColor: '#0070f3',
                                            backgroundColor: 'rgba(0, 112, 243, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.4,
                                            pointBackgroundColor: '#00d4ff',
                                            pointBorderColor: '#0070f3',
                                            pointRadius: 5
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                labels: {
                                                    color: '#ededed',
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
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

                                console.log('[v0] Charts rendered successfully');
                            } catch (error) {
                                console.error('[v0] Error rendering charts:', error);
                            } finally {
                                this.isRenderingCharts = false;
                            }
                        });
                    }, 100);
                }
            }
            // Directive v-click-outside pour fermer le dropdown fournisseur
        }).directive('click-outside', {
            mounted(el, binding) {
                el._clickOutsideHandler = (event) => {
                    if (!el.contains(event.target)) binding.value();
                };
                document.addEventListener('mousedown', el._clickOutsideHandler);
            },
            unmounted(el) {
                document.removeEventListener('mousedown', el._clickOutsideHandler);
            }
        }).mount('#app');
    </script>
</body>

</html>