<?php

require 'vendor/autoload.php';
require 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$pdo = getPDO();

/*
|--------------------------------------------------------------------------
| 1️⃣ Vérification du fichier envoyé
|--------------------------------------------------------------------------
*/
if (!isset($_FILES['file'])) {
	echo '<script>alert("Aucun fichier envoyé."); window.history.back();</script>';
	exit;
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
	echo '<script>alert("Erreur lors de l\'upload du fichier."); window.history.back();</script>';
	exit;
}

$fileTmpPath = $_FILES['file']['tmp_name'];
$fileName = $_FILES['file']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, ['xlsx', 'xls'])) {
	echo '<script>alert("Format invalide. Veuillez importer un fichier Excel (.xlsx ou .xls)."); window.history.back();</script>';
	exit;
}

/*
|--------------------------------------------------------------------------
| 2️⃣ Traitement du fichier
|--------------------------------------------------------------------------
*/
try {
	$pdo->beginTransaction();

	$spreadsheet = IOFactory::load($fileTmpPath);

	// ---------- 2A️⃣ Projets & Budgets ----------
	$sheetProjects = $spreadsheet->getSheetByName('Projets & Budgets');
	if (!$sheetProjects) {
		throw new Exception("La feuille 'Projets & Budgets' est introuvable.");
	}

	$rowsProjects = $sheetProjects->toArray();
	if (count($rowsProjects) <= 1) {
		throw new Exception("La feuille 'Projets & Budgets' est vide.");
	}

	array_shift($rowsProjects); // retirer l'en-tête

	foreach ($rowsProjects as $lineNumber => $row) {
		$lineNumber += 2;

		if (count($row) < 3) {
			throw new Exception("Structure invalide à la ligne {$lineNumber} dans 'Projets & Budgets'.");
		}

		[$projectName, $budgetLineName, $allocatedAmount] = $row;

		if (!$projectName) throw new Exception("Nom du projet manquant à la ligne {$lineNumber}.");
		if (!$budgetLineName) throw new Exception("Ligne budgétaire manquante à la ligne {$lineNumber}.");
		if (!is_numeric($allocatedAmount)) throw new Exception("Montant alloué invalide à la ligne {$lineNumber}.");

		// Projet
		$stmtProject = $pdo->prepare("SELECT id FROM projects WHERE name = ?");
		$stmtProject->execute([$projectName]);
		$projectId = $stmtProject->fetchColumn();

		if (!$projectId) {
			$insertProject = $pdo->prepare("
                INSERT INTO projects 
                (name, description, documents, date_of_creation, created_at)
                VALUES (?, '', '[]', NULL, NOW())
            ");
			$insertProject->execute([$projectName]);
			$projectId = $pdo->lastInsertId();
		}

		// Ligne budgétaire
		$stmtBudget = $pdo->prepare("SELECT id FROM budget_lines WHERE name = ?");
		$stmtBudget->execute([$budgetLineName]);
		$budgetLineId = $stmtBudget->fetchColumn();

		if (!$budgetLineId) {
			$insertBudget = $pdo->prepare("INSERT INTO budget_lines (name) VALUES (?)");
			$insertBudget->execute([$budgetLineName]);
			$budgetLineId = $pdo->lastInsertId();
		}

		// Liaison projet-ligne
		$stmtPBL = $pdo->prepare("
            SELECT id FROM project_budget_lines 
            WHERE project_id = ? AND budget_line_id = ?
        ");
		$stmtPBL->execute([$projectId, $budgetLineId]);
		$projectBudgetLineId = $stmtPBL->fetchColumn();

		if (!$projectBudgetLineId) {
			$insertPBL = $pdo->prepare("
                INSERT INTO project_budget_lines
                (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ");
			$insertPBL->execute([$projectId, $budgetLineId, (float)$allocatedAmount]);
		}
	}

	// ---------- 2B️⃣ Dépenses ----------
	$sheetExpenses = $spreadsheet->getSheetByName('Dépenses');
	if ($sheetExpenses) {
		$rowsExpenses = $sheetExpenses->toArray();
		if (count($rowsExpenses) > 1) {
			array_shift($rowsExpenses);
			foreach ($rowsExpenses as $lineNumber => $row) {
				$lineNumber += 2;
				if (count($row) < 5) throw new Exception("Structure invalide à la ligne {$lineNumber} dans 'Dépenses'.");

				[$projectName, $budgetLineName, $expenseDescription, $expenseDate, $expenseAmount] = $row;

				if (!$projectName || !$budgetLineName) {
					throw new Exception("Projet ou ligne budgétaire manquant à la ligne {$lineNumber} dans 'Dépenses'.");
				}
				if (!is_numeric($expenseAmount)) {
					throw new Exception("Montant de dépense invalide à la ligne {$lineNumber}.");
				}

				// Récupérer IDs
				$stmtProject = $pdo->prepare("SELECT id FROM projects WHERE name = ?");
				$stmtProject->execute([$projectName]);
				$projectId = $stmtProject->fetchColumn();
				if (!$projectId) throw new Exception("Projet '{$projectName}' introuvable à la ligne {$lineNumber}.");

				$stmtBudget = $pdo->prepare("SELECT id FROM budget_lines WHERE name = ?");
				$stmtBudget->execute([$budgetLineName]);
				$budgetLineId = $stmtBudget->fetchColumn();
				if (!$budgetLineId) throw new Exception("Ligne budgétaire '{$budgetLineName}' introuvable à la ligne {$lineNumber}.");

				$stmtPBL = $pdo->prepare("
                    SELECT id FROM project_budget_lines 
                    WHERE project_id = ? AND budget_line_id = ?
                ");
				$stmtPBL->execute([$projectId, $budgetLineId]);
				$projectBudgetLineId = $stmtPBL->fetchColumn();
				if (!$projectBudgetLineId) {
					throw new Exception("Le projet '{$projectName}' n'a pas de ligne budgétaire '{$budgetLineName}' allouée.");
				}

				// Insertion dépense
				$insertExpense = $pdo->prepare("
                    INSERT INTO expenses
                    (project_id, project_budget_line_id, amount, description, expense_date, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
				$insertExpense->execute([
					$projectId,
					$projectBudgetLineId,
					(float)$expenseAmount,
					$expenseDescription ?: null,
					$expenseDate ?: null
				]);
			}
		}
	}

	$pdo->commit();

	// Succès : alerte + redirection
	echo '<script>
        alert("Import effectué avec succès !");
        window.location.href = "../index.php";
    </script>';
	exit;
} catch (Exception $e) {
	$pdo->rollBack();
	echo '<script>
        alert("Erreur lors de l\'import : ' . addslashes($e->getMessage()) . '");
        window.history.back();
    </script>';
	exit;
}
