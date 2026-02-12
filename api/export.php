<?php
require 'vendor/autoload.php';
require 'db.php'; // Ton fichier contenant getPDO()

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Créer une instance de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes des colonnes
$sheet->setCellValue('A1', 'ID Projet');
$sheet->setCellValue('B1', 'Nom Projet');
$sheet->setCellValue('C1', 'ID Ligne Budgétaire');
$sheet->setCellValue('D1', 'Nom Ligne Budgétaire');
$sheet->setCellValue('E1', 'Montant Alloué');

// Récupérer les projets et lignes budgétaires
$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT 
        p.id AS project_id,
        p.name AS project_name,
        pbl.id AS budget_line_id,
        bl.name AS budget_line_name,
        pbl.allocated_amount
    FROM projects p
    LEFT JOIN project_budget_lines pbl ON pbl.project_id = p.id
    LEFT JOIN budget_lines bl ON bl.id = pbl.budget_line_id
    ORDER BY p.id, pbl.id
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remplir les données
$rowNum = 2;
foreach ($rows as $row) {
	$sheet->setCellValue('A' . $rowNum, $row['project_id']);
	$sheet->setCellValue('B' . $rowNum, $row['project_name']);
	$sheet->setCellValue('C' . $rowNum, $row['budget_line_id']);
	$sheet->setCellValue('D' . $rowNum, $row['budget_line_name']);
	$sheet->setCellValue('E' . $rowNum, $row['allocated_amount']);
	$rowNum++;
}

// Définir le nom du fichier
$filename = 'projets_lignes_budgetaires.xlsx';

// Envoyer le fichier au navigateur
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
