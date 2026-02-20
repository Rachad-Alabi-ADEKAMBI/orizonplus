<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

include 'db.php';

function exportEtatChantiers()
{
	$pdo = getPDO();

	$stmt = $pdo->prepare("
        SELECT 
            contract_number,
            department,
            description,
            location,
            date_of_creation,
            contract_amount_ht,
            execution_budget_ht,
            collected_amount_ht,
            total_payment_made,
            observation,
            project_status
        FROM projects
        ORDER BY date_of_creation DESC
    ");
	$stmt->execute();
	$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setTitle('Etat des Chantiers');

	// ======================
	// TITRE
	// ======================
	$sheet->mergeCells('A1:N1');
	$sheet->setCellValue('A1', 'ETAT DES CHANTIERS AU ' . date('d/m/Y'));
	$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
	$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

	// ======================
	// EN-TÊTES
	// ======================
	$headers = [
		'A3' => 'N° BON / CONTRAT',
		'B3' => 'SECTION',
		'C3' => 'OBJET',
		'D3' => 'CHANTIER',
		'E3' => 'DATE CONTRAT',
		'F3' => 'MONTANT MARCHE HT',
		'G3' => 'BUDGET EXECUTION HT',
		'H3' => 'ENCAISSEMENT HT',
		'I3' => 'PAIEMENT EFFECTUE',
		'J3' => '% DECAISSEMENT',
		'K3' => 'RESTE A ENCAISSER',
		'L3' => 'RESTE A PAYER',
		'M3' => 'OBSERVATION',
		'N3' => 'STATUT'
	];

	foreach ($headers as $cell => $value) {
		$sheet->setCellValue($cell, $value);
	}

	$sheet->getStyle('A3:N3')->getFont()->setBold(true);
	$sheet->getStyle('A3:N3')->getFill()->setFillType(Fill::FILL_SOLID)
		->getStartColor()->setRGB('D9D9D9');

	// ======================
	// DONNÉES
	// ======================
	$row = 4;

	$totalMarche = 0;
	$totalBudget = 0;
	$totalEncaisse = 0;
	$totalPaiement = 0;

	foreach ($projects as $p) {

		$resteEncaisser = $p['contract_amount_ht'] - $p['collected_amount_ht'];
		$restePayer = $p['execution_budget_ht'] - $p['total_payment_made'];

		$percentDecaissement = 0;
		if ($p['execution_budget_ht'] > 0) {
			$percentDecaissement = $p['total_payment_made'] / $p['execution_budget_ht'];
		}

		$sheet->setCellValue("A$row", $p['contract_number']);
		$sheet->setCellValue("B$row", $p['department']);
		$sheet->setCellValue("C$row", $p['description']);
		$sheet->setCellValue("D$row", $p['location']);
		$sheet->setCellValue("E$row", $p['date_of_creation']);
		$sheet->setCellValue("F$row", $p['contract_amount_ht']);
		$sheet->setCellValue("G$row", $p['execution_budget_ht']);
		$sheet->setCellValue("H$row", $p['collected_amount_ht']);
		$sheet->setCellValue("I$row", $p['total_payment_made']);
		$sheet->setCellValue("J$row", $percentDecaissement);
		$sheet->setCellValue("K$row", $resteEncaisser);
		$sheet->setCellValue("L$row", $restePayer);
		$sheet->setCellValue("M$row", $p['observation']);
		$sheet->setCellValue("N$row", $p['project_status']);

		$totalMarche += $p['contract_amount_ht'];
		$totalBudget += $p['execution_budget_ht'];
		$totalEncaisse += $p['collected_amount_ht'];
		$totalPaiement += $p['total_payment_made'];

		$row++;
	}

	// ======================
	// FORMAT MONETAIRE
	// ======================
	$sheet->getStyle("F4:L$row")
		->getNumberFormat()
		->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

	$sheet->getStyle("J4:J$row")
		->getNumberFormat()
		->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

	// ======================
	// TOTAUX
	// ======================
	$sheet->setCellValue("E$row", "TOTAL");
	$sheet->setCellValue("F$row", $totalMarche);
	$sheet->setCellValue("G$row", $totalBudget);
	$sheet->setCellValue("H$row", $totalEncaisse);
	$sheet->setCellValue("I$row", $totalPaiement);

	$sheet->getStyle("E$row:N$row")->getFont()->setBold(true);

	// ======================
	// BORDURES
	// ======================
	$sheet->getStyle("A3:N$row")->getBorders()->getAllBorders()
		->setBorderStyle(Border::BORDER_THIN);

	// ======================
	// AUTO SIZE COLONNES
	// ======================
	foreach (range('A', 'N') as $col) {
		$sheet->getColumnDimension($col)->setAutoSize(true);
	}

	// ======================
	// EXPORT
	// ======================
	$writer = new Xlsx($spreadsheet);
	$fileName = 'etat_des_chantiers.xlsx';

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	header('Cache-Control: max-age=0');

	$writer->save('php://output');
	exit;
}

exportEtatChantiers();
