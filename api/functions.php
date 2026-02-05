<?php

/* =======================
   CONFIG & DB
======================= */

include 'config.php';
/* =======================
   HELPERS
======================= */

function jsonResponse($data = null, $message = "OK", $warning = false)
{
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
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

function getInput()
{
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        jsonError("Format JSON invalide");
    }
    return $data;
}

function requireField($data, $field)
{
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        jsonError("Champ manquant ou vide : $field");
    }
}

function isPositiveNumber($value)
{
    return is_numeric($value) && $value >= 0;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
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
    $user = $stmt->fetch();

    if (!$user || !password_verify($data['password'], $user['password'])) {
        jsonError("Identifiants incorrects", 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    jsonResponse([
        "id" => $user['id'],
        "name" => $user['name'],
        "role" => $user['role']
    ], "Connexion réussie");
}

function logout()
{
    session_destroy();
    jsonResponse(null, "Déconnexion réussie");
}

/* =======================
   PROJETS
======================= */

function getProjects()
{
    $sql = "
        SELECT p.id, p.name,
        COALESCE(SUM(bl.planned_amount),0) AS total_budget,
        COALESCE(SUM(e.amount),0) AS total_spent,
        COALESCE(SUM(bl.planned_amount),0) - COALESCE(SUM(e.amount),0) AS remaining
        FROM projects p
        LEFT JOIN budget_lines bl ON bl.project_id = p.id
        LEFT JOIN expenses e ON e.project_id = p.id
        GROUP BY p.id
    ";
    jsonResponse(db()->query($sql)->fetchAll());
}

function createProject()
{
    $data = getInput();
    requireField($data, 'name');

    db()->prepare("INSERT INTO projects (name, description) VALUES (?, ?)")
        ->execute([$data['name'], $data['description'] ?? null]);

    $projectId = db()->lastInsertId();

    if (!empty($data['budget_lines']) && is_array($data['budget_lines'])) {
        foreach ($data['budget_lines'] as $line) {
            requireField($line, 'name');
            requireField($line, 'planned_amount');

            if (!isPositiveNumber($line['planned_amount'])) {
                jsonError("Montant invalide pour la ligne : {$line['name']}");
            }

            db()->prepare("
                INSERT INTO budget_lines (project_id, name, planned_amount)
                VALUES (?, ?, ?)
            ")->execute([$projectId, $line['name'], $line['planned_amount']]);
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
    $project = $project->fetch();

    if (!$project) jsonError("Projet introuvable");

    $lines = db()->prepare("
        SELECT bl.*, 
        COALESCE(SUM(e.amount),0) AS spent,
        bl.planned_amount - COALESCE(SUM(e.amount),0) AS remaining
        FROM budget_lines bl
        LEFT JOIN expenses e ON e.budget_line_id = bl.id
        WHERE bl.project_id = ?
        GROUP BY bl.id
    ");
    $lines->execute([$id]);

    jsonResponse([
        "project" => $project,
        "budget_lines" => $lines->fetchAll()
    ]);
}

/* =======================
   DÉPENSES
======================= */

function createExpense()
{
    $data = getInput();
    foreach (['project_id', 'budget_line_id', 'expense_date', 'amount'] as $f) {
        requireField($data, $f);
    }

    if (!isPositiveNumber($data['amount'])) {
        jsonError("Montant invalide");
    }

    db()->prepare("
        INSERT INTO expenses (project_id, budget_line_id, expense_date, amount, description)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        $data['project_id'],
        $data['budget_line_id'],
        $data['expense_date'],
        $data['amount'],
        $data['description'] ?? null
    ]);

    // Vérifier dépassement
    $check = db()->prepare("
        SELECT bl.planned_amount - COALESCE(SUM(e.amount),0) AS remaining
        FROM budget_lines bl
        LEFT JOIN expenses e ON e.budget_line_id = bl.id
        WHERE bl.id = ?
        GROUP BY bl.id
    ");
    $check->execute([$data['budget_line_id']]);
    $remaining = $check->fetchColumn();

    jsonResponse(
        null,
        $remaining < 0 ? "Dépense enregistrée — budget dépassé" : "Dépense enregistrée",
        $remaining < 0
    );
}
