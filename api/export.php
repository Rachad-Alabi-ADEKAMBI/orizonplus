<?php
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'db.php';

function exportProjectsAndExpenses()
{
    $pdo = getPDO();

    // =======================
    // Feuille 1 : Projets et lignes budgétaires
    // =======================
    $stmtProjects = $pdo->prepare("
        SELECT 
            p.name AS project_name,
            bl.name AS budget_line_name,
            pbl.allocated_amount
        FROM project_budget_lines pbl
        JOIN projects p ON p.id = pbl.project_id
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        ORDER BY p.name
    ");
    $stmtProjects->execute();
    $projects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);

    // =======================
    // Feuille 2 : Dépenses
    // =======================
    $stmtExpenses = $pdo->prepare("
        SELECT 
            p.name AS project_name,
            bl.name AS budget_line_name,
            e.description,
            e.expense_date,
            e.amount
        FROM expenses e
        JOIN projects p ON p.id = e.project_id
        JOIN project_budget_lines pbl ON pbl.id = e.project_budget_line_id
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        ORDER BY e.expense_date
    ");
    $stmtExpenses->execute();
    $expenses = $stmtExpenses->fetchAll(PDO::FETCH_ASSOC);

    // =======================
    // Création du Spreadsheet
    // =======================
    $spreadsheet = new Spreadsheet();

    // --- Feuille 1 ---
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Projets & Budgets');
    $sheet1->setCellValue('A1', 'Projet');
    $sheet1->setCellValue('B1', 'Ligne Budgétaire');
    $sheet1->setCellValue('C1', 'Montant Alloué');

    $row = 2;
    foreach ($projects as $p) {
        $sheet1->setCellValue("A$row", $p['project_name']);
        $sheet1->setCellValue("B$row", $p['budget_line_name']);
        $sheet1->setCellValue("C$row", $p['allocated_amount']);
        $row++;
    }

    // --- Feuille 2 ---
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Dépenses');
    $sheet2->setCellValue('A1', 'Projet');
    $sheet2->setCellValue('B1', 'Ligne Budgétaire');
    $sheet2->setCellValue('C1', 'Description');
    $sheet2->setCellValue('D1', 'Date');
    $sheet2->setCellValue('E1', 'Montant');

    $row = 2;
    foreach ($expenses as $e) {
        $sheet2->setCellValue("A$row", $e['project_name']);
        $sheet2->setCellValue("B$row", $e['budget_line_name']);
        $sheet2->setCellValue("C$row", $e['description']);
        $sheet2->setCellValue("D$row", $e['expense_date']);
        $sheet2->setCellValue("E$row", $e['amount']);
        $row++;
    }

    // =======================
    // Export
    // =======================
    $writer = new Xlsx($spreadsheet);
    $fileName = 'export_projects_expenses.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

// Appel de la fonction
exportProjectsAndExpenses();
