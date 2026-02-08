<?php

/**
 * Connexion à la base de données
 */
function getPDO(): PDO
{
    $host = 'localhost';
    $db   = 'orizonplus';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO($dsn, $user, $pass, $options);
}

/**
 * Helpers JSON
 */
function jsonSuccess($data = [], $message = '')
{
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

function jsonError($message = '', $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * 🔐 AUTH
 */
function login()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        jsonSuccess(['user' => $user], 'Login réussi');
    } else {
        jsonError('Identifiants invalides', 401);
    }
}

function logout()
{
    session_destroy();
    jsonSuccess([], 'Déconnexion réussie');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * 📁 PROJETS
 */
function getProjects()
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            IFNULL(SUM(pbl.allocated_amount), 0) AS allocated_amount,
            IFNULL(SUM(e.amount), 0) AS spent,
            (IFNULL(SUM(pbl.allocated_amount), 0) - IFNULL(SUM(e.amount), 0)) AS remaining
        FROM projects p
        LEFT JOIN project_budget_lines pbl ON pbl.project_id = p.id
        LEFT JOIN expenses e ON e.project_budget_line_id = pbl.id
        GROUP BY p.id, p.name
        ORDER BY p.name
    ");

    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir les montants en float pour l'affichage dans Vue
    foreach ($projects as &$project) {
        $project['allocated_amount'] = (float) $project['allocated_amount'];
        $project['spent'] = (float) $project['spent'];
        $project['remaining'] = (float) $project['remaining'];
    }

    jsonSuccess($projects);
}



function getProjectsData(PDO $pdo): array
{
    return $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll();
}

function createProject()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name'])) {
        jsonError('Nom de projet manquant');
    }

    $name = trim($data['name']);
    //  $globalBudget = floatval($data['global_budget'] ?? 0);
    $lines = $data['lines'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Création du projet
        $stmt = $pdo->prepare("INSERT INTO projects (name) VALUES ( ?)");
        $stmt->execute([$name]);
        $projectId = $pdo->lastInsertId();

        // 2. Insertion des lignes budgétaires
        if (!empty($lines)) {
            $stmtLine = $pdo->prepare("
                INSERT INTO project_budget_lines 
                (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ");

            foreach ($lines as $line) {
                if (
                    empty($line['budget_line_id']) ||
                    !isset($line['allocated_amount'])
                ) {
                    continue;
                }

                $stmtLine->execute([
                    $projectId,
                    (int) $line['budget_line_id'],
                    (float) $line['allocated_amount']
                ]);
            }
        }

        $pdo->commit();

        jsonSuccess(
            ['id' => $projectId],
            'Projet et lignes budgétaires créés'
        );
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur création projet : ' . $e->getMessage());
    }
}


function getProject()
{
    $pdo = getPDO();
    $id = $_GET['id'] ?? null;
    if (!$id) jsonError('ID de projet manquant');

    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if (!$project) jsonError('Projet non trouvé', 404);
    jsonSuccess($project);
}

function updateProject()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? null;
    $name = trim($data['name'] ?? '');
    $lines = $data['lines'] ?? []; // nouvelles lignes
    $updatedLines = $data['updated_lines'] ?? []; // lignes existantes

    if (!$id || !$name) {
        jsonError('Paramètres manquants');
    }

    try {
        $pdo->beginTransaction();

        // 1. Mise à jour du nom du projet
        $stmt = $pdo->prepare("UPDATE projects SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);

        // 2. Mise à jour des lignes existantes
        if (!empty($updatedLines)) {
            $stmtUpdate = $pdo->prepare("
                UPDATE project_budget_lines 
                SET allocated_amount = ? 
                WHERE id = ?
            ");

            foreach ($updatedLines as $line) {
                if (!isset($line['project_budget_line_id']) || !isset($line['allocated_amount'])) {
                    continue;
                }

                $stmtUpdate->execute([
                    (float) $line['allocated_amount'],
                    (int) $line['project_budget_line_id']
                ]);
            }
        }

        // 3. Insertion des nouvelles lignes
        if (!empty($lines)) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO project_budget_lines 
                (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ");

            foreach ($lines as $line) {
                if (empty($line['budget_line_id']) || !isset($line['allocated_amount'])) {
                    continue;
                }

                $stmtInsert->execute([
                    $id,
                    (int) $line['budget_line_id'],
                    (float) $line['allocated_amount']
                ]);
            }
        }

        $pdo->commit();
        jsonSuccess([], 'Projet et lignes budgétaires mis à jour');
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur mise à jour projet : ' . $e->getMessage());
    }
}


function deleteProject()
{
    $pdo = getPDO();
    $id = $_GET['id'] ?? null;
    if (!$id) jsonError('ID manquant');

    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    jsonSuccess([], 'Projet supprimé');
}

/**
 * 💼 LIGNES BUDGÉTAIRES
 */
function createBudgetLine()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $projectId = $data['project_id'] ?? null;
    $name = $data['name'] ?? '';
    if (!$projectId || !$name) jsonError('Paramètres manquants');

    $stmt = $pdo->prepare("INSERT INTO budget_lines (project_id, name) VALUES (?, ?)");
    $stmt->execute([$projectId, $name]);
    jsonSuccess(['id' => $pdo->lastInsertId()], 'Ligne budgétaire créée');
}

function updateBudgetLine()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    if (!$id || !$name) jsonError('Paramètres manquants');

    $stmt = $pdo->prepare("UPDATE budget_lines SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    jsonSuccess([], 'Ligne budgétaire mise à jour');
}

function getBudgetLines()
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM budget_lines");
    $stmt->execute();
    $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess($lines);
}


function getProjectDetails()
{
    $pdo = getPDO();
    $id = $_GET['id'] ?? null;
    if (!$id) jsonError('ID de projet manquant');

    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) jsonError('Projet non trouvé');

    $project['budget_lines'] = getBudgetLinesByProject($pdo, $id);
    jsonSuccess($project);
}

function deleteProjectBudgetLine()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonError('ID de ligne budgetaire manquant');
    }

    try {
        $pdo->beginTransaction();

        // Vérifier que la ligne existe
        $stmtCheck = $pdo->prepare("SELECT * FROM project_budget_lines WHERE id = ?");
        $stmtCheck->execute([$id]);
        $line = $stmtCheck->fetch();
        if (!$line) {
            $pdo->rollBack();
            jsonError('Ligne budgetaire introuvable', 404);
        }

        // Supprimer toutes les dépenses liées à cette ligne
        $stmtDeleteExpenses = $pdo->prepare("
            DELETE FROM expenses 
            WHERE project_budget_line_id = ?
        ");
        $stmtDeleteExpenses->execute([$id]);

        // Supprimer la ligne budgetaire
        $stmtDeleteLine = $pdo->prepare("DELETE FROM project_budget_lines WHERE id = ?");
        $stmtDeleteLine->execute([$id]);

        $pdo->commit();
        jsonSuccess([], 'Ligne budgetaire et dépenses associées supprimées');
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur suppression ligne : ' . $e->getMessage());
    }
}


function deleteBudgetLine()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonError('ID manquant');
    }

    try {
        // Vérifier que la ligne existe
        $stmtCheck = $pdo->prepare("SELECT * FROM budget_lines WHERE id = ?");
        $stmtCheck->execute([$id]);
        $line = $stmtCheck->fetch();
        if (!$line) {
            jsonError('Ligne budgétaire introuvable', 404);
        }

        // Supprimer toutes les lignes projet associées si tu veux aussi nettoyer project_budget_lines
        $stmtDeleteProjectLines = $pdo->prepare("
            DELETE FROM project_budget_lines WHERE budget_line_id = ?
        ");
        $stmtDeleteProjectLines->execute([$id]);

        // Supprimer la ligne budgétaire
        $stmtDelete = $pdo->prepare("DELETE FROM budget_lines WHERE id = ?");
        $stmtDelete->execute([$id]);

        jsonSuccess([], 'Ligne budgétaire et lignes associées supprimées');
    } catch (Exception $e) {
        jsonError('Erreur suppression ligne : ' . $e->getMessage());
    }
}



/**
 * 💰 DÉPENSES
 */
function getExpenses()
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT 
            e.id,
            e.project_id,
            p.name AS project_name,
            e.project_budget_line_id,
            bl.name AS budget_line_name,
            pbl.allocated_amount,
            -- Total déjà dépensé sur cette ligne budgétaire
            (SELECT IFNULL(SUM(e2.amount), 0) 
             FROM expenses e2 
             WHERE e2.project_budget_line_id = e.project_budget_line_id) AS spent,
            e.amount,
            e.expense_date,
            e.description,
            e.created_at
        FROM expenses e
        JOIN projects p 
            ON p.id = e.project_id
        JOIN project_budget_lines pbl 
            ON pbl.id = e.project_budget_line_id
        JOIN budget_lines bl 
            ON bl.id = pbl.budget_line_id
        ORDER BY e.created_at DESC
    ");

    $stmt->execute();
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess($expenses);
}


function createExpense()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        jsonError('JSON invalide', 400);
    }

    $projectId = $data['project_id'] ?? null;
    $lines = $data['lines'] ?? [];

    if (!$projectId || empty($lines)) {
        jsonError('Paramètres manquants', 400);
    }

    // Vérifier que la ligne budgétaire appartient bien au projet
    $checkPBL = $pdo->prepare("
        SELECT id 
        FROM project_budget_lines 
        WHERE id = ? AND project_id = ?
    ");

    $insertExpense = $pdo->prepare("
        INSERT INTO expenses (
            project_id,
            project_budget_line_id,
            amount,
            description,
            expense_date,
            created_at
        ) VALUES (?, ?, ?, ?, CURDATE(), NOW())
    ");

    foreach ($lines as $line) {

        if (
            empty($line['project_budget_line_id']) ||
            empty($line['amount'])
        ) {
            jsonError('Ligne de dépense invalide', 400);
        }

        // ✅ validation réelle
        $checkPBL->execute([
            $line['project_budget_line_id'],
            $projectId
        ]);

        if (!$checkPBL->fetch()) {
            jsonError('Ligne budgétaire introuvable pour ce projet', 400);
        }

        $insertExpense->execute([
            $projectId,
            $line['project_budget_line_id'],
            $line['amount'],
            $line['description'] ?? null
        ]);
    }

    jsonSuccess([], 'Dépense enregistrée avec succès');
}




function updateExpense()
{
    $pdo = getPDO();
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $amount = $data['amount'] ?? null;
    if (!$id || $amount === null) jsonError('Paramètres manquants');

    $stmt = $pdo->prepare("UPDATE expenses SET amount = ? WHERE id = ?");
    $stmt->execute([$amount, $id]);
    jsonSuccess([], 'Dépense mise à jour');
}

function deleteExpense()
{
    $pdo = getPDO();
    $id = $_GET['id'] ?? null;
    if (!$id) jsonError('ID manquant');

    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    jsonSuccess([], 'Dépense supprimée');
}


function getProjectBudgetLines()
{
    $pdo = getPDO();
    $project_id = $_GET['project_id'] ?? 0;

    if (!$project_id) jsonError('ID du projet manquant');

    $stmt = $pdo->prepare("
        SELECT 
            pbl.id AS project_budget_line_id,
            bl.name,
            pbl.allocated_amount,
            IFNULL(SUM(e.amount), 0) AS spent,
            (pbl.allocated_amount - IFNULL(SUM(e.amount), 0)) AS remaining
        FROM project_budget_lines pbl
        JOIN budget_lines bl ON bl.id = pbl.budget_line_id
        LEFT JOIN expenses e ON e.project_budget_line_id = pbl.id
        WHERE pbl.project_id = ?
        GROUP BY pbl.id, bl.name, pbl.allocated_amount
    ");

    $stmt->execute([$project_id]);
    jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
}




/**
 * 📊 RÉCAPS
 */
function getGlobalSummary()
{
    $pdo = getPDO();
    $total = $pdo->query("SELECT SUM(amount) as total FROM expenses")->fetchColumn();
    jsonSuccess(['total_expenses' => (float)$total]);
}

function getProjectsSummary()
{
    $pdo = getPDO();
    $stmt = $pdo->query("
        SELECT p.id, p.name, SUM(e.amount) AS total_expenses
        FROM projects p
        LEFT JOIN expenses e ON e.project_id = p.id
        GROUP BY p.id
    ");
    $summary = $stmt->fetchAll();
    jsonSuccess($summary);
}

/**
 * Fonctions internes déjà présentes
 */
function getLastExpenses(PDO $pdo, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT e.*, 
               p.name AS project_name, 
               b.name AS budget_name
        FROM expenses e
        JOIN projects p ON p.id = e.project_id
        JOIN budget_lines b ON b.id = e.budget_line_id
        ORDER BY e.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBudgetLinesByProject(PDO $pdo, int $projectId): array
{
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM budget_lines 
        WHERE project_id = ?
        ORDER BY name
    ");
    $stmt->execute([$projectId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function storeExpense(PDO $pdo, int $projectId, array $lines): void
{
    $stmt = $pdo->prepare("
        INSERT INTO expenses 
        (project_id, project_budget_line_id, amount, description, expense_date, created_at)
        VALUES (?, ?, ?, ?, CURDATE(), NOW())
    ");

    foreach ($lines as $line) {
        if (empty($line['project_budget_line_id']) || empty($line['amount'])) {
            throw new Exception('Ligne de dépense invalide');
        }

        $stmt->execute([
            $projectId,
            $line['project_budget_line_id'],
            $line['amount'],
            $line['description'] ?? null
        ]);
    }
}
