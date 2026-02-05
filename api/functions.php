<?php

/* =======================
   CONFIG & DB
======================= */
require_once __DIR__ . '/config.php';

/* =======================
   HELPERS
======================= */

function jsonResponse($data = null, $message = "OK", $warning = false)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => true,
        "warning" => $warning,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

function jsonError($message, $code = 400)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

function getInput()
{
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        jsonError("Format JSON invalide");
    }
    return $data;
}

function requireField($data, $field)
{
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        jsonError("Champ manquant : $field");
    }
}

function isPositiveNumber($value)
{
    return is_numeric($value) && $value >= 0;
}

/* =======================
   AUTH
======================= */

function login()
{
    $data = getInput();
    requireField($data, 'name');
    requireField($data, 'password');

    $stmt = db()->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->execute([$data['name']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        jsonError("Identifiants incorrects", 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    jsonResponse([
        "id" => $user['id'],
        "name" => $user['name']
    ], "Connexion réussie");
}

function logout()
{
    session_destroy();
    jsonResponse(null, "Déconnexion réussie");
}

/* =======================
   BUDGET LINES (CATALOGUE)
======================= */

function getBudgetLines()
{
    $stmt = db()->query("
        SELECT id, name
        FROM budget_lines
        ORDER BY name ASC
    ");

    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function createBudgetLine()
{
    $data = getInput();
    requireField($data, 'name');

    db()->prepare("
        INSERT INTO budget_lines (name)
        VALUES (?)
    ")->execute([$data['name']]);

    jsonResponse(null, "Ligne budgétaire créée");
}

function updateBudgetLine()
{
    $data = getInput();
    requireField($data, 'id');
    requireField($data, 'name');

    db()->prepare("
        UPDATE budget_lines
        SET name = ?
        WHERE id = ?
    ")->execute([$data['name'], $data['id']]);

    jsonResponse(null, "Ligne budgétaire mise à jour");
}

function deleteBudgetLine()
{
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) jsonError("ID invalide");

    db()->prepare("DELETE FROM budget_lines WHERE id = ?")->execute([$id]);
    jsonResponse(null, "Ligne budgétaire supprimée");
}

/* =======================
   PROJECTS
======================= */

function getProjects()
{
    $sql = "
        SELECT 
            p.id,
            p.name,
            p.total_budget,
            COALESCE(SUM(e.amount),0) AS total_spent,
            p.total_budget - COALESCE(SUM(e.amount),0) AS remaining
        FROM projects p
        LEFT JOIN expenses e ON e.project_id = p.id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ";
    jsonResponse(db()->query($sql)->fetchAll(PDO::FETCH_ASSOC));
}

function createProject()
{
    $data = getInput();
    requireField($data, 'name');
    requireField($data, 'total_budget');

    if (!isPositiveNumber($data['total_budget'])) {
        jsonError("Budget total invalide");
    }

    db()->prepare("
        INSERT INTO projects (name, description, total_budget)
        VALUES (?, ?, ?)
    ")->execute([
        $data['name'],
        $data['description'] ?? null,
        $data['total_budget']
    ]);

    $projectId = db()->lastInsertId();

    if (!empty($data['lines'])) {
        foreach ($data['lines'] as $line) {
            requireField($line, 'budget_line_id');
            requireField($line, 'allocated_amount');

            db()->prepare("
                INSERT INTO project_budget_lines (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ")->execute([
                $projectId,
                $line['budget_line_id'],
                $line['allocated_amount']
            ]);
        }
    }

    jsonResponse(["project_id" => $projectId], "Projet créé");
}

function getProject()
{
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) jsonError("ID projet invalide");

    $project = db()->prepare("SELECT * FROM projects WHERE id = ?");
    $project->execute([$id]);
    $project = $project->fetch(PDO::FETCH_ASSOC);

    if (!$project) jsonError("Projet introuvable");

    $lines = db()->prepare("
        SELECT 
            pbl.id,
            bl.name,
            pbl.allocated_amount,
            COALESCE(SUM(e.amount),0) AS spent,
            pbl.allocated_amount - COALESCE(SUM(e.amount),0) AS remaining
        FROM project_budget_lines pbl
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        LEFT JOIN expenses e ON e.project_budget_line_id = pbl.id
        WHERE pbl.project_id = ?
        GROUP BY pbl.id
    ");
    $lines->execute([$id]);

    jsonResponse([
        "project" => $project,
        "budget_lines" => $lines->fetchAll(PDO::FETCH_ASSOC)
    ]);
}

function updateProject()
{
    $data = getInput();
    requireField($data, 'id');
    requireField($data, 'name');
    requireField($data, 'total_budget');

    db()->prepare("
        UPDATE projects
        SET name = ?, description = ?, total_budget = ?
        WHERE id = ?
    ")->execute([
        $data['name'],
        $data['description'] ?? null,
        $data['total_budget'],
        $data['id']
    ]);

    jsonResponse(null, "Projet mis à jour");
}

function deleteProject()
{
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) jsonError("ID invalide");

    db()->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
    jsonResponse(null, "Projet supprimé");
}

/* =======================
   EXPENSES
======================= */

function getExpenses()
{
    $sql = "
        SELECT 
            e.id,
            p.name AS project_name,
            bl.name AS line_name,
            e.amount,
            e.expense_date,
            e.description
        FROM expenses e
        JOIN projects p ON p.id = e.project_id
        JOIN project_budget_lines pbl ON pbl.id = e.project_budget_line_id
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        ORDER BY e.expense_date DESC
    ";
    jsonResponse(db()->query($sql)->fetchAll(PDO::FETCH_ASSOC));
}

function createExpense()
{
    $data = getInput();
    foreach (['project_id', 'project_budget_line_id', 'expense_date', 'amount'] as $f) {
        requireField($data, $f);
    }

    if (!isPositiveNumber($data['amount'])) {
        jsonError("Montant invalide");
    }

    db()->prepare("
        INSERT INTO expenses (project_id, project_budget_line_id, amount, expense_date, description)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        $data['project_id'],
        $data['project_budget_line_id'],
        $data['amount'],
        $data['expense_date'],
        $data['description'] ?? null
    ]);

    jsonResponse(null, "Dépense enregistrée");
}

function updateExpense()
{
    $data = getInput();
    requireField($data, 'id');

    db()->prepare("
        UPDATE expenses
        SET amount = ?, expense_date = ?, description = ?
        WHERE id = ?
    ")->execute([
        $data['amount'],
        $data['expense_date'],
        $data['description'] ?? null,
        $data['id']
    ]);

    jsonResponse(null, "Dépense mise à jour");
}

function deleteExpense()
{
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) jsonError("ID invalide");

    db()->prepare("DELETE FROM expenses WHERE id = ?")->execute([$id]);
    jsonResponse(null, "Dépense supprimée");
}

/* =======================
   SUMMARY
======================= */

function getGlobalSummary()
{
    $sql = "
        SELECT 
            SUM(total_budget) AS total_budget,
            (SELECT SUM(amount) FROM expenses) AS total_spent
        FROM projects
    ";
    jsonResponse(db()->query($sql)->fetch(PDO::FETCH_ASSOC));
}

function getProjectsSummary()
{
    $sql = "
        SELECT 
            p.id,
            p.name,
            p.total_budget,
            COALESCE(SUM(e.amount),0) AS spent,
            p.total_budget - COALESCE(SUM(e.amount),0) AS remaining
        FROM projects p
        LEFT JOIN expenses e ON e.project_id = p.id
        GROUP BY p.id
    ";
    jsonResponse(db()->query($sql)->fetchAll(PDO::FETCH_ASSOC));
}

function getProjectDetails()
{
    // Récupérer l'ID du projet depuis l'URL
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        jsonError("ID projet invalide");
    }

    // Récupérer les informations principales du projet
    $stmt = db()->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        jsonError("Projet introuvable");
    }

    // Récupérer les lignes budgétaires associées au projet avec leur nom et montant alloué
    $stmt2 = db()->prepare("
        SELECT 
            pbl.id AS budget_line_id,
            pbl.allocated_amount,
            bl.name
        FROM project_budget_lines pbl
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        WHERE pbl.project_id = ?
    ");
    $stmt2->execute([$id]);
    $lines = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total dépensé pour le projet
    $stmt3 = db()->prepare("SELECT COALESCE(SUM(amount),0) AS total_spent FROM expenses WHERE project_id = ?");
    $stmt3->execute([$id]);
    $totalSpent = $stmt3->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;

    // Ajouter total dépensé et reste dans les données du projet
    $project['total_spent'] = $totalSpent;
    $project['remaining'] = $project['total_budget'] - $totalSpent;

    // Renvoyer la réponse JSON complète
    jsonResponse([
        "id" => $project['id'],
        "name" => $project['name'],
        "description" => $project['description'],
        "total_budget" => $project['total_budget'],
        "total_spent" => $project['total_spent'],
        "remaining" => $project['remaining'],
        "lines" => $lines
    ]);
}
