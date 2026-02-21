<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'db.php';

function exportEtatChantiers()
{
	$pdo = getPDO();

	$stmt = $pdo->prepare("
        SELECT 
            contract_number,
            name,
            department,
            description,
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

	// DEBUG : afficher le premier projet pour voir les colonnes récupérées
	var_dump($projects[0]);
	exit;
}

exportEtatChantiers();
