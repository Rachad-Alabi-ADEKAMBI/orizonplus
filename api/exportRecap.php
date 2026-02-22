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
// DISPATCH : si format demandé on exporte, sinon on affiche le modal
// ============================================================
$format = $_GET['format'] ?? null;

if ($format === 'excel') { exportExcel(); exit; }
if ($format === 'pdf')   { exportPDF();   exit; }

// ============================================================
// PAGE HTML – Modal de choix du format
// ============================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Export – État des Chantiers | KAM US</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: radial-gradient(ellipse at 60% 40%, #dde3f5 0%, #eef0f8 60%, #f5f6fc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Overlay ── */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 18, 55, 0.50);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn .2s ease;
        }

        @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(32px) scale(.96); }
            to   { opacity: 1; transform: translateY(0)    scale(1);   }
        }

        /* ── Modal ── */
        .modal {
            background: #fff;
            border-radius: 22px;
            padding: 48px 52px 42px;
            width: 500px;
            max-width: 95vw;
            box-shadow:
                0 2px 6px rgba(10,18,55,.06),
                0 12px 40px rgba(10,18,55,.16),
                0 32px 80px rgba(10,18,55,.10);
            animation: slideUp .32s cubic-bezier(.22,.68,0,1.15);
            text-align: center;
            position: relative;
        }

        /* Fine bande décorative en haut */
        .modal::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1E2A5E 0%, #2E3A8C 50%, #C9A96E 100%);
            border-radius: 22px 22px 0 0;
        }

        .modal-logo {
            height: 72px;
            margin-bottom: 18px;
            object-fit: contain;
        }

        .modal h2 {
            font-size: 1.2rem;
            color: #1E2A5E;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -.01em;
        }

        .modal p {
            font-size: .86rem;
            color: #8a93b2;
            margin-bottom: 34px;
            line-height: 1.5;
        }

        /* ── Boutons de choix ── */
        .choices {
            display: flex;
            gap: 18px;
            justify-content: center;
        }

        .choice-btn {
            flex: 1;
            border: 2px solid transparent;
            border-radius: 16px;
            padding: 26px 18px 20px;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .choice-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,.12);
        }

        .choice-btn:active { transform: translateY(-1px); }

        /* Excel : bleu marine */
        .choice-btn.excel             { background: #f0f2fa; border-color: #2E3A8C; }
        .choice-btn.excel:hover       { background: #e4e8f7; border-color: #1E2A5E; }
        .choice-btn.excel .btn-label  { color: #1E2A5E; }
        .choice-btn.excel .btn-sub    { color: #8a93b2; }

        /* PDF : beige/or */
        .choice-btn.pdf               { background: #fdf8f0; border-color: #C9A96E; }
        .choice-btn.pdf:hover         { background: #faf0d7; border-color: #A07830; }
        .choice-btn.pdf .btn-label    { color: #A07830; }
        .choice-btn.pdf .btn-sub      { color: #c4aa80; }

        .choice-btn .btn-icon { width: 52px; height: 52px; }

        .choice-btn .btn-label {
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .01em;
        }

        .choice-btn .btn-sub {
            font-size: .75rem;
        }

        /* ── Annuler ── */
        .cancel-wrap { margin-top: 26px; }
        .cancel {
            font-size: .82rem;
            color: #b0b8d0;
            cursor: pointer;
            background: none;
            border: none;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color .15s;
        }
        .cancel:hover { color: #1E2A5E; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="modal">

        <img src="images/logo_kamus.png" alt="KAM US" class="modal-logo"
             onerror="this.style.display='none'"/>

        <h2>Exporter l'état des chantiers</h2>
        <p>Sélectionnez le format dans lequel vous souhaitez<br>télécharger le document.</p>

        <div class="choices">

            <!-- Bouton Excel -->
            <a href="?format=excel" class="choice-btn excel">
                <svg class="btn-icon" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="10" fill="#2E3A8C" fill-opacity=".1"/>
                    <path d="M30 10H18a3 3 0 0 0-3 3v26a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3V20L30 10Z"
                          fill="#2E3A8C" fill-opacity=".2" stroke="#2E3A8C" stroke-width="1.5"
                          stroke-linejoin="round"/>
                    <path d="M30 10v10h10" stroke="#2E3A8C" stroke-width="1.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 28l3.5-4.5L21 19M31 19l-3.5 4.5L31 28" stroke="#2E3A8C"
                          stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="24.5" y1="23.5" x2="27.5" y2="23.5" stroke="#2E3A8C"
                          stroke-width="1.4" stroke-linecap="round"/>
                    <rect x="16" y="32" width="20" height="7" rx="2"
                          fill="#2E3A8C" fill-opacity=".15"/>
                    <text x="26" y="38" text-anchor="middle" font-size="5.5" font-weight="bold"
                          fill="#2E3A8C" font-family="sans-serif">XLSX</text>
                </svg>
                <span class="btn-label">Excel</span>
                <span class="btn-sub">.xlsx</span>
            </a>

            <!-- Bouton PDF -->
            <a href="?format=pdf" class="choice-btn pdf">
                <svg class="btn-icon" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="10" fill="#C9A96E" fill-opacity=".15"/>
                    <path d="M30 10H18a3 3 0 0 0-3 3v26a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3V20L30 10Z"
                          fill="#C9A96E" fill-opacity=".3" stroke="#A07830" stroke-width="1.5"
                          stroke-linejoin="round"/>
                    <path d="M30 10v10h10" stroke="#A07830" stroke-width="1.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="20" y1="24" x2="32" y2="24" stroke="#A07830"
                          stroke-width="1.4" stroke-linecap="round"/>
                    <line x1="20" y1="27.5" x2="28" y2="27.5" stroke="#A07830"
                          stroke-width="1.4" stroke-linecap="round"/>
                    <rect x="16" y="32" width="20" height="7" rx="2"
                          fill="#A07830" fill-opacity=".2"/>
                    <text x="26" y="38" text-anchor="middle" font-size="5.5" font-weight="bold"
                          fill="#A07830" font-family="sans-serif">PDF</text>
                </svg>
                <span class="btn-label">PDF</span>
                <span class="btn-sub">.pdf</span>
            </a>

        </div>

        <div class="cancel-wrap">
            <button class="cancel" onclick="window.history.back()">Annuler</button>
        </div>
    </div>
</div>

</body>
</html>
<?php

// ============================================================
// EXPORT EXCEL
// ============================================================
function exportExcel(): void
{
    $pdo      = getPDO();
    $projects = fetchProjects($pdo);

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Etat des Chantiers');

    $navyDark   = '1E2A5E';
    $navyHeader = '2E3A8C';
    $navyAlt    = 'F0F2FA';
    $goldDark   = 'A07830';
    $goldHeader = 'C9A96E';
    $goldLight  = 'FDF6E3';
    $goldAlt    = 'FAF0D7';
    $white      = 'FFFFFF';
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
    $sheet->setCellValue('R1', date('d M Y'));
    $sheet->getStyle('R1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $navyDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(55);

    // Ligne 2 – Titres sections
    $sheet->mergeCells('A2:O2');
    $sheet->setCellValue('A2', 'ETAT DES CHANTIERS ENCOURS');
    $sheet->getStyle('A2')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF' . $white]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $navyDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->mergeCells('P2:R2');
    $sheet->setCellValue('P2', 'ENGAGEMENTS FOURNISSEURS');
    $sheet->getStyle('P2')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $white]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $goldDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(28);

    // Ligne 3 – En-têtes
    $headers = [
        'A' => 'N°', 'B' => "BON DE COMMANDE /CONTRAT", 'C' => 'SECTION',
        'D' => 'OBJET', 'E' => 'CHANTIER', 'F' => 'DATE DE CONTRAT',
        'G' => 'MONTANT HT DU MARCHE', 'H' => "BUDGET D'EXECUTION HT",
        'I' => 'ENCAISSEMENT HT', 'J' => 'REALISATION',
        'K' => "% DE DECAISSEMENT PAR RAPPORT AU BUDGET D'EXECUTION",
        'L' => "TAUX D'EXECUTION PHYSIQUE", 'M' => 'PART DU MARCHE NON EXECUTEE',
        'N' => 'RESTE A ENCAISSER HT', 'O' => 'OBSERVATION',
        'P' => 'MONTANT TOTAL', 'Q' => 'PAIEMENT EFFECTUE', 'R' => 'RESTE A PAYER',
    ];
    foreach ($headers as $col => $label) {
        $sheet->setCellValue($col . '3', $label);
        $isGold = in_array($col, ['P', 'Q', 'R']);
        $sheet->getStyle($col . '3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF' . ($isGold ? $navyDark : $white)]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . ($isGold ? $goldHeader : $navyHeader)]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
    }
    $sheet->getRowDimension(3)->setRowHeight(50);

    // Lignes de données
    $row = 4;
    foreach ($projects as $i => $p) {
        $bgMain = ($i % 2 === 0) ? $white     : $navyAlt;
        $bgGold = ($i % 2 === 0) ? $goldLight : $goldAlt;

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

        $sheet->setCellValue('K' . $row, '');

        $sheet->setCellValue('L' . $row, "=IF(H{$row}<>0,J{$row}/H{$row},\"-\")");
        $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('0%');

        $sheet->setCellValue('M' . $row, "=IF(H{$row}<>0,H{$row}-J{$row},\"-\")");
        $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode($numFormat);

        $sheet->setCellValue('N' . $row, "=IF(H{$row}<>0,H{$row}-I{$row},\"-\")");
        $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode($numFormat);

        $sheet->setCellValue('O' . $row, $p['observation'] ?? '');
        $sheet->setCellValue('P' . $row, 0);
        $sheet->getStyle('P' . $row)->getNumberFormat()->setFormatCode($numFormat);

        $sheet->setCellValue('Q' . $row, $p['total_payment_made'] ?? 0);
        $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode($numFormat);

        $sheet->setCellValue('R' . $row, "=IF(P{$row}<>0,P{$row}-Q{$row},\"-\")");
        $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode($numFormat);

        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['argb' => 'FF1A1A2E']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgMain]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
        ]);
        $sheet->getStyle("P{$row}:R{$row}")->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['argb' => 'FF1A1A2E']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgGold]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
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
    $sheet->getStyle("A{$totalRow}:O{$totalRow}")->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $white]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $navyDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
    ]);
    foreach (['P', 'Q'] as $col) {
        $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}4:{$col}{$lastDataRow})");
        $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode($numFormat);
    }
    $sheet->getStyle("P{$totalRow}:R{$totalRow}")->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $white]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $goldDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
    ]);
    $sheet->getRowDimension($totalRow)->setRowHeight(25);

    // Largeurs colonnes
    $colWidths = [
        'A' => 5,  'B' => 28, 'C' => 13, 'D' => 38, 'E' => 20,
        'F' => 14, 'G' => 18, 'H' => 18, 'I' => 18, 'J' => 14,
        'K' => 13, 'L' => 13, 'M' => 17, 'N' => 17, 'O' => 38,
        'P' => 18, 'Q' => 18, 'R' => 18,
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
}


// ============================================================
// EXPORT PDF  (Dompdf) – mise en page fidèle au document de référence
// ============================================================
function exportPDF(): void
{
    $pdo      = getPDO();
    $projects = fetchProjects($pdo);
    $dateStr  = date('d M Y');
    $numFmt   = fn($v) => number_format((float)$v, 0, ',', ' ');

    // Logo en base64 (embarqué dans le HTML pour Dompdf)
    // Le dossier images est au même niveau que le dossier parent de ce fichier
    $logoPath = dirname(__DIR__) . '/images/logo_kamus.png';
    $logoTag  = file_exists($logoPath)
        ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '" alt="KAM US" style="height:60px;"/>'
        : '<span style="font-size:15px;font-weight:bold;color:#1E2A5E;">KAM US</span>';

    // ── Lignes de données ──
    $rows = '';
    $totalContrat = $totalBudget = $totalEnc = $totalReal = $totalMontant = $totalPmt = 0;

    foreach ($projects as $i => $p) {
        $bg     = ($i % 2 === 0) ? '#ffffff' : '#f0f2fa';
        $bgGold = ($i % 2 === 0) ? '#fdf6e3' : '#faf0d7';

        $contrat      = (float)($p['contract_amount_ht']  ?? 0);
        $budgetExec   = (float)($p['execution_budget_ht'] ?? 0);
        $encaissement = (float)($p['collected_amount_ht'] ?? 0);
        $realisation  = (float)($p['realisation']         ?? 0);
        $montantTotal = 0; // à remplir manuellement
        $paiement     = (float)($p['total_payment_made']  ?? 0);

        // Calculs
        $tauxDecaiss  = $budgetExec > 0 ? round(($encaissement / $budgetExec) * 100) . '%' : '-';
        $tauxExec     = $budgetExec > 0 ? round(($realisation  / $budgetExec) * 100) . '%' : '-';
        $partNonExec  = $budgetExec > 0 ? $numFmt($budgetExec - $realisation)  : '-';
        $resteEnc     = $budgetExec > 0 ? $numFmt($budgetExec - $encaissement) : '-';
        $resteAPayer  = $montantTotal > 0 ? $numFmt($montantTotal - $paiement) : '-';

        $totalContrat  += $contrat;
        $totalBudget   += $budgetExec;
        $totalEnc      += $encaissement;
        $totalReal     += $realisation;
        $totalMontant  += $montantTotal;
        $totalPmt      += $paiement;

        $rows .= '<tr style="background:' . $bg . ';height:28px;">
            <td class="c">' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($p['contract_number'] ?? '') . '</td>
            <td class="c">' . htmlspecialchars($p['department'] ?? '') . '</td>
            <td>' . htmlspecialchars($p['name'] ?? '') . '</td>
            <td>' . htmlspecialchars($p['location'] ?? '') . '</td>
            <td class="c">' . htmlspecialchars($p['date_of_creation'] ?? '') . '</td>
            <td class="r">' . ($contrat     > 0 ? $numFmt($contrat)     : '') . '</td>
            <td class="r">' . ($budgetExec  > 0 ? $numFmt($budgetExec)  : '') . '</td>
            <td class="r">' . ($encaissement> 0 ? $numFmt($encaissement): '') . '</td>
            <td class="r">' . ($realisation > 0 ? $numFmt($realisation) : '') . '</td>
            <td class="c">' . $tauxDecaiss . '</td>
            <td class="c">' . $tauxExec . '</td>
            <td class="r">' . $partNonExec . '</td>
            <td class="r">' . $resteEnc . '</td>
            <td class="obs">' . htmlspecialchars($p['observation'] ?? '') . '</td>
            <td class="r" style="background:' . $bgGold . ';">' . ($montantTotal > 0 ? $numFmt($montantTotal) : '') . '</td>
            <td class="r" style="background:' . $bgGold . ';">' . ($paiement     > 0 ? $numFmt($paiement)     : '') . '</td>
            <td class="r" style="background:' . $bgGold . ';">' . $resteAPayer . '</td>
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
        <td class="r gold">' . $numFmt($totalMontant) . '</td>
        <td class="r gold">' . $numFmt($totalPmt)     . '</td>
        <td class="gold"></td>
    </tr>';

    // ── HTML complet ──
    $html = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<style>
    * { box-sizing: border-box; margin:0; padding:0; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 6pt;
        color: #1a1a2e;
        padding: 10mm 8mm 8mm;
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
        width: 82%;
    }
    .title-gold {
        background: #A07830;
        color: #ffffff;
        font-size: 7pt;
        font-weight: bold;
        text-align: center;
        padding: 5px 4px;
        width: 18%;
    }

    /* ── Tableau principal ── */
    table.data {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* Largeurs des colonnes (total = 100%) */
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
    col.c-obs   { width: 9%; }
    col.c-mtot  { width: 5%; }
    col.c-pmt   { width: 5%; }
    col.c-rap   { width: 5%; }

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
    thead th.gold {
        background: #C9A96E;
        color: #1E2A5E;
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
    tr.totals td.gold { background: #A07830; }

    /* Pied de page */
    .footer {
        margin-top: 8px;
        font-size: 5.5pt;
        color: #9aa0b8;
        text-align: right;
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
<table class="title-table">
    <tr>
        <td class="title-main">ETAT DES CHANTIERS ENCOURS</td>
        <td class="title-gold">ENGAGEMENTS AUPRES DES FOURNISSEURS / PRESTATAIRES</td>
    </tr>
</table>

<!-- Tableau principal -->
<table class="data">
    <colgroup>
        <col class="c-no"/>  <col class="c-bc"/>  <col class="c-sec"/>
        <col class="c-obj"/> <col class="c-chan"/> <col class="c-date"/>
        <col class="c-mnt"/> <col class="c-bgt"/> <col class="c-enc"/>
        <col class="c-real"/><col class="c-pct"/> <col class="c-taux"/>
        <col class="c-part"/><col class="c-rest"/><col class="c-obs"/>
        <col class="c-mtot"/><col class="c-pmt"/> <col class="c-rap"/>
    </colgroup>
    <thead>
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
            <th class="gold">MONTANT<br/>TOTAL</th>
            <th class="gold">PAIEMENT<br/>EFFECTUE</th>
            <th class="gold">RESTE A<br/>PAYER</th>
        </tr>
    </thead>
    <tbody>' . $rows . '</tbody>
</table>

<div class="footer">KAM UNITED SOCIETY &mdash; &Eacute;tat des Chantiers &mdash; ' . $dateStr . '</div>
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
}


// ============================================================
// REQUÊTE COMMUNE
// ============================================================
function fetchProjects(PDO $pdo): array
{
    $stmt = $pdo->prepare("
        SELECT
            p.contract_number, p.name, p.department, p.description,
            p.date_of_creation, p.contract_amount_ht, p.execution_budget_ht,
            p.collected_amount_ht, p.total_payment_made, p.observation,
            p.project_status, p.location,
            IFNULL(SUM(e.amount), 0) AS realisation
        FROM projects p
        LEFT JOIN expenses e ON e.project_id = p.id
        GROUP BY p.id
        ORDER BY p.date_of_creation DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}