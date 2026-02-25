<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Dompdf\Dompdf;
use Dompdf\Options;

include 'db.php';

// ============================================================
// DISPATCH : si format demandé on exporte, sinon on affiche le wizard
// ============================================================
$format = $_GET['format'] ?? null;

if ($format === 'excel') {
    exportExcel();
    exit;
}
if ($format === 'pdf') {
    exportPDF();
    exit;
}

// Charger la liste des projets pour le wizard
$pdo      = getPDO();
$allProjects = $pdo->query("SELECT id, name, department, location FROM projects ORDER BY date_of_creation DESC")->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// PAGE HTML – Wizard 3 étapes
// ============================================================
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Export – État des Chantiers | OrizonPlus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0a0a0a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(28px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes stepIn {
            from {
                opacity: 0;
                transform: translateX(24px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ── Overlay ── */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn .2s ease;
        }

        /* ── Modal ── */
        .modal {
            background: #111111;
            border: 1px solid #2a2a2a;
            border-radius: 18px;
            width: 560px;
            max-width: 95vw;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 24px 80px rgba(0, 0, 0, .6);
            animation: slideUp .32s cubic-bezier(.22, .68, 0, 1.15);
            position: relative;
        }

        /* Bande dégradée en haut */
        .modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0070f3 0%, #00d4ff 100%);
            border-radius: 18px 18px 0 0;
        }

        .modal-inner {
            padding: 40px 44px 36px;
            text-align: center;
        }

        /* ── Logo OrizonPlus ── */
        .modal-logo {
            height: 60px;
            margin-bottom: 16px;
            object-fit: contain;
        }

        .modal-logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0070f3 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
            display: block;
        }

        .modal h2 {
            font-size: 1.15rem;
            color: #ededed;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .modal .subtitle {
            font-size: .83rem;
            color: #606060;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* ── Indicateur d'étapes ── */
        .steps-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }

        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #1a1a1a;
            border: 2px solid #2a2a2a;
            color: #606060;
            font-size: .75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .25s;
            position: relative;
            z-index: 1;
        }

        .step-dot.active {
            background: #0070f3;
            border-color: #0070f3;
            color: #fff;
        }

        .step-dot.done {
            background: #00e676;
            border-color: #00e676;
            color: #111;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: #2a2a2a;
            max-width: 60px;
            transition: background .25s;
        }

        .step-line.done {
            background: #00e676;
        }

        /* ── Steps ── */
        .step {
            display: none;
            animation: stepIn .22s ease;
        }

        .step.active {
            display: block;
        }

        /* ── Boutons de choix format ── */
        .choices {
            display: flex;
            gap: 14px;
            justify-content: center;
        }

        .choice-btn {
            flex: 1;
            border: 2px solid #2a2a2a;
            border-radius: 14px;
            padding: 22px 14px 18px;
            cursor: pointer;
            background: #1a1a1a;
            transition: transform .18s, box-shadow .18s, border-color .18s, background .18s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .choice-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 112, 243, .2);
        }

        .choice-btn.selected {
            border-color: #0070f3;
            background: rgba(0, 112, 243, .1);
        }

        .choice-btn.excel .btn-label {
            color: #0070f3;
        }

        .choice-btn.pdf .btn-label {
            color: #00d4ff;
        }

        .choice-btn .btn-icon {
            width: 46px;
            height: 46px;
        }

        .choice-btn .btn-label {
            font-weight: 700;
            font-size: .95rem;
            color: #ededed;
        }

        .choice-btn .btn-sub {
            font-size: .72rem;
            color: #606060;
        }

        /* ── Choix périmètre ── */
        .scope-choices {
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
        }

        .scope-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border: 2px solid #2a2a2a;
            border-radius: 12px;
            background: #1a1a1a;
            cursor: pointer;
            transition: border-color .18s, background .18s;
            color: #ededed;
        }

        .scope-btn:hover {
            border-color: #0070f3;
            background: rgba(0, 112, 243, .07);
        }

        .scope-btn.selected {
            border-color: #0070f3;
            background: rgba(0, 112, 243, .12);
        }

        .scope-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(0, 112, 243, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .scope-btn .scope-title {
            font-weight: 700;
            font-size: .92rem;
            margin-bottom: 2px;
        }

        .scope-btn .scope-sub {
            font-size: .76rem;
            color: #606060;
        }

        /* ── Liste projets ── */
        .search-box {
            width: 100%;
            padding: .6rem 1rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            color: #ededed;
            font-size: .85rem;
            margin-bottom: 10px;
            outline: none;
            transition: border-color .2s;
        }

        .search-box:focus {
            border-color: #0070f3;
        }

        .projects-list {
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            background: #0f0f0f;
        }

        .projects-list::-webkit-scrollbar {
            width: 5px;
        }

        .projects-list::-webkit-scrollbar-track {
            background: #0f0f0f;
        }

        .projects-list::-webkit-scrollbar-thumb {
            background: #2a2a2a;
            border-radius: 3px;
        }

        .project-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-bottom: 1px solid #1a1a1a;
            cursor: pointer;
            transition: background .15s;
            text-align: left;
        }

        .project-item:last-child {
            border-bottom: none;
        }

        .project-item:hover {
            background: #1a1a1a;
        }

        .project-item.selected {
            background: rgba(0, 112, 243, .1);
        }

        .project-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #2a2a2a;
            border-radius: 5px;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .15s;
        }

        .project-item.selected .project-checkbox {
            background: #0070f3;
            border-color: #0070f3;
        }

        .project-checkbox svg {
            display: none;
        }

        .project-item.selected .project-checkbox svg {
            display: block;
        }

        .project-info .project-name {
            font-size: .875rem;
            font-weight: 600;
            color: #ededed;
        }

        .project-info .project-meta {
            font-size: .72rem;
            color: #606060;
            margin-top: 2px;
        }

        /* mode "un seul" : radio style */
        .project-item.radio-mode .project-checkbox {
            border-radius: 50%;
        }

        .project-item.radio-mode.selected .project-checkbox {
            background: #0070f3;
            border-color: #0070f3;
        }

        .selection-count {
            font-size: .78rem;
            color: #606060;
            margin-top: 8px;
            text-align: left;
        }

        .selection-count span {
            color: #0070f3;
            font-weight: 700;
        }

        /* ── Boutons navigation ── */
        .btn-row {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            justify-content: flex-end;
        }

        .btn {
            padding: .65rem 1.4rem;
            border-radius: 10px;
            border: none;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-ghost {
            background: #1a1a1a;
            color: #a0a0a0;
            border: 1px solid #2a2a2a;
        }

        .btn-ghost:hover {
            background: #222;
            color: #ededed;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0070f3, #0060df);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 112, 243, .35);
        }

        .btn-primary:disabled {
            opacity: .4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-generate {
            background: linear-gradient(135deg, #00e676, #00c85a);
            color: #111;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 230, 118, .35);
        }

        .btn-generate:disabled {
            opacity: .4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ── Résumé ── */
        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            margin: 0 4px 8px;
        }

        .pill-format {
            background: rgba(0, 112, 243, .2);
            color: #0070f3;
        }

        .pill-scope {
            background: rgba(0, 230, 118, .2);
            color: #00e676;
        }

        .pill-count {
            background: rgba(0, 212, 255, .2);
            color: #00d4ff;
        }

        /* ── Cancel ── */
        .cancel-wrap {
            margin-top: 18px;
            text-align: center;
        }

        .cancel {
            font-size: .79rem;
            color: #404040;
            cursor: pointer;
            background: none;
            border: none;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color .15s;
        }

        .cancel:hover {
            color: #a0a0a0;
        }
    </style>
</head>

<body>

    <div class="overlay">
        <div class="modal">
            <div class="modal-inner">

                <!-- Logo OrizonPlus -->
                <img src="logo.png" alt="OrizonPlus" class="modal-logo"
                    onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block'" />
                <span id="logo-text" class="modal-logo-text" style="display:none;"> <i class="fas fa-chart-line"></i> OrizonPlus</span>

                <!-- Indicateur étapes -->
                <div class="steps-indicator">
                    <div class="step-dot active" id="dot1">1</div>
                    <div class="step-line" id="line1"></div>
                    <div class="step-dot" id="dot2">2</div>
                    <div class="step-line" id="line2"></div>
                    <div class="step-dot" id="dot3">3</div>
                </div>

                <!-- ══════════════════════════════
         ÉTAPE 1 – Choix du format
    ══════════════════════════════ -->
                <div class="step active" id="step1">
                    <h2>Format d'export</h2>
                    <p class="subtitle">Choisissez le format dans lequel<br>vous souhaitez générer le document.</p>

                    <div class="choices">
                        <button class="choice-btn excel" onclick="selectFormat('excel', this)">
                            <svg class="btn-icon" viewBox="0 0 52 52" fill="none">
                                <rect width="52" height="52" rx="10" fill="#0070f3" fill-opacity=".1" />
                                <path d="M30 10H18a3 3 0 0 0-3 3v26a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3V20L30 10Z"
                                    fill="#0070f3" fill-opacity=".2" stroke="#0070f3" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M30 10v10h10" stroke="#0070f3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 28l3.5-4.5L21 19M31 19l-3.5 4.5L31 28" stroke="#0070f3" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                <line x1="24.5" y1="23.5" x2="27.5" y2="23.5" stroke="#0070f3" stroke-width="1.4" stroke-linecap="round" />
                                <rect x="16" y="32" width="20" height="7" rx="2" fill="#0070f3" fill-opacity=".15" />
                                <text x="26" y="38" text-anchor="middle" font-size="5.5" font-weight="bold" fill="#0070f3" font-family="sans-serif">XLSX</text>
                            </svg>
                            <span class="btn-label">Excel</span>
                            <span class="btn-sub">.xlsx</span>
                        </button>
                        <button class="choice-btn pdf" onclick="selectFormat('pdf', this)">
                            <svg class="btn-icon" viewBox="0 0 52 52" fill="none">
                                <rect width="52" height="52" rx="10" fill="#00d4ff" fill-opacity=".1" />
                                <path d="M30 10H18a3 3 0 0 0-3 3v26a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3V20L30 10Z"
                                    fill="#00d4ff" fill-opacity=".2" stroke="#00d4ff" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M30 10v10h10" stroke="#00d4ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <line x1="20" y1="24" x2="32" y2="24" stroke="#00d4ff" stroke-width="1.4" stroke-linecap="round" />
                                <line x1="20" y1="27.5" x2="28" y2="27.5" stroke="#00d4ff" stroke-width="1.4" stroke-linecap="round" />
                                <rect x="16" y="32" width="20" height="7" rx="2" fill="#00d4ff" fill-opacity=".15" />
                                <text x="26" y="38" text-anchor="middle" font-size="5.5" font-weight="bold" fill="#00d4ff" font-family="sans-serif">PDF</text>
                            </svg>
                            <span class="btn-label">PDF</span>
                            <span class="btn-sub">.pdf</span>
                        </button>
                    </div>

                    <div class="btn-row">
                        <button type="button" class="btn btn-ghost" id="btn-cancel">
                            Annuler
                        </button>

                        <button type="button" class="btn btn-primary" id="btn-step1-next" disabled>
                            Suivant →
                        </button>
                    </div>

                    <script>
                        document.getElementById('btn-cancel').addEventListener('click', function() {
                            window.location.replace('../index.php');
                        });

                        document.getElementById('btn-step1-next').addEventListener('click', function() {
                            goStep(2);
                        });
                    </script>
                </div>

                <!-- ══════════════════════════════
         ÉTAPE 2 – Périmètre
    ══════════════════════════════ -->
                <div class="step" id="step2">
                    <h2>Périmètre du document</h2>
                    <p class="subtitle">Quels projets souhaitez-vous inclure<br>dans l'export ?</p>

                    <div class="scope-choices">
                        <button class="scope-btn" onclick="selectScope('all', this)">
                            <div class="scope-icon">📋</div>
                            <div>
                                <div class="scope-title">Tous les projets</div>
                                <div class="scope-sub">Inclure l'ensemble des <?= count($allProjects) ?> projets</div>
                            </div>
                        </button>
                        <button class="scope-btn" onclick="selectScope('multiple', this)">
                            <div class="scope-icon">☑️</div>
                            <div>
                                <div class="scope-title">Plusieurs projets</div>
                                <div class="scope-sub">Sélectionner manuellement plusieurs projets</div>
                            </div>
                        </button>
                        <button class="scope-btn" onclick="selectScope('single', this)">
                            <div class="scope-icon">🎯</div>
                            <div>
                                <div class="scope-title">Un seul projet</div>
                                <div class="scope-sub">Exporter uniquement un projet spécifique</div>
                            </div>
                        </button>
                    </div>

                    <div class="btn-row">
                        <button class="btn btn-ghost" onclick="goStep(1)">← Retour</button>
                        <button class="btn btn-primary" id="btn-step2-next" disabled onclick="goStep(3)">Suivant →</button>
                    </div>
                </div>

                <!-- ══════════════════════════════
         ÉTAPE 3 – Sélection / Génération
    ══════════════════════════════ -->
                <div class="step" id="step3">
                    <h2 id="step3-title">Sélectionner les projets</h2>
                    <p class="subtitle" id="step3-sub">Cochez les projets à inclure dans l'export.</p>

                    <!-- Zone de sélection (masquée si scope=all) -->
                    <div id="selection-zone">
                        <input type="text" class="search-box" placeholder="🔍 Rechercher un projet…" oninput="filterProjects(this.value)" />
                        <div class="projects-list" id="projects-list">
                            <?php foreach ($allProjects as $p): ?>
                                <div class="project-item"
                                    data-id="<?= $p['id'] ?>"
                                    data-name="<?= htmlspecialchars($p['name']) ?>"
                                    onclick="toggleProject(this)">
                                    <div class="project-checkbox">
                                        <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
                                            <path d="M1 4l3 3 5-6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div class="project-info">
                                        <div class="project-name"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="project-meta">
                                            <?= htmlspecialchars($p['department'] ?? '') ?>
                                            <?= ($p['department'] && $p['location']) ? ' · ' : '' ?>
                                            <?= htmlspecialchars($p['location'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="selection-count" id="selection-count">0 projet sélectionné</div>
                    </div>

                    <!-- Résumé (scope=all) -->
                    <div id="all-summary" style="display:none; margin-bottom:8px;">
                        <p style="color:#a0a0a0; font-size:.85rem; margin-bottom:12px;">
                            Le document sera généré avec <strong style="color:#ededed;"><?= count($allProjects) ?> projets</strong>.
                        </p>
                    </div>

                    <!-- Pills résumé -->
                    <div style="margin-top:16px; margin-bottom:4px;">
                        <span class="summary-pill pill-format" id="pill-format">—</span>
                        <span class="summary-pill pill-scope" id="pill-scope">—</span>
                        <span class="summary-pill pill-count" id="pill-count" style="display:none;">—</span>
                    </div>

                    <div class="btn-row">
                        <button class="btn btn-ghost" onclick="goStep(2)">← Retour</button>
                        <button class="btn btn-generate" id="btn-generate" disabled onclick="generate()">⬇ Générer</button>
                    </div>
                </div>

            </div><!-- /modal-inner -->
        </div><!-- /modal -->
    </div><!-- /overlay -->

    <script>
        let selectedFormat = null;
        let selectedScope = null;
        let selectedIds = new Set();

        // ── Format ──
        function selectFormat(fmt, el) {
            selectedFormat = fmt;
            document.querySelectorAll('.choice-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('btn-step1-next').disabled = false;
        }

        // ── Périmètre ──
        function selectScope(scope, el) {
            selectedScope = scope;
            document.querySelectorAll('.scope-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('btn-step2-next').disabled = false;
        }

        // ── Navigation étapes ──
        function goStep(n) {
            [1, 2, 3].forEach(i => {
                document.getElementById('step' + i).classList.toggle('active', i === n);
                const dot = document.getElementById('dot' + i);
                dot.classList.remove('active', 'done');
                if (i < n) dot.classList.add('done');
                if (i === n) dot.classList.add('active');
            });
            [1, 2].forEach(i => {
                const line = document.getElementById('line' + i);
                line.classList.toggle('done', i < n);
            });
            if (n === 3) prepareStep3();
        }

        // ── Prépare l'étape 3 ──
        function prepareStep3() {
            const isAll = selectedScope === 'all';
            const isSingle = selectedScope === 'single';

            // Radio mode pour "un seul"
            document.querySelectorAll('.project-item').forEach(el => {
                el.classList.toggle('radio-mode', isSingle);
            });

            document.getElementById('selection-zone').style.display = isAll ? 'none' : 'block';
            document.getElementById('all-summary').style.display = isAll ? 'block' : 'none';

            const titles = {
                all: 'Prêt à générer',
                multiple: 'Sélectionner plusieurs projets',
                single: 'Sélectionner un projet'
            };
            const subs = {
                all: 'Tous les projets seront inclus dans le document.',
                multiple: 'Cochez les projets à inclure dans l\'export.',
                single: 'Cliquez sur le projet à exporter.'
            };
            document.getElementById('step3-title').textContent = titles[selectedScope];
            document.getElementById('step3-sub').textContent = subs[selectedScope];

            // Pills résumé
            document.getElementById('pill-format').textContent = '📄 ' + selectedFormat.toUpperCase();
            const scopeLabels = {
                all: '📋 Tous les projets',
                multiple: '☑️ Sélection multiple',
                single: '🎯 Un seul projet'
            };
            document.getElementById('pill-scope').textContent = scopeLabels[selectedScope];

            selectedIds.clear();
            updateGenerateBtn();
        }

        // ── Toggle projet ──
        function toggleProject(el) {
            const id = el.dataset.id;
            if (selectedScope === 'single') {
                // Radio : déselectionner tous les autres
                document.querySelectorAll('.project-item').forEach(p => p.classList.remove('selected'));
                selectedIds.clear();
                el.classList.add('selected');
                selectedIds.add(id);
            } else {
                if (el.classList.contains('selected')) {
                    el.classList.remove('selected');
                    selectedIds.delete(id);
                } else {
                    el.classList.add('selected');
                    selectedIds.add(id);
                }
            }
            updateGenerateBtn();
        }

        // ── Filtre recherche ──
        function filterProjects(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.project-item').forEach(el => {
                el.style.display = el.dataset.name.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        // ── Met à jour le bouton Générer ──
        function updateGenerateBtn() {
            const btn = document.getElementById('btn-generate');
            const countEl = document.getElementById('selection-count');
            const pillCount = document.getElementById('pill-count');

            if (selectedScope === 'all') {
                btn.disabled = false;
                pillCount.style.display = 'none';
            } else {
                const n = selectedIds.size;
                const label = n <= 1 ? n + ' projet sélectionné' : n + ' projets sélectionnés';
                countEl.innerHTML = '<span>' + n + '</span> ' + (n <= 1 ? 'projet sélectionné' : 'projets sélectionnés');
                btn.disabled = n === 0;
                if (n > 0) {
                    pillCount.style.display = 'inline-flex';
                    pillCount.textContent = '🗂 ' + n + (n === 1 ? ' projet' : ' projets');
                } else {
                    pillCount.style.display = 'none';
                }
            }
        }

        // ── Génération ──
        function generate() {
            let url = '?format=' + selectedFormat;
            if (selectedScope !== 'all') {
                url += '&ids=' + Array.from(selectedIds).join(',');
            }
            window.location.href = url;
        }
    </script>

</body>

</html>
<?php

// ============================================================
// EXPORT EXCEL
// ============================================================
function dateFr(string $format): string
{
    $mois  = [
        'January' => 'janvier',
        'February' => 'février',
        'March' => 'mars',
        'April' => 'avril',
        'May' => 'mai',
        'June' => 'juin',
        'July' => 'juillet',
        'August' => 'août',
        'September' => 'septembre',
        'October' => 'octobre',
        'November' => 'novembre',
        'December' => 'décembre'
    ];
    $jours = [
        'Monday' => 'lundi',
        'Tuesday' => 'mardi',
        'Wednesday' => 'mercredi',
        'Thursday' => 'jeudi',
        'Friday' => 'vendredi',
        'Saturday' => 'samedi',
        'Sunday' => 'dimanche'
    ];
    return strtr(date($format), array_merge($mois, $jours));
}

function exportExcel(): void
{
    try {
        $pdo         = getPDO();
        $projects    = fetchProjects($pdo);
        $expenseMap  = fetchExpensesByProject($pdo);   // ← données agrégées fournisseurs

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Etat des Chantiers');

        $navyDark   = '1E2A5E';
        $navyHeader = '2E3A8C';
        $navyAlt    = 'F0F2FA';
        $white      = 'FFFFFF';
        $yellow     = 'FFD966';   // jaune highlight colonnes engagement fournisseurs
        $numFormat  = '#,##0';

        // Ligne 1 – Logo + date
        $logoPath = dirname(__DIR__) . '/images/logo_kamus.png';
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('KAM US')->setDescription('Logo KAM US')
                ->setPath($logoPath)->setHeight(55)
                ->setCoordinates('A1')->setOffsetX(4)->setOffsetY(2)
                ->setWorksheet($sheet);
        } else {
            $sheet->setCellValue('A1', 'KAM US');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF' . $navyDark]],
            ]);
        }
        $sheet->setCellValue('O1', dateFr('d F Y'));
        $sheet->getStyle('O1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $navyDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(55);

        // Ligne 2 – Titre principal (colonnes A→O) + bandeau jaune ENGAGEMENTS (P→R)
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'ETAT DES CHANTIERS ENCOURS');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF' . $white]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $navyDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells('P2:R2');
        $sheet->setCellValue('P2', 'ENGAGEMENTS AUPRES DES FOURNISSEURS / PRESTATAIRES');
        $sheet->getStyle('P2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF1A1A2E']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $yellow]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // Ligne 3 – En-têtes
        $headers = [
            'A' => 'N°',
            'B' => "BON DE COMMANDE /CONTRAT",
            'C' => 'SECTION',
            'D' => 'OBJET',
            'E' => 'CHANTIER',
            'F' => 'DATE DE CONTRAT',
            'G' => 'MONTANT HT DU MARCHE',
            'H' => "BUDGET D'EXECUTION HT",
            'I' => 'ENCAISSEMENT HT',
            'J' => 'REALISATION',
            'K' => "% DE DECAISSEMENT PAR RAPPORT AU BUDGET D'EXECUTION",
            'L' => "TAUX D'EXECUTION PHYSIQUE",
            'M' => 'PART DU MARCHE NON EXECUTEE',
            'N' => 'RESTE A ENCAISSER HT',
            'O' => 'OBSERVATION',
            'P' => 'MONTANT TOTAL PAIEMENT EFFECTUE',
            'Q' => 'PAIEMENT EFFECTUE',
            'R' => 'RESTE A PAYER',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '3', $label);
            // Colonnes jaunes P Q R
            $isYellow = in_array($col, ['P', 'Q', 'R']);
            $sheet->getStyle($col . '3')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $isYellow ? 'FF1A1A2E' : 'FF' . $white]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $isYellow ? 'FF' . $yellow : 'FF' . $navyHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(50);

        // Lignes de données
        $row = 4;
        foreach ($projects as $i => $p) {
            $bgMain = ($i % 2 === 0) ? $white : $navyAlt;

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $p['contract_number'] ?? '');
            $sheet->setCellValue('C' . $row, $p['department'] ?? '');
            $sheet->setCellValue('D' . $row, $p['name'] ?? '');
            $sheet->setCellValue('E' . $row, $p['location'] ?? '');
            $sheet->setCellValue('F' . $row, $p['date_of_creation'] ?? '');

            $sheet->setCellValue('G' . $row, $p['contract_amount_ht'] ?? 0);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->setCellValue('H' . $row, $p['execution_budget_ht'] ?? 0);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->setCellValue('I' . $row, $p['collected_amount_ht'] ?? 0);
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->setCellValue('J' . $row, $p['realisation'] ?? 0);
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode($numFormat);

            // K = % de décaissement (encaissement / budget)
            $sheet->setCellValue('K' . $row, "=IF(H{$row}<>0,I{$row}/H{$row},\"-\")");
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('0%');

            // L = taux d'exécution physique (depuis la DB)
            $execRate = $p['execution_rate'];
            if ($execRate !== null && $execRate !== '') {
                $sheet->setCellValue('L' . $row, (float)$execRate / 100);
                $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('0%');
            } else {
                $sheet->setCellValue('L' . $row, '-');
            }

            $sheet->setCellValue('M' . $row, "=IF(H{$row}<>0,H{$row}-J{$row},\"-\")");
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode($numFormat);

            $sheet->setCellValue('N' . $row, "=IF(H{$row}<>0,H{$row}-I{$row},\"-\")");
            $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode($numFormat);

            $sheet->setCellValue('O' . $row, $p['observation'] ?? '');

            // ── Colonnes jaunes : engagement fournisseurs ──
            $pid        = (int)($p['id'] ?? 0);
            $expAgg     = $expenseMap[$pid] ?? ['montant_total' => 0, 'montant_paye' => 0, 'reste_a_payer' => 0];
            $sheet->setCellValue('P' . $row, $expAgg['montant_total']);
            $sheet->getStyle('P' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->setCellValue('Q' . $row, $expAgg['montant_paye']);
            $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->setCellValue('R' . $row, $expAgg['reste_a_payer']);
            $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode($numFormat);

            $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['argb' => 'FF1A1A2E']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgMain]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ]);
            // Fond jaune sur les 3 colonnes engagement
            $sheet->getStyle("P{$row}:R{$row}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['argb' => 'FF1A1A2E']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ]);
            foreach (['A', 'C', 'F', 'K', 'L'] as $c) {
                $sheet->getStyle($c . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $sheet->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Ligne totaux
        $lastDataRow = $row - 1;
        $totalRow    = $row;
        $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAUX');
        foreach (['G', 'H', 'I', 'J'] as $col) {
            $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}4:{$col}{$lastDataRow})");
            $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode($numFormat);
        }
        foreach (['P', 'Q', 'R'] as $col) {
            $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}4:{$col}{$lastDataRow})");
            $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode($numFormat);
        }
        $sheet->getStyle("A{$totalRow}:O{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $white]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $navyDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        // Totaux P Q R en jaune foncé
        $sheet->getStyle("P{$totalRow}:R{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1A1A2E']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $yellow]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        $sheet->getRowDimension($totalRow)->setRowHeight(25);

        // Largeurs colonnes
        $colWidths = [
            'A' => 5,
            'B' => 28,
            'C' => 13,
            'D' => 38,
            'E' => 20,
            'F' => 14,
            'G' => 18,
            'H' => 18,
            'I' => 18,
            'J' => 14,
            'K' => 13,
            'L' => 13,
            'M' => 17,
            'N' => 17,
            'O' => 38,
            'P' => 18,
            'Q' => 18,
            'R' => 18,
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->freezePane('A4');

        $filename = 'ETAT_DES_CHANTIERS_' . date('d_m_Y') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
    } catch (\Throwable $e) {
        // Affichage de l'erreur exacte pour debug
        header('Content-Type: text/plain; charset=utf-8');
        echo "=== ERREUR exportExcel ===\n";
        var_dump([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        exit;
    }
}


// ============================================================
// EXPORT PDF  (Dompdf) – mise en page fidèle au document de référence
// ============================================================
function exportPDF(): void
{
    try {
        $pdo        = getPDO();
        $projects   = fetchProjects($pdo);
        $expenseMap = fetchExpensesByProject($pdo);   // ← données agrégées fournisseurs
        $dateStr    = dateFr('d F Y');
        $numFmt     = fn($v) => number_format((float)$v, 0, ',', ' ');

        // Logo en base64 (embarqué dans le HTML pour Dompdf)
        // Le dossier images est au même niveau que le dossier parent de ce fichier
        $logoPath = dirname(__DIR__) . '/images/logo_kamus.png';
        $logoTag  = file_exists($logoPath)
            ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '" alt="KAM US" style="height:60px;"/>'
            : '<span style="font-size:15px;font-weight:bold;color:#1E2A5E;">KAM US</span>';

        // ── Lignes de données ──
        $rows = '';
        $totalContrat = $totalBudget = $totalEnc = $totalReal = 0;
        $totalMontantTotal = $totalPaye = $totalReste = 0;

        foreach ($projects as $i => $p) {
            $bg = ($i % 2 === 0) ? '#ffffff' : '#f0f2fa';

            $contrat      = (float)($p['contract_amount_ht']  ?? 0);
            $budgetExec   = (float)($p['execution_budget_ht'] ?? 0);
            $encaissement = (float)($p['collected_amount_ht'] ?? 0);
            $realisation  = (float)($p['realisation']         ?? 0);

            $tauxDecaiss  = $budgetExec > 0 ? round(($encaissement / $budgetExec) * 100) . '%' : '-';
            $tauxExecPhys = ($p['execution_rate'] !== null && $p['execution_rate'] !== '')
                ? round((float)$p['execution_rate']) . '%' : '-';
            $partNonExec  = $budgetExec > 0 ? $numFmt($budgetExec - $realisation)  : '-';
            $resteEnc     = $budgetExec > 0 ? $numFmt($budgetExec - $encaissement) : '-';

            $totalContrat += $contrat;
            $totalBudget  += $budgetExec;
            $totalEnc     += $encaissement;
            $totalReal    += $realisation;

            // Colonnes engagement fournisseurs
            $pid     = (int)($p['id'] ?? 0);
            $expAgg  = $expenseMap[$pid] ?? ['montant_total' => 0, 'montant_paye' => 0, 'reste_a_payer' => 0];
            $mtTotal = $expAgg['montant_total'];
            $mtPaye  = $expAgg['montant_paye'];
            $mtReste = $expAgg['reste_a_payer'];
            $totalMontantTotal += $mtTotal;
            $totalPaye         += $mtPaye;
            $totalReste        += $mtReste;

            $rows .= '<tr style="background:' . $bg . ';height:28px;">
            <td class="c">' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($p['contract_number'] ?? '') . '</td>
            <td class="c">' . htmlspecialchars($p['department'] ?? '') . '</td>
            <td>' . htmlspecialchars($p['name'] ?? '') . '</td>
            <td>' . htmlspecialchars($p['location'] ?? '') . '</td>
            <td class="c">' . htmlspecialchars($p['date_of_creation'] ?? '') . '</td>
            <td class="r">' . ($contrat      > 0 ? $numFmt($contrat)      : '') . '</td>
            <td class="r">' . ($budgetExec   > 0 ? $numFmt($budgetExec)   : '') . '</td>
            <td class="r">' . ($encaissement > 0 ? $numFmt($encaissement) : '') . '</td>
            <td class="r">' . ($realisation  > 0 ? $numFmt($realisation)  : '') . '</td>
            <td class="c">' . $tauxDecaiss  . '</td>
            <td class="c">' . $tauxExecPhys . '</td>
            <td class="r">' . $partNonExec  . '</td>
            <td class="r">' . $resteEnc     . '</td>
            <td class="obs">' . htmlspecialchars($p['observation'] ?? '') . '</td>
            <td class="r yellow">' . ($mtTotal > 0 ? $numFmt($mtTotal) : '-') . '</td>
            <td class="r yellow">' . ($mtPaye  > 0 ? $numFmt($mtPaye)  : '-') . '</td>
            <td class="r yellow">' . ($mtReste != 0 ? $numFmt($mtReste) : '-') . '</td>
        </tr>';
        }

        // Ligne totaux
        $rows .= '<tr class="totals">
        <td colspan="6" style="text-align:left;padding-left:6px;">TOTAUX</td>
        <td class="r">' . $numFmt($totalContrat) . '</td>
        <td class="r">' . $numFmt($totalBudget)  . '</td>
        <td class="r">' . $numFmt($totalEnc)     . '</td>
        <td class="r">' . $numFmt($totalReal)    . '</td>
        <td></td><td></td><td></td><td></td><td></td>
        <td class="r" style="background:#FFD966;color:#1a1a2e;">' . $numFmt($totalMontantTotal) . '</td>
        <td class="r" style="background:#FFD966;color:#1a1a2e;">' . $numFmt($totalPaye)         . '</td>
        <td class="r" style="background:#FFD966;color:#1a1a2e;">' . $numFmt($totalReste)        . '</td>
    </tr>';

        // ── HTML complet ──
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<style>
    @page { margin: 10mm 8mm 8mm 8mm; }

    * { box-sizing: border-box; margin:0; padding:0; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 6pt;
        color: #1a1a2e;
        padding: 0;
        width: 100%;
    }

    /* ── En-tête ── */
    table.header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    table.header-table td { vertical-align: middle; border: none; padding: 0; }
    .td-logo  { width: 75px; }
    .td-date  { text-align: right; font-size: 7.5pt; font-weight: bold;
                color: #1E2A5E; white-space: nowrap; width: 90px; }

    /* ── Bandeaux de titre (ligne fusionnée sur 2 zones) ── */
    table.title-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .title-main {
        background: #1E2A5E;
        color: #ffffff;
        font-size: 9pt;
        font-weight: bold;
        text-align: center;
        padding: 5px 4px;
        width: 100%;
    }

    /* ── Tableau principal ── */
    table.data {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* Largeurs des colonnes — total = 100% exactement, 18 colonnes */
    col.c-no    { width: 2%; }
    col.c-bc    { width: 9%; }
    col.c-sec   { width: 5%; }
    col.c-obj   { width: 10%; }
    col.c-chan  { width: 6%; }
    col.c-date  { width: 5%; }
    col.c-mnt   { width: 6%; }
    col.c-bgt   { width: 6%; }
    col.c-enc   { width: 6%; }
    col.c-real  { width: 5%; }
    col.c-pct   { width: 4%; }
    col.c-taux  { width: 4%; }
    col.c-part  { width: 5%; }
    col.c-rest  { width: 5%; }
    col.c-obs   { width: 8%; }
    col.c-v1    { width: 6%; }
    col.c-v2    { width: 6%; }
    col.c-v3    { width: 6%; }

    /* En-têtes */
    thead th {
        background: #2E3A8C;
        color: #ffffff;
        font-size: 5.5pt;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        padding: 3px 2px;
        border: 0.5px solid #888;
        word-wrap: break-word;
        height: 32px;
    }

    /* Cellules données */
    tbody td {
        font-size: 5.5pt;
        vertical-align: middle;
        padding: 2px 2px;
        border: 0.5px solid #cccccc;
        word-wrap: break-word;
        overflow: hidden;
    }
    tbody td.c   { text-align: center; }
    tbody td.r   { text-align: right; }
    tbody td.obs { font-size: 5pt; }

    /* Colonnes engagement fournisseurs – fond jaune */
    tbody td.yellow {
        background: #FFF2CC !important;
    }
    thead th.yellow-th {
        background: #FFD966 !important;
        color: #1a1a2e !important;
    }

    /* Ligne totaux */
    tr.totals td {
        background: #1E2A5E;
        color: #ffffff;
        font-weight: bold;
        font-size: 6pt;
        padding: 4px 2px;
        border: 0.5px solid #888;
        text-align: center;
        vertical-align: middle;
    }
    tr.totals td.r    { text-align: right; }

    /* Pied de page */
    .footer-wrap {
        margin-top: 10px;
        width: 100%;
    }
    .footer-thanks {
        text-align: center;
        font-size: 7pt;
        font-weight: bold;
        color: #1E2A5E;
        margin-bottom: 3px;
    }
    .footer-brand {
        text-align: center;
        font-size: 5.5pt;
        color: #A07830;
        margin-bottom: 4px;
    }
    .footer-date {
        text-align: right;
        font-size: 5pt;
        color: #9aa0b8;
    }
</style>
</head>
<body>

<!-- En-tête : logo à gauche, date à droite -->
<table class="header-table">
    <tr>
        <td class="td-logo">' . $logoTag . '</td>
        <td>&nbsp;</td>
        <td class="td-date">' . $dateStr . '</td>
    </tr>
</table>

<!-- Bandeaux de titre -->
<table class="title-table" style="table-layout:fixed;width:100%;border-collapse:collapse;margin-bottom:0;">
    <tr>
        <td class="title-main">ETAT DES CHANTIERS ENCOURS</td>
    </tr>
</table>

<!-- Tableau principal -->
<table class="data" style="table-layout:fixed;width:100%;">
    <colgroup>
        <col class="c-no"/>  <col class="c-bc"/>  <col class="c-sec"/>
        <col class="c-obj"/> <col class="c-chan"/> <col class="c-date"/>
        <col class="c-mnt"/> <col class="c-bgt"/> <col class="c-enc"/>
        <col class="c-real"/><col class="c-pct"/> <col class="c-taux"/>
        <col class="c-part"/><col class="c-rest"/><col class="c-obs"/>
        <col class="c-v1"/>  <col class="c-v2"/>  <col class="c-v3"/>
    </colgroup>
    <thead>
        <tr>
            <th colspan="15" style="background:#1E2A5E;color:#fff;font-size:8pt;font-weight:bold;text-align:center;padding:4px;">ETAT DES CHANTIERS ENCOURS</th>
            <th colspan="3" class="yellow-th" style="font-size:7pt;font-weight:bold;text-align:center;padding:4px;">ENGAGEMENTS AUPRES DES FOURNISSEURS / PRESTATAIRES</th>
        </tr>
        <tr>
            <th>N°</th>
            <th>BON DE COMMANDE /<br/>CONTRAT</th>
            <th>SECTION</th>
            <th>OBJET</th>
            <th>CHANTIER</th>
            <th>DATE DE<br/>CONTRAT</th>
            <th>MONTANT HT<br/>DU MARCHE</th>
            <th>BUDGET<br/>D\'EXECUTION HT</th>
            <th>ENCAISSEMENT<br/>HT</th>
            <th>REALISATION</th>
            <th>% DE<br/>DECAISSEMENT<br/>/ BUDGET</th>
            <th>TAUX<br/>D\'EXECUTION<br/>PHYSIQUE</th>
            <th>PART DU<br/>MARCHE NON<br/>EXECUTEE</th>
            <th>RESTE A<br/>ENCAISSER HT</th>
            <th>OBSERVATION</th>
            <th class="yellow-th">MONTANT<br/>TOTAL<br/>PAIEMENT</th>
            <th class="yellow-th">PAIEMENT<br/>EFFECTUE</th>
            <th class="yellow-th">RESTE A<br/>PAYER</th>
        </tr>
    </thead>
    <tbody>' . $rows . '</tbody>
</table>

<div class="footer-wrap">
    <div class="footer-thanks">Merci pour votre collaboration</div>
    <div class="footer-brand">Document g&eacute;n&eacute;r&eacute; avec OrizonPlus, syst&egrave;me de gestion</div>
    <div class="footer-date">KAM UNITED SOCIETY &mdash; &Eacute;tat des Chantiers &mdash; ' . $dateStr . '</div>
</div>
</body>
</html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        $filename = 'ETAT_DES_CHANTIERS_' . date('d_m_Y') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    } catch (\Throwable $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "=== ERREUR exportPDF ===\n";
        var_dump([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        exit;
    }
}


// ============================================================
// AGRÉGATION DES DÉPENSES PAR PROJET (requête directe en base)
// ============================================================
function fetchExpensesByProject(PDO $pdo): array
{
    try {
        // montant_total  = somme de toutes les dépenses engagées (amount)
        // montant_paye   = somme des montants réellement payés (paid_amount)
        // reste_a_payer  = montant_total - montant_paye
        $rows = $pdo->query("
            SELECT
                e.project_id,
                IFNULL(SUM(e.amount),      0) AS montant_total,
                IFNULL(SUM(e.paid_amount), 0) AS montant_paye
            FROM expenses e
            GROUP BY e.project_id
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        // Fallback si paid_amount absent de la table
        $rows = $pdo->query("
            SELECT
                e.project_id,
                IFNULL(SUM(e.amount), 0) AS montant_total,
                0                        AS montant_paye
            FROM expenses e
            GROUP BY e.project_id
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    $byProject = [];
    foreach ($rows as $row) {
        $pid   = (int)$row['project_id'];
        $total = (float)$row['montant_total'];
        $paye  = (float)$row['montant_paye'];
        $byProject[$pid] = [
            'montant_total' => $total,
            'montant_paye'  => $paye,
            'reste_a_payer' => $total - $paye,
        ];
    }

    return $byProject;
}

// ============================================================
// REQUÊTE COMMUNE
// ============================================================
function fetchProjects(PDO $pdo): array
{
    $ids = [];
    if (!empty($_GET['ids'])) {
        $ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
    }

    $where = '';
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where = "WHERE p.id IN ({$placeholders})";
    }

    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.contract_number, p.name, p.department, p.description,
            p.date_of_creation, p.contract_amount_ht, p.execution_budget_ht,
            p.collected_amount_ht, p.observation,
            p.project_status, p.location,
            p.execution_rate,
            IFNULL(SUM(e.amount), 0) AS realisation
        FROM projects p
        LEFT JOIN expenses e ON e.project_id = p.id
        {$where}
        GROUP BY p.id
        ORDER BY p.date_of_creation DESC
    ");
    $stmt->execute(!empty($ids) ? array_values($ids) : []);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
