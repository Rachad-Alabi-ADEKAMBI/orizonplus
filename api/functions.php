<?php

use PhpOffice\PhpSpreadsheet\Worksheet\Validations;

session_start();

/**
 * Connexion à la base de données
 */
include 'db.php';

function verifyInput($input)
{
    if (!isset($input)) {
        throw new Exception("Paramètre manquant.");
    }

    // Supprime les espaces avant/après
    $input = trim($input);

    // Supprime les balises HTML
    $input = strip_tags($input);

    // Supprime les caractères spéciaux dangereux
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

    // Vérifie que c'est un entier positif
    if (!ctype_digit($input) || intval($input) <= 0) {
        throw new Exception("Paramètre invalide.");
    }

    return intval($input);
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
    session_start();

    try {
        $pdo = getPDO();

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonError('JSON invalide', 400);
        }

        $name = trim($data['name'] ?? '');
        $password = $data['password'] ?? '';

        if ($name === '' || $password === '') {
            jsonError('Champs "name" et "password" obligatoires', 400);
        }

        // Vérifier l'utilisateur et son statut
        $stmt = $pdo->prepare("SELECT id, name, password, role, status FROM users WHERE name = ?");
        $stmt->execute([$name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['status'] !== 'Actif') {
            jsonError('Ce compte n\'existe pas', 404);
        }

        if (!password_verify($password, $user['password'])) {
            jsonError('Identifiants invalides', 401);
        }

        // Stocker les infos en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        jsonSuccess(['user_id' => $user['id']], 'Login réussi');
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur base de données : ' . $e->getMessage()
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur interne : ' . $e->getMessage()
        ]);
        exit;
    }
}





function logout()
{
    session_destroy();     // Détruire la session
    header('Location: ../login.php'); // Redirection vers la page login
    exit;                  // Arrêter l'exécution du script
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

    try {
        $stmt = $pdo->prepare("
    SELECT 
        p.*,
        IFNULL(SUM(DISTINCT pbl.allocated_amount), 0) AS allocated_amount,
        IFNULL(SUM(e.amount), 0) AS spent,
        (
            IFNULL(SUM(DISTINCT pbl.allocated_amount), 0) 
            - IFNULL(SUM(e.amount), 0)
        ) AS remaining
    FROM projects p
    LEFT JOIN project_budget_lines pbl 
        ON pbl.project_id = p.id
    LEFT JOIN expenses e 
        ON e.project_budget_line_id = pbl.id
    GROUP BY p.id
    ORDER BY p.name
");
        $stmt->execute();

        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // S'assurer que $projects est un tableau
        if (!is_array($projects)) {
            $projects = [];
        }

        foreach ($projects as &$project) {

            // S'assurer que les montants sont des floats
            $project['allocated_amount'] = isset($project['allocated_amount']) ? (float) $project['allocated_amount'] : 0.0;
            $project['spent'] = isset($project['spent']) ? (float) $project['spent'] : 0.0;
            $project['remaining'] = isset($project['remaining']) ? (float) $project['remaining'] : 0.0;

            // Convertir le JSON documents en tableau, en cas d'erreur retourner tableau vide
            $decoded = json_decode($project['documents'], true);
            $project['documents'] = is_array($decoded) ? $decoded : [];
        }

        // Envoyer la réponse JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $projects
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la récupération des projets : ' . $e->getMessage()
        ]);
        exit;
    }
}



function getProjectsData(PDO $pdo): array
{
    return $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll();
}
function createProject()
{
    try {
        $pdo = getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $name = trim($_POST['name'] ?? '');
        $description = $_POST['description'] ?? null;
        $department = $_POST['department'] ?? null;
        $location = $_POST['location'] ?? null;
        $date_of_creation = $_POST['date_of_creation'] ?? null;
        $status = "Déverrouillé";

        $linesRaw = $_POST['lines'] ?? '[]';
        $lines = json_decode($linesRaw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonError('Erreur JSON lines : ' . json_last_error_msg());
        }

        if ($name === '') {
            jsonError('Nom de projet manquant');
        }

        $contract_number        = $_POST['contract_number'] !== '' ? $_POST['contract_number'] : null;
        $contract_amount_ht     = $_POST['contract_amount_ht'] !== '' ? (float)$_POST['contract_amount_ht'] : null;
        $execution_budget_ht    = $_POST['execution_budget_ht'] !== '' ? (float)$_POST['execution_budget_ht'] : null;
        $collected_amount_ht    = $_POST['collected_amount_ht'] !== '' ? (float)$_POST['collected_amount_ht'] : null;

        // Taux d'exécution physique
        $execution_rate = isset($_POST['execution_rate']) && $_POST['execution_rate'] !== ''
            ? (float)$_POST['execution_rate']
            : null;

        $pdo->beginTransaction();

        /* ================= UPLOAD ================= */

        $uploadedFiles = [];
        if (!empty($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                throw new Exception("Impossible de créer le dossier upload");
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {

                if ($_FILES['documents']['error'][$key] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur upload fichier : " . $_FILES['documents']['name'][$key]);
                }

                $originalName = $_FILES['documents']['name'][$key];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

                if (!in_array($extension, $allowed)) {
                    throw new Exception("Extension non autorisée : " . $originalName);
                }

                $newName = uniqid('doc_') . '.' . $extension;
                $destination = $uploadDir . $newName;

                if (!move_uploaded_file($tmpName, $destination)) {
                    throw new Exception("Échec déplacement fichier : " . $originalName);
                }

                $uploadedFiles[] = $newName;
            }
        }

        /* ================= INSERT PROJECT ================= */

        $stmt = $pdo->prepare("
            INSERT INTO projects
            (name, description, department, location, documents, project_status, date_of_creation,
             contract_number, contract_amount_ht, execution_budget_ht, collected_amount_ht,
             execution_rate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt->execute([
            $name,
            $description,
            $department,
            $location,
            json_encode($uploadedFiles),
            $status,
            $date_of_creation,
            $contract_number,
            $contract_amount_ht,
            $execution_budget_ht,
            $collected_amount_ht,
            $execution_rate
        ])) {
            throw new Exception("Erreur insertion projet : " . implode(' | ', $stmt->errorInfo()));
        }

        $projectId = $pdo->lastInsertId();

        /* ================= INSERT LINES ================= */

        if (!empty($lines) && is_array($lines)) {

            $stmtLine = $pdo->prepare("
                INSERT INTO project_budget_lines
                (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ");

            foreach ($lines as $line) {

                if (empty($line['budget_line_id']) || !isset($line['allocated_amount'])) {
                    continue;
                }

                if (!$stmtLine->execute([
                    $projectId,
                    (int)$line['budget_line_id'],
                    (float)$line['allocated_amount']
                ])) {
                    throw new Exception("Erreur insertion ligne : " . implode(' | ', $stmtLine->errorInfo()));
                }
            }
        }

        $pdo->commit();

        jsonSuccess(
            ['id' => $projectId],
            'Projet créé avec succès'
        );
    } catch (Throwable $e) {

        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        jsonError(
            'Erreur création projet : ' . $e->getMessage()
        );
    }
}


function lockProject()
{
    try {
        $pdo = getPDO();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            jsonError('ID du projet manquant');
        }

        $stmt = $pdo->prepare("
            UPDATE projects 
            SET project_status = 'Verrouillé'
            WHERE id = ?
        ");

        $stmt->execute([(int)$data['id']]);

        if ($stmt->rowCount() === 0) {
            jsonError('Projet introuvable');
        }

        jsonSuccess([], 'Projet clôturé avec succès');
    } catch (Exception $e) {
        jsonError('Erreur lors du verrouillage du projet');
    }
}

function unlockProject()
{
    try {
        $pdo = getPDO();

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            jsonError('ID du projet manquant');
        }

        $stmt = $pdo->prepare("
            UPDATE projects 
            SET project_status = 'Déverrouillé'
            WHERE id = ?
        ");

        $stmt->execute([(int)$data['id']]);

        if ($stmt->rowCount() === 0) {
            jsonError('Projet introuvable');
        }

        jsonSuccess([], 'Projet ouvert avec succès');
    } catch (Exception $e) {
        jsonError('Erreur lors du déverrouillage du projet');
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
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $pdo = getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id                  = $_POST['id'] ?? null;
        $name                = trim($_POST['name'] ?? '');
        $description         = $_POST['description'] ?? null;
        $department          = $_POST['department'] ?? null;
        $location            = $_POST['location'] ?? null;
        $date_of_creation    = $_POST['date_of_creation'] ?? null;
        $observation         = $_POST['observation'] ?? null;
        $contract_number     = $_POST['contract_number'] !== '' ? $_POST['contract_number'] : null;
        $contract_amount_ht  = $_POST['contract_amount_ht'] !== '' ? (float)$_POST['contract_amount_ht'] : null;
        $execution_budget_ht = $_POST['execution_budget_ht'] !== '' ? (float)$_POST['execution_budget_ht'] : null;
        $collected_amount_ht = $_POST['collected_amount_ht'] !== '' ? (float)$_POST['collected_amount_ht'] : null;

        // Taux d'exécution physique
        $execution_rate = isset($_POST['execution_rate']) && $_POST['execution_rate'] !== ''
            ? (float)$_POST['execution_rate']
            : null;

        $linesRaw     = $_POST['lines'] ?? '[]';
        $lines        = json_decode($linesRaw, true);
        $updatedLines = json_decode($_POST['updated_lines'] ?? '[]', true);
        $deletedLines = json_decode($_POST['deleted_lines'] ?? '[]', true);
        $keptDocuments = json_decode($_POST['existing_documents'] ?? '[]', true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonError('Erreur JSON lines : ' . json_last_error_msg());
        }

        if (!$id || $name === '') {
            jsonError('Paramètres manquants (ID ou nom)');
        }

        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldProject = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$oldProject) {
            jsonError('Projet introuvable');
        }

        $changes = [];

        /*
        |--------------------------------------------------------------------------
        | 📁 Gestion documents
        |--------------------------------------------------------------------------
        */

        $oldDocuments  = json_decode($oldProject['documents'] ?? '[]', true);
        $uploadDir     = __DIR__ . '/../images/';

        $keptDocuments = array_intersect($oldDocuments, $keptDocuments ?? []);

        foreach ($oldDocuments as $oldFile) {
            if (!in_array($oldFile, $keptDocuments)) {
                $filePath = $uploadDir . $oldFile;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $changes[] = "Document supprimé : {$oldFile}";
            }
        }

        $finalDocuments = $keptDocuments;

        if (!empty($_FILES['documents']['name'][0])) {

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {

                if ($_FILES['documents']['error'][$key] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur upload fichier : " . ($_FILES['documents']['name'][$key] ?? ''));
                }

                $originalName = $_FILES['documents']['name'][$key];
                $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed      = ['jpg', 'jpeg', 'png', 'pdf'];

                if (!in_array($extension, $allowed)) {
                    throw new Exception("Extension non autorisée : " . $originalName);
                }

                $newName     = uniqid('doc_') . '.' . $extension;
                $destination = $uploadDir . $newName;

                if (!move_uploaded_file($tmpName, $destination)) {
                    throw new Exception("Échec déplacement fichier : " . $originalName);
                }

                $finalDocuments[] = $newName;
                $changes[]        = "Document ajouté : {$newName}";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🔄 Comparaison champs principaux
        |--------------------------------------------------------------------------
        */

        $fieldsToCheck = [
            'name'                => $name,
            'description'         => $description,
            'department'          => $department,
            'location'            => $location,
            'date_of_creation'    => $date_of_creation,
            'observation'         => $observation,
            'contract_number'     => $contract_number,
            'contract_amount_ht'  => $contract_amount_ht,
            'execution_budget_ht' => $execution_budget_ht,
            'collected_amount_ht' => $collected_amount_ht,
            'execution_rate'      => $execution_rate,
        ];

        foreach ($fieldsToCheck as $field => $newValue) {
            $oldValue = $oldProject[$field] ?? null;
            if ((string)$oldValue !== (string)$newValue) {
                $changes[] = ucfirst(str_replace('_', ' ', $field))
                    . " modifié : \"" . ($oldValue ?? '') . "\" → \"" . ($newValue ?? '') . "\"";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🗂 Mise à jour projet
        |--------------------------------------------------------------------------
        */

        $stmtUpdateProject = $pdo->prepare("
            UPDATE projects SET
                name                 = ?,
                description          = ?,
                department           = ?,
                location             = ?,
                documents            = ?,
                date_of_creation     = ?,
                observation          = ?,
                contract_number      = ?,
                contract_amount_ht   = ?,
                execution_budget_ht  = ?,
                collected_amount_ht  = ?,
                execution_rate       = ?
            WHERE id = ?
        ");

        $stmtUpdateProject->execute([
            $name,
            $description,
            $department,
            $location,
            json_encode($finalDocuments),
            $date_of_creation,
            $observation,
            $contract_number,
            $contract_amount_ht,
            $execution_budget_ht,
            $collected_amount_ht,
            $execution_rate,
            $id
        ]);

        /*
        |--------------------------------------------------------------------------
        | 💰 Mise à jour lignes existantes
        |--------------------------------------------------------------------------
        */

        if (!empty($updatedLines)) {

            $stmtUpdate = $pdo->prepare("
                UPDATE project_budget_lines
                SET allocated_amount = ?
                WHERE id = ?
            ");

            foreach ($updatedLines as $line) {

                if (empty($line['project_budget_line_id']) || !isset($line['allocated_amount'])) continue;

                $stmtLine = $pdo->prepare("
                    SELECT pbl.allocated_amount, bl.name
                    FROM project_budget_lines pbl
                    JOIN budget_lines bl ON bl.id = pbl.budget_line_id
                    WHERE pbl.id = ?
                ");
                $stmtLine->execute([(int)$line['project_budget_line_id']]);
                $lineData = $stmtLine->fetch(PDO::FETCH_ASSOC);

                if (!$lineData) continue;

                if ((float)$lineData['allocated_amount'] !== (float)$line['allocated_amount']) {
                    $changes[] = "Ligne \"{$lineData['name']}\" modifiée : "
                        . $lineData['allocated_amount'] . " → " . $line['allocated_amount'];
                }

                $stmtUpdate->execute([
                    (float)$line['allocated_amount'],
                    (int)$line['project_budget_line_id']
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ➕ Nouvelles lignes
        |--------------------------------------------------------------------------
        */

        if (!empty($lines)) {

            $stmtInsert = $pdo->prepare("
                INSERT INTO project_budget_lines
                (project_id, budget_line_id, allocated_amount)
                VALUES (?, ?, ?)
            ");

            foreach ($lines as $line) {

                if (empty($line['budget_line_id']) || !isset($line['allocated_amount'])) continue;

                $stmtName = $pdo->prepare("SELECT name FROM budget_lines WHERE id = ?");
                $stmtName->execute([(int)$line['budget_line_id']]);
                $lineName = $stmtName->fetchColumn();

                $changes[] = "Ligne ajoutée : \"{$lineName}\" (" . $line['allocated_amount'] . ")";

                $stmtInsert->execute([
                    $id,
                    (int)$line['budget_line_id'],
                    (float)$line['allocated_amount']
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ❌ Suppression lignes
        |--------------------------------------------------------------------------
        */

        if (!empty($deletedLines)) {

            $stmtDelete = $pdo->prepare("DELETE FROM project_budget_lines WHERE id = ?");

            foreach ($deletedLines as $lineId) {

                $stmtName = $pdo->prepare("
                    SELECT bl.name
                    FROM project_budget_lines pbl
                    JOIN budget_lines bl ON bl.id = pbl.budget_line_id
                    WHERE pbl.id = ?
                ");
                $stmtName->execute([(int)$lineId]);
                $lineName = $stmtName->fetchColumn();

                if ($lineName) {
                    $changes[] = "Ligne supprimée : \"{$lineName}\"";
                }

                $stmtDelete->execute([(int)$lineId]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🔔 Notification
        |--------------------------------------------------------------------------
        */

        if (!empty($changes)) {
            $notificationText = "Projet modifié : \"{$name}\" (ID {$id}). Détails des changements :\n\n"
                . implode("\n", $changes);
            createNotification($notificationText, 1, 'admin');
        }

        $pdo->commit();

        jsonSuccess([], 'Projet mis à jour avec succès');
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonError('Erreur mise à jour projet : ' . $e->getMessage());
    }
}

function createExpense()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = getPDO();

    $currentUserId   = $_SESSION['user_id']   ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;

    if (!$currentUserId || !$currentUserName) {
        jsonError('Utilisateur non authentifié', 401);
    }

    $projectId           = $_POST['project_id']            ?? null;
    $projectBudgetLineId = $_POST['project_budget_line_id'] ?? null;
    $amount              = $_POST['amount']                 ?? null;
    $expenseDate         = $_POST['expense_date']           ?? null;
    $description         = $_POST['description']            ?? null;

    // Montant payé (optionnel)
    $paidAmount = isset($_POST['paid_amount']) && $_POST['paid_amount'] !== ''
        ? (float) $_POST['paid_amount']
        : null;

    // ── Fournisseur (optionnel) ──────────────────────────────────────────────
    $supplierId = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' && (int)$_POST['supplier_id'] > 0
        ? (int) $_POST['supplier_id']
        : null;

    if (!$projectId || !$projectBudgetLineId || !$amount || !$expenseDate) {
        jsonError('Paramètres manquants', 400);
    }

    // ── Date au format West Africa / Bénin (UTC+1) ──────────────────────────
    try {
        $tz          = new DateTimeZone('Africa/Porto-Novo'); // UTC+1 – Bénin
        $dtInput     = new DateTime($expenseDate);
        $dtInput->setTimezone($tz);
        $expenseDate = $dtInput->format('Y-m-d');
    } catch (Exception $e) {
        jsonError('Format de date invalide', 400);
    }

    try {
        $pdo->beginTransaction();

        // 🔹 Projet
        $stmtProject = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
        $stmtProject->execute([$projectId]);
        $project = $stmtProject->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            jsonError('Projet introuvable', 400);
        }

        $projectName = $project['name'];

        // 🔹 Ligne budgétaire projet
        $stmtPBL = $pdo->prepare("SELECT budget_line_id FROM project_budget_lines WHERE id = ? AND project_id = ?");
        $stmtPBL->execute([$projectBudgetLineId, $projectId]);
        $pbl = $stmtPBL->fetch(PDO::FETCH_ASSOC);

        if (!$pbl) {
            jsonError('Ligne budgétaire introuvable pour ce projet', 400);
        }

        $budgetLineId = $pbl['budget_line_id'];

        // 🔹 Nom ligne budgétaire
        $stmtBudget = $pdo->prepare("SELECT name FROM budget_lines WHERE id = ?");
        $stmtBudget->execute([$budgetLineId]);
        $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);

        if (!$budget) {
            jsonError('Nom de ligne budgétaire introuvable', 400);
        }

        $budgetName = $budget['name'];

        // 🔹 Nom du fournisseur (pour la notification)
        $supplierName = null;
        if ($supplierId) {
            $stmtSupplier = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
            $stmtSupplier->execute([$supplierId]);
            $supplier = $stmtSupplier->fetch(PDO::FETCH_ASSOC);
            $supplierName = $supplier['name'] ?? null;
        }

        // 🔹 Gestion multi-documents (max 15)
        $uploadedFiles = [];

        if (!empty($_FILES['documents']['name'][0])) {

            if (count($_FILES['documents']['name']) > 15) {
                jsonError('Maximum 15 documents autorisés', 400);
            }

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            foreach ($_FILES['documents']['name'] as $index => $originalName) {

                if ($_FILES['documents']['error'][$index] !== UPLOAD_ERR_OK) {
                    jsonError('Erreur lors de l\'upload d\'un document', 400);
                }

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowed)) {
                    jsonError('Format de document non autorisé', 400);
                }

                $newFileName = uniqid() . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (!move_uploaded_file($_FILES['documents']['tmp_name'][$index], $destination)) {
                    jsonError('Impossible d\'enregistrer un document', 500);
                }

                $uploadedFiles[] = $newFileName;
            }
        }

        // 🔹 Insertion avec supplier_id + user_id
        $insertSqlWithUserId = "
            INSERT INTO expenses (
                project_id, project_budget_line_id, amount, paid_amount, description,
                expense_date, documents, supplier_id, user_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";
        $insertSqlWithoutUserId = "
            INSERT INTO expenses (
                project_id, project_budget_line_id, amount, paid_amount, description,
                expense_date, documents, supplier_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        try {
            $insertExpense = $pdo->prepare($insertSqlWithUserId);
            $insertExpense->execute([
                $projectId,
                $projectBudgetLineId,
                $amount,
                $paidAmount,
                $description,
                $expenseDate,
                json_encode($uploadedFiles),
                $supplierId,
                $currentUserId,
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'user_id') !== false || strpos($e->getMessage(), 'created_by') !== false) {
                $insertExpense = $pdo->prepare($insertSqlWithoutUserId);
                $insertExpense->execute([
                    $projectId,
                    $projectBudgetLineId,
                    $amount,
                    $paidAmount,
                    $description,
                    $expenseDate,
                    json_encode($uploadedFiles),
                    $supplierId,
                ]);
            } else {
                throw $e;
            }
        }

        $expenseId = $pdo->lastInsertId();

        // 🔹 Notification
        $notificationText = "Nouvelle dépense enregistrée par {$currentUserName} pour le projet \"{$projectName}\":\n"
            . "Ligne budgétaire: {$budgetName}\n"
            . "Montant: {$amount} FCFA\n"
            . ($paidAmount  !== null  ? "Montant payé: {$paidAmount} FCFA\n"  : '')
            . ($supplierName !== null  ? "Fournisseur: {$supplierName}\n"       : '')
            . ($description           ? "Description: {$description}\n"         : '')
            . (!empty($uploadedFiles) ? "Documents joints: " . count($uploadedFiles) : '');

        createNotification($notificationText, 1, 'admin');

        $pdo->commit();

        jsonSuccess(['expense_id' => $expenseId], 'Dépense enregistrée avec succès');
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        jsonError('Erreur interne du serveur : ' . $e->getMessage(), 500);
    }
}

function updateExpenseDocuments()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = getPDO();

    $currentUserId   = $_SESSION['user_id'] ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;
    $expenseId       = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$currentUserId) {
        jsonError('Utilisateur non connecté', 401);
    }

    if (!$expenseId) {
        jsonError('ID de la dépense manquant', 400);
    }

    // documents existants envoyés par le front après suppression
    $existingDocuments = isset($_POST['documents']) ? json_decode($_POST['documents'], true) : [];

    if (!is_array($existingDocuments)) {
        $existingDocuments = [];
    }

    try {
        $pdo->beginTransaction();

        // 🔹 Récupérer la dépense existante (user_id optionnel si colonne absente)
        try {
            $stmt = $pdo->prepare("SELECT documents, user_id FROM expenses WHERE id = ?");
            $stmt->execute([$expenseId]);
            $expense = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'user_id') !== false || strpos($e->getMessage(), 'created_by') !== false) {
                $stmt = $pdo->prepare("SELECT documents FROM expenses WHERE id = ?");
                $stmt->execute([$expenseId]);
                $expense = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($expense) {
                    $expense['user_id'] = null;
                }
            } else {
                throw $e;
            }
        }

        if (!$expense) {
            jsonError('Dépense introuvable', 404);
        }

        $currentUserRole = $_SESSION['user_role'] ?? null;
        $isAdmin = ($currentUserRole === 'admin');
        if (!$isAdmin && isset($expense['user_id']) && $expense['user_id'] !== null && (int) $expense['user_id'] !== (int) $currentUserId) {
            jsonError('Vous ne pouvez modifier que les dépenses que vous avez insérées.', 403);
        }

        $oldDocuments = json_decode($expense['documents'] ?? '[]', true);
        if (!is_array($oldDocuments)) $oldDocuments = [];

        // 🔹 Supprimer physiquement les documents retirés
        $documentsToDelete = array_diff($oldDocuments, $existingDocuments);
        $uploadDir = __DIR__ . '/../images/';
        foreach ($documentsToDelete as $doc) {
            $path = $uploadDir . $doc;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $uploadedFiles = [];

        // 🔹 Ajouter de nouveaux fichiers si fournis
        if (!empty($_FILES['documents']['name'][0])) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            foreach ($_FILES['documents']['name'] as $index => $originalName) {
                if ($_FILES['documents']['error'][$index] !== UPLOAD_ERR_OK) {
                    jsonError('Erreur lors de l\'upload d\'un document', 400);
                }

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowed)) {
                    jsonError('Format de document non autorisé', 400);
                }

                $newFileName = uniqid() . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (!move_uploaded_file($_FILES['documents']['tmp_name'][$index], $destination)) {
                    jsonError('Impossible d\'enregistrer un document', 500);
                }

                $uploadedFiles[] = $newFileName;
            }
        }

        // 🔹 Fusionner les documents existants + nouveaux
        $allDocuments = array_merge($existingDocuments, $uploadedFiles);

        // 🔹 Limite 15 documents
        if (count($allDocuments) > 15) {
            jsonError('Maximum 15 documents autorisés par dépense', 400);
        }

        // 🔹 Mise à jour
        $stmtUpdate = $pdo->prepare("
            UPDATE expenses
            SET documents = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([json_encode($allDocuments), $expenseId]);

        // 🔹 Récupérer infos de la dépense et projet
        $stmtInfo = $pdo->prepare("
    SELECT e.description AS expense_desc, p.name AS project_name
    FROM expenses e
    JOIN projects p ON e.project_id = p.id
    WHERE e.id = ?
");
        $stmtInfo->execute([$expenseId]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        $projectName = $info['project_name'] ?? "Projet inconnu";
        $expenseDesc = $info['expense_desc'] ?? "Dépense sans description";

        $user_id = 1;
        $user_name = "Admin";

        // 🔹 Notification
        $notificationText = "Documents de la dépense \"{$expenseDesc}\" du projet \"{$projectName}\" mis à jour par {$currentUserName}. "
            . "Total documents : " . count($allDocuments);

        createNotification($notificationText, $user_id, $user_name);

        $pdo->commit();

        jsonSuccess(['documents' => $allDocuments], 'Documents mis à jour avec succès');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonError('Erreur serveur : ' . $e->getMessage(), 500);
    }
}

function getSuppliers()
{
    try {
        $pdo = getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id, name FROM suppliers ORDER BY name ASC");
        $stmt->execute();

        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess($suppliers, 'Fournisseurs récupérés avec succès');
    } catch (Throwable $e) {
        jsonError('Erreur récupération fournisseurs : ' . $e->getMessage(), 500);
    }
}

function createSupplier()
{
    try {
        $pdo = getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            jsonError('Le nom du fournisseur est obligatoire', 400);
        }

        // Vérifier si un fournisseur avec ce nom existe déjà
        $stmtCheck = $pdo->prepare("SELECT id FROM suppliers WHERE name = ?");
        $stmtCheck->execute([$name]);

        if ($stmtCheck->fetch()) {
            jsonError('Un fournisseur avec ce nom existe déjà', 409);
        }

        $stmt = $pdo->prepare("INSERT INTO suppliers (name) VALUES (?)");
        $stmt->execute([$name]);

        $newId = $pdo->lastInsertId();

        jsonSuccess(
            ['id' => $newId, 'name' => $name],
            'Fournisseur créé avec succès'
        );
    } catch (Throwable $e) {
        jsonError('Erreur création fournisseur : ' . $e->getMessage(), 500);
    }
}


function updateSupplier()
{
    try {
        $pdo = getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Accepte l'id depuis GET ou POST
        $id = isset($_GET['id'])  ? (int) $_GET['id']
            : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

        $name = trim($_POST['name'] ?? '');

        if (!$id) {
            jsonError('ID fournisseur manquant', 400);
        }

        if ($name === '') {
            jsonError('Le nom du fournisseur est obligatoire', 400);
        }

        // Vérifier que le fournisseur existe
        $stmtCheck = $pdo->prepare("SELECT id, name FROM suppliers WHERE id = ?");
        $stmtCheck->execute([$id]);
        $supplier = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$supplier) {
            jsonError('Fournisseur introuvable', 404);
        }

        // Vérifier qu'aucun autre fournisseur n'a déjà ce nom
        $stmtDup = $pdo->prepare("SELECT id FROM suppliers WHERE name = ? AND id != ?");
        $stmtDup->execute([$name, $id]);

        if ($stmtDup->fetch()) {
            jsonError('Un autre fournisseur avec ce nom existe déjà', 409);
        }

        $stmt = $pdo->prepare("UPDATE suppliers SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);

        jsonSuccess(
            ['id' => $id, 'name' => $name],
            "Fournisseur mis à jour avec succès"
        );
    } catch (Throwable $e) {
        jsonError('Erreur mise à jour fournisseur : ' . $e->getMessage(), 500);
    }
}


function newExpenseValidation()
{
    $pdo = getPDO();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentUserId   = $_SESSION['user_id'] ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;

    if (!$currentUserId || !$currentUserName) {
        jsonError('Utilisateur non authentifié', 401);
    }

    $projectId           = $_POST['project_id']            ?? null;
    $projectBudgetLineId = $_POST['project_budget_line_id'] ?? null;
    $amount              = $_POST['amount']                 ?? null;
    $expenseDate         = $_POST['expense_date']           ?? null;
    $description         = $_POST['description']            ?? null;

    // Montant payé (optionnel)
    $paidAmount = isset($_POST['paid_amount']) && $_POST['paid_amount'] !== ''
        ? (float) $_POST['paid_amount']
        : null;

    // Fournisseur (optionnel)
    $supplierId = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' && (int)$_POST['supplier_id'] > 0
        ? (int) $_POST['supplier_id']
        : null;

    if (!$projectId || !$projectBudgetLineId || !$amount || !$expenseDate) {
        jsonError('Paramètres manquants', 400);
    }

    try {

        $pdo->beginTransaction();

        /*
            ==========================
            1️⃣ Vérification projet
            ==========================
        */

        $stmtProject = $pdo->prepare("
            SELECT id, name
            FROM projects
            WHERE id = ?
        ");
        $stmtProject->execute([$projectId]);
        $project = $stmtProject->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            jsonError('Projet introuvable', 400);
        }

        /*
            ==========================
            2️⃣ Vérification ligne budgétaire + jointure correcte
            ==========================
        */

        $stmtPBL = $pdo->prepare("
            SELECT 
                pbl.id,
                pbl.allocated_amount,
                bl.name AS budget_name
            FROM project_budget_lines pbl
            JOIN budget_lines bl ON bl.id = pbl.budget_line_id
            WHERE pbl.id = ? AND pbl.project_id = ?
        ");
        $stmtPBL->execute([$projectBudgetLineId, $projectId]);
        $budgetLine = $stmtPBL->fetch(PDO::FETCH_ASSOC);

        if (!$budgetLine) {
            jsonError('Ligne budgétaire invalide', 400);
        }

        /*
            ==========================
            3️⃣ Nom du fournisseur (pour notification)
            ==========================
        */

        $supplierName = null;
        if ($supplierId) {
            $stmtSupplier = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
            $stmtSupplier->execute([$supplierId]);
            $supplierRow  = $stmtSupplier->fetch(PDO::FETCH_ASSOC);
            $supplierName = $supplierRow['name'] ?? null;
        }

        /*
            ==========================
            4️⃣ Upload fichiers
            ==========================
        */

        $uploadedFiles = [];
        $uploadDir = __DIR__ . '/../images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($_FILES['documents']['name'][0])) {

            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            foreach ($_FILES['documents']['name'] as $index => $fileName) {

                if ($_FILES['documents']['error'][$index] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowed)) {
                    continue;
                }

                $newFileName = uniqid() . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['documents']['tmp_name'][$index], $destination)) {
                    $uploadedFiles[] = $newFileName;
                }
            }
        }

        $documentsJson = !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;

        /*
            ==========================
            5️⃣ Insertion validation
            ==========================
        */

        $stmtInsert = $pdo->prepare("
            INSERT INTO expenses_validations (
                project_id,
                project_budget_line_id,
                amount,
                paid_amount,
                description,
                expense_date,
                documents,
                supplier_id,
                status,
                created_at,
                user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");

        $stmtInsert->execute([
            $projectId,
            $projectBudgetLineId,
            $amount,
            $paidAmount,
            $description,
            $expenseDate,
            $documentsJson,
            $supplierId,
            'en attente',
            $currentUserId,
        ]);

        /*
            ==========================
            6️⃣ Calcul dépassement
            ==========================
        */

        $stmtTotal = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses_validations
            WHERE project_budget_line_id = ?
            AND status = 'validée'
        ");
        $stmtTotal->execute([$projectBudgetLineId]);
        $totalSpent = $stmtTotal->fetchColumn();

        $allocated  = $budgetLine['allocated_amount'];
        $newTotal   = $totalSpent + $amount;
        $overAmount = max(0, $newTotal - $allocated);

        $fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' FCFA';

        /*
            ==========================
            7️⃣ Notifications
            ==========================
        */

        $adminNotification = "Nouvelle demande de validation\n\n"
            . "Utilisateur : {$currentUserName}\n"
            . "Projet : {$project['name']}\n"
            . "Ligne budgétaire : {$budgetLine['budget_name']}\n"
            . "Montant : " . $fmt($amount) . "\n"
            . ($paidAmount  !== null  ? "Montant payé : " . $fmt($paidAmount) . "\n" : '')
            . ($supplierName !== null  ? "Fournisseur : {$supplierName}\n"             : '')
            . "Date : {$expenseDate}\n"
            . ($description           ? "Description : {$description}\n"               : '')
            . (!empty($uploadedFiles) ? "Documents : " . count($uploadedFiles) . " fichier(s) joint(s)\n" : '')
            . ($overAmount > 0        ? "⚠ Dépassement : " . $fmt($overAmount) . "\n"  : '')
            . "\nAction requise : Confirmer ou refuser.";

        createNotification($adminNotification, $currentUserId, $currentUserName);

        $userNotification = "Votre demande de validation a été enregistrée.\n\n"
            . "Projet : {$project['name']}\n"
            . "Ligne budgétaire : {$budgetLine['budget_name']}\n"
            . "Montant : " . $fmt($amount) . "\n"
            . ($paidAmount  !== null  ? "Montant payé : " . $fmt($paidAmount) . "\n" : '')
            . ($supplierName !== null  ? "Fournisseur : {$supplierName}\n"             : '')
            . "Statut : en attente\n\n"
            . "Vous serez notifié après décision.";

        createNotification($userNotification, $currentUserId, $currentUserName);

        /*
            ==========================
            8️⃣ Commit final
            ==========================
        */

        $pdo->commit();

        jsonSuccess([], 'Demande de validation envoyée avec succès');
    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        jsonError('Erreur serveur : ' . $e->getMessage(), 500);
    }
}

function acceptExpenseValidation()
{
    $pdo = getPDO();

    $validationId = $_POST['validation_id'] ?? $_GET['validation_id'] ?? null;

    if (!$validationId || !ctype_digit($validationId)) {
        return [
            'success' => false,
            'message' => 'ID de validation invalide.'
        ];
    }

    try {
        $pdo->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Récupération validation + projet + ligne budget + utilisateur + fournisseur
        |--------------------------------------------------------------------------
        */
        $stmt = $pdo->prepare("
            SELECT 
                ev.*,
                p.name  AS project_name,
                bl.name AS budget_name,
                u.name  AS user_name,
                s.name  AS supplier_name
            FROM expenses_validations ev
            INNER JOIN projects p             ON p.id  = ev.project_id
            INNER JOIN project_budget_lines pbl ON pbl.id = ev.project_budget_line_id
            INNER JOIN budget_lines bl        ON bl.id = pbl.budget_line_id
            INNER JOIN users u                ON u.id  = ev.user_id
            LEFT  JOIN suppliers s            ON s.id  = ev.supplier_id
            WHERE ev.id = :id
              AND ev.status = 'en attente'
            LIMIT 1
        ");

        $stmt->execute(['id' => $validationId]);
        $validation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$validation) {
            throw new Exception("Validation introuvable ou déjà traitée.");
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Création de la dépense (avec supplier_id et paid_amount)
        |--------------------------------------------------------------------------
        */
        $insert = $pdo->prepare("
            INSERT INTO expenses (
                project_id,
                project_budget_line_id,
                amount,
                paid_amount,
                description,
                expense_date,
                documents,
                supplier_id,
                user_id,
                 created_at
            ) VALUES (
                :project_id,
                :budget_line_id,
                :amount,
                :paid_amount,
                :description,
                :expense_date,
                :documents,
                :supplier_id,
                :user_id,
                NOW()
            )
        ");

        $insert->execute([
            'project_id'     => $validation['project_id'],
            'budget_line_id' => $validation['project_budget_line_id'],
            'amount'         => $validation['amount'],
            'paid_amount'    => $validation['paid_amount'] ?? null,
            'description'    => $validation['description'],
            'expense_date'   => $validation['expense_date'],
            'documents'      => $validation['documents'],
            'supplier_id'    => $validation['supplier_id'] ?? null,
            'user_id'        => $validation['user_id'],
        ]);

        $expenseId = $pdo->lastInsertId();

        $userId = $validation['user_id'];
        $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $userName = $stmtUser->fetch(PDO::FETCH_COLUMN);

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Mise à jour statut validation
        |--------------------------------------------------------------------------
        */
        $update = $pdo->prepare("
            UPDATE expenses_validations
            SET status = 'acceptée'
            WHERE id = :id
        ");
        $update->execute(['id' => $validationId]);

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Notification avec tous les détails
        |--------------------------------------------------------------------------
        */
        $fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' FCFA';

        $message  = "Bonjour {$validation['user_name']},\n\n";
        $message .= "Votre demande de validation a été acceptée.\n\n";
        $message .= "Projet : {$validation['project_name']}\n";
        $message .= "Ligne budgétaire : {$validation['budget_name']}\n";
        $message .= "Montant : " . $fmt($validation['amount']) . "\n";

        if (!empty($validation['paid_amount'])) {
            $message .= "Montant payé : " . $fmt($validation['paid_amount']) . "\n";
        }

        if (!empty($validation['supplier_name'])) {
            $message .= "Fournisseur : {$validation['supplier_name']}\n";
        }

        $message .= "Date : {$validation['expense_date']}\n";

        if (!empty($validation['description'])) {
            $message .= "Description : {$validation['description']}\n";
        }

        $message .= "\nLa dépense a été enregistrée dans le système.";

        createNotification($message, $userId, $userName);

        $pdo->commit();

        jsonSuccess(['expense_id' => $expenseId], 'Dépense enregistrée avec succès');
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        jsonError($e->getMessage());
    }
}

function updatePassword()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');

    $pdo = getPDO();

    $userId = $_SESSION['user_id'] ?? null;
    $oldPassword = $_POST['old_password'] ?? null;
    $newPassword = $_POST['new_password'] ?? null;
    $confirmPassword = $_POST['confirm_password'] ?? null;

    if (!$userId) {
        jsonError("Utilisateur non connecté.", 401);
    }

    if (!$oldPassword || !$newPassword || !$confirmPassword) {
        jsonError("Tous les champs sont obligatoires.", 400);
    }

    if ($newPassword !== $confirmPassword) {
        jsonError("Le nouveau mot de passe et sa confirmation ne correspondent pas.", 400);
    }

    if (strlen($newPassword) < 6) {
        jsonError("Le mot de passe doit contenir au moins 6 caractères.", 400);
    }

    try {

        $stmt = $pdo->prepare("SELECT name, password FROM users WHERE id = :id AND status = 'Actif'");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError("Utilisateur introuvable ou inactif.", 404);
        }

        if (!password_verify($oldPassword, $user['password'])) {
            jsonError("L'ancien mot de passe est incorrect.", 400);
        }

        $pdo->beginTransaction();

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmtUpdate = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmtUpdate->execute([
            'password' => $hashedPassword,
            'id' => $userId
        ]);

        $message = "Bonjour {$user['name']}, votre mot de passe a été mis à jour avec succès.";
        createNotification($message, $userId, $user['name']);

        $pdo->commit();

        jsonSuccess([], "Mot de passe mis à jour avec succès.");
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        jsonError("Erreur interne du serveur.", 500);
    }
}

function rejectExpenseValidation()
{
    $pdo = getPDO();

    $validationId = $_POST['validation_id'] ?? $_GET['validation_id'] ?? null;

    if (!$validationId || !ctype_digit($validationId)) {
        jsonError('ID de validation invalide.');
        return;
    }

    try {
        $pdo->beginTransaction();

        // 1️⃣ Récupérer la validation + utilisateur uniquement si en attente
        $stmt = $pdo->prepare("
            SELECT ev.*, u.name AS user_name
            FROM expenses_validations ev
            INNER JOIN users u ON u.id = ev.user_id
            WHERE ev.id = :id AND ev.status = 'en attente'
            LIMIT 1
        ");
        $stmt->execute(['id' => $validationId]);
        $validation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$validation) {
            throw new Exception("Validation introuvable ou déjà traitée.");
        }

        // 2️⃣ Mettre à jour le statut en 'refusée'
        $update = $pdo->prepare("
            UPDATE expenses_validations
            SET status = 'refusée'
            WHERE id = :id
        ");
        $update->execute(['id' => $validationId]);

        // 3️⃣ Notification utilisateur
        $message  = "Bonjour {$validation['user_name']},\n\n";
        $message .= "Votre demande de validation d'une dépense a été refusée.\n\n";
        $message .= "Montant : " . number_format($validation['amount'], 0, ',', ' ') . " FCFA\n";
        if (!empty($validation['description'])) {
            $message .= "Description : {$validation['description']}\n";
        }
        $message .= "\nMerci de vérifier votre demande ou contacter l'administration.";

        createNotification(
            $message,
            $validation['user_id'],
            $validation['user_name'],
        );

        $pdo->commit();

        jsonSuccess([], 'Demande refusée et utilisateur notifié.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonError($e->getMessage());
    }
}

function getAllExpensesValidations()
{
    $pdo = getPDO();
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ev.id AS validation_id,
                ev.project_id,
                p.name AS project_name,
                ev.project_budget_line_id,
                bl.name AS budget_line_name,
                ev.amount AS requested_amount,
                ev.paid_amount,
                ev.description,
                ev.expense_date,
                ev.documents,
                ev.status,
                ev.user_id,
                u.name AS user_name,
                ev.supplier_id,
                s.name AS supplier_name,
                ev.created_at
            FROM expenses_validations ev
            LEFT JOIN projects p          ON p.id  = ev.project_id
            LEFT JOIN project_budget_lines pbl ON pbl.id = ev.project_budget_line_id
            LEFT JOIN budget_lines bl     ON bl.id = pbl.budget_line_id
            LEFT JOIN users u             ON u.id  = ev.user_id
            LEFT JOIN suppliers s         ON s.id  = ev.supplier_id
            ORDER BY ev.created_at DESC
        ");
        $stmt->execute();
        $validations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonSuccess($validations);
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
}



function updateExpense()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = getPDO();

    $currentUserId   = $_SESSION['user_id']   ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;
    $currentUserRole = $_SESSION['user_role'] ?? null;

    if (!$currentUserId || !$currentUserName) {
        jsonError('Utilisateur non authentifié', 401);
    }

    $id                  = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $projectId           = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
    $projectBudgetLineId = isset($_POST['project_budget_line_id']) ? (int) $_POST['project_budget_line_id'] : 0;
    $amount              = isset($_POST['amount']) ? (float) $_POST['amount'] : null;
    $expenseDate         = $_POST['expense_date'] ?? null;
    $description         = $_POST['description'] ?? null;

    // Montant payé (optionnel)
    $paidAmount = isset($_POST['paid_amount']) && $_POST['paid_amount'] !== ''
        ? (float) $_POST['paid_amount']
        : null;

    // ── Fournisseur (optionnel) ──────────────────────────────────────────────
    $supplierId = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' && (int)$_POST['supplier_id'] > 0
        ? (int) $_POST['supplier_id']
        : null;

    if (!$id || !$projectId || !$projectBudgetLineId || $amount === null || !$expenseDate) {
        jsonError('Paramètres manquants', 400);
    }

    // ── Date au format West Africa / Bénin (UTC+1) ──────────────────────────
    try {
        $tz          = new DateTimeZone('Africa/Porto-Novo');
        $dtInput     = new DateTime($expenseDate);
        $dtInput->setTimezone($tz);
        $expenseDate = $dtInput->format('Y-m-d');
    } catch (Exception $e) {
        jsonError('Format de date invalide', 400);
    }

    // Helper : récupère le libellé d'une project_budget_line via jointure budget_lines
    $getBudgetLineLabel = function (int $pblId) use ($pdo): string {
        $stmt = $pdo->prepare("
            SELECT bl.name
            FROM project_budget_lines pbl
            JOIN budget_lines bl ON bl.id = pbl.budget_line_id
            WHERE pbl.id = ?
        ");
        $stmt->execute([$pblId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['name'] ?? "Ligne #{$pblId}";
    };

    try {
        $pdo->beginTransaction();

        // Récupération ancienne dépense
        $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        $oldExpense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldExpense) {
            jsonError('Dépense introuvable', 404);
        }

        // Vérification droits
        $isAdmin = ($currentUserRole === 'admin');
        if (
            !$isAdmin &&
            isset($oldExpense['user_id']) &&
            (int)$oldExpense['user_id'] !== (int)$currentUserId
        ) {
            jsonError('Accès refusé', 403);
        }

        // Documents existants
        $existingDocuments = [];
        if (!empty($oldExpense['documents'])) {
            $decoded = json_decode($oldExpense['documents'], true);
            if (is_array($decoded)) {
                $existingDocuments = $decoded;
            }
        }

        $uploadedFiles = $existingDocuments;

        // Upload nouveaux documents
        if (!empty($_FILES['documents']['name'][0])) {

            $newFilesCount = count($_FILES['documents']['name']);
            if (($newFilesCount + count($existingDocuments)) > 15) {
                jsonError('Maximum 15 documents autorisés', 400);
            }

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

            foreach ($_FILES['documents']['name'] as $i => $originalName) {

                if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
                    jsonError('Erreur upload document', 400);
                }

                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtensions)) {
                    jsonError('Format non autorisé', 400);
                }

                $newFileName = uniqid('doc_', true) . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (!move_uploaded_file($_FILES['documents']['tmp_name'][$i], $destination)) {
                    jsonError('Échec enregistrement fichier', 500);
                }

                $uploadedFiles[] = $newFileName;
            }
        }

        // Si user_id était NULL, on l'attribue à celui qui modifie
        $previousUserId = $oldExpense['user_id'] ?? null;
        $newUserId      = (empty($previousUserId)) ? $currentUserId : $previousUserId;

        // ── Update avec supplier_id ──────────────────────────────────────────
        $stmtUpdate = $pdo->prepare("
            UPDATE expenses SET
                project_id             = ?,
                project_budget_line_id = ?,
                amount                 = ?,
                paid_amount            = ?,
                description            = ?,
                expense_date           = ?,
                documents              = ?,
                supplier_id            = ?,
                user_id                = ?,
                updated_at             = NOW()
            WHERE id = ?
        ");

        $stmtUpdate->execute([
            $projectId,
            $projectBudgetLineId,
            $amount,
            $paidAmount,
            $description,
            $expenseDate,
            json_encode($uploadedFiles),
            $supplierId,
            $newUserId,
            $id,
        ]);

        // Récupération des infos projet
        $stmtProject = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmtProject->execute([$projectId]);
        $project = $stmtProject->fetch(PDO::FETCH_ASSOC);

        $projectName = $project['name'] ?? ($project['project_name'] ?? 'Projet inconnu');
        $contractNum = $project['contract_number'] ?? '-';

        // Libellés des lignes budgétaires
        $budgetLineLabel = $getBudgetLineLabel($projectBudgetLineId);

        // Nom du fournisseur actuel (pour la notification)
        $supplierName = null;
        if ($supplierId) {
            $stmtSupplier = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
            $stmtSupplier->execute([$supplierId]);
            $supplierRow  = $stmtSupplier->fetch(PDO::FETCH_ASSOC);
            $supplierName = $supplierRow['name'] ?? null;
        }

        // ── Détection des changements ────────────────────────────────────────
        $changes = [];
        $fmt     = fn($v) => number_format((float)$v, 0, ',', ' ') . ' FCFA';

        if ((float)$oldExpense['amount'] !== (float)$amount) {
            $changes[] = "• Montant      : " . $fmt($oldExpense['amount']) . " → " . $fmt($amount);
        }

        $oldPaid = isset($oldExpense['paid_amount']) && $oldExpense['paid_amount'] !== '' && $oldExpense['paid_amount'] !== null
            ? (float)$oldExpense['paid_amount']
            : null;
        if ($oldPaid !== $paidAmount) {
            $oldPaidStr = $oldPaid !== null ? $fmt($oldPaid) : '(non renseigné)';
            $newPaidStr = $paidAmount !== null ? $fmt($paidAmount) : '(non renseigné)';
            $changes[] = "• Montant payé : {$oldPaidStr} → {$newPaidStr}";
        }

        if ((int)$oldExpense['project_id'] !== $projectId) {
            $changes[] = "• Projet       : modifié → {$projectName} (#{$projectId})";
        }

        if ((int)$oldExpense['project_budget_line_id'] !== $projectBudgetLineId) {
            $oldLabel  = $getBudgetLineLabel((int)$oldExpense['project_budget_line_id']);
            $changes[] = "• Ligne budg.  : {$oldLabel} → {$budgetLineLabel}";
        }

        if (($oldExpense['description'] ?? '') !== ($description ?? '')) {
            $oldDesc   = trim($oldExpense['description'] ?? '') ?: '(vide)';
            $newDesc   = trim($description ?? '') ?: '(vide)';
            $changes[] = "• Description  : \"{$oldDesc}\" → \"{$newDesc}\"";
        }

        if (($oldExpense['expense_date'] ?? '') !== $expenseDate) {
            $changes[] = "• Date         : {$oldExpense['expense_date']} → {$expenseDate}";
        }

        // Changement de fournisseur
        $oldSupplierId = isset($oldExpense['supplier_id']) && (int)$oldExpense['supplier_id'] > 0
            ? (int)$oldExpense['supplier_id']
            : null;
        if ($oldSupplierId !== $supplierId) {
            $oldSupplierName = '(aucun)';
            if ($oldSupplierId) {
                $stmtOldS = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
                $stmtOldS->execute([$oldSupplierId]);
                $oldSupplierRow  = $stmtOldS->fetch(PDO::FETCH_ASSOC);
                $oldSupplierName = $oldSupplierRow['name'] ?? "(#$oldSupplierId)";
            }
            $newSupplierName = $supplierName ?? '(aucun)';
            $changes[] = "• Fournisseur  : {$oldSupplierName} → {$newSupplierName}";
        }

        if (json_encode($existingDocuments) !== json_encode($uploadedFiles)) {
            $added     = count($uploadedFiles) - count($existingDocuments);
            $changes[] = "• Documents    : +{$added} fichier(s) ajouté(s) — total " . count($uploadedFiles);
        }

        if (empty($previousUserId)) {
            $changes[] = "• Responsable  : attribué à {$currentUserName} (user #{$currentUserId})";
        }

        if (!empty($changes)) {
            $date    = (new DateTime('now', new DateTimeZone('Africa/Porto-Novo')))->format('d/m/Y à H:i');
            $message = "════════════════════════════════\n"
                . "  MODIFICATION DE DÉPENSE\n"
                . "════════════════════════════════\n"
                . "Projet       : {$projectName}\n"
                . "Contrat      : {$contractNum}\n"
                . "Dépense      : #{$id}\n"
                . "Montant actuel : " . $fmt($amount) . "\n"
                . ($paidAmount    !== null ? "Montant payé : " . $fmt($paidAmount) . "\n" : '')
                . ($supplierName  !== null ? "Fournisseur  : {$supplierName}\n"            : '')
                . "Date dépense : {$expenseDate}\n"
                . "Ligne budg.  : {$budgetLineLabel}\n"
                . "Modifié par  : {$currentUserName} (user #{$currentUserId})\n"
                . "Le           : {$date}\n"
                . "────────────────────────────────\n"
                . "Modifications :\n"
                . implode("\n", $changes) . "\n"
                . "════════════════════════════════";

            createNotification($message, 1, 'admin');
        }

        $pdo->commit();

        jsonSuccess([], 'Dépense mise à jour avec succès');
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $detail = sprintf(
            '[%s] %s — dans %s ligne %d',
            get_class($e),
            $e->getMessage(),
            str_replace(__DIR__, '', $e->getFile()),
            $e->getLine()
        );

        $traceLines = array_slice(explode("\n", $e->getTraceAsString()), 0, 5);
        $traceStr   = implode(' | ', $traceLines);

        jsonError("Erreur serveur : {$detail} | Trace : {$traceStr}", 500);
    }
}


function createNotification($description, $user_id, $user_name)
{
    $pdo = getPDO();

    if (empty($description) || empty($user_id) || empty($user_name)) {
        return false;
    }

    try {

        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, user_name, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([
            (int)$user_id,
            trim($user_name),
            trim($description)
        ]);

        return $pdo->lastInsertId();
    } catch (Exception $e) {
        return false;
    }
}

function markNotificationsAsReaden($user_id = null)
{
    $pdo = getPDO();

    try {
        if ($user_id) {
            // Utilisateur : marquer uniquement ses notifications
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([(int)$user_id]);
        } else {
            // Admin : marquer toutes les notifications admin
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_name = 'admin' AND is_read = 0");
            $stmt->execute();
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}


function deleteProject()
{
    $pdo = getPDO();

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonError('ID manquant');
    }

    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);

    jsonSuccess([], 'Projet supprimé');
}





function createUser()
{
    $pdo = getPDO();

    // Lire le JSON envoyé par fetch
    $input = json_decode(file_get_contents('php://input'), true);

    $name = $input['name'] ?? null;
    $password = $input['password'] ?? null;
    $role = $input['role'] ?? null;

    if (!$name || !$password || !$role) {
        jsonError('Paramètres manquants', 400);
    }

    try {
        // Vérifier si l'utilisateur existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE name = ?");
        $check->execute([$name]);

        if ($check->fetch()) {
            jsonError('Cet utilisateur existe déjà', 400);
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $status = 'Actif';

        // Insertion
        $stmt = $pdo->prepare("
            INSERT INTO users (name, password, role, status, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $name,
            $hashedPassword,
            $role,
            $status
        ]);

        jsonSuccess([], 'Utilisateur créé avec succès');
    } catch (PDOException $e) {
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
}

function getUsers()
{
    $pdo = getPDO();

    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess($users);
    } catch (PDOException $e) {
        jsonError('Erreur lors de la récupération des utilisateurs : ' . $e->getMessage(), 500);
    }
}


function updateUser()
{
    $pdo = getPDO();

    // Lire le JSON envoyé par fetch
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'] ?? null;
    $role = $input['role'] ?? null;
    $password = $input['password'] ?? null;

    if (!$id || !$role) {
        jsonError('Paramètres manquants', 400);
    }

    try {
        // Vérifier que l'utilisateur existe
        $check = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $check->execute([$id]);
        $user = $check->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError('Utilisateur introuvable', 404);
        }

        // Construire dynamiquement la requête
        $fields = ["role = ?"];
        $params = [$role];

        // Si un nouveau mot de passe est fourni
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $fields[] = "password = ?";
            $params[] = $hashedPassword;
        }

        $fields[] = "updated_at = NOW()";

        $params[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        jsonSuccess([], 'Utilisateur modifié avec succès');
    } catch (PDOException $e) {
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
}


function banUser()
{
    $pdo = getPDO();

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        jsonError('ID utilisateur manquant', 400);
    }

    try {
        // Vérifier que l'utilisateur existe
        $check = $pdo->prepare("SELECT id, status FROM users WHERE id = ?");
        $check->execute([$id]);
        $user = $check->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError('Utilisateur introuvable', 404);
        }

        if ($user['status'] === 'Banni') {
            jsonError('Utilisateur déjà banni', 400);
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET status = 'Banni', updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        jsonSuccess([], 'Utilisateur banni avec succès');
    } catch (PDOException $e) {
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
}


function unbanUser()
{
    $pdo = getPDO();

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        jsonError('ID utilisateur manquant', 400);
    }

    try {
        // Vérifier que l'utilisateur existe
        $check = $pdo->prepare("SELECT id, status FROM users WHERE id = ?");
        $check->execute([$id]);
        $user = $check->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError('Utilisateur introuvable', 404);
        }

        if ($user['status'] === 'Actif') {
            jsonError('Utilisateur déjà actif', 400);
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET status = 'Actif', updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        jsonSuccess([], 'Utilisateur débanni avec succès');
    } catch (PDOException $e) {
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
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


function createSimpleBudgetLine()
{
    try {
        $pdo = getPDO();
        $data = json_decode(file_get_contents('php://input'), true);

        $name = $data['name'] ?? null;
        if (!$name) {
            jsonError('Paramètres manquants');
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO budget_lines (name) VALUES (?)"
        );
        $stmt->execute([$name]); // <-- CORRECTION CRITIQUE

        jsonSuccess(
            ['id' => $pdo->lastInsertId()],
            'Ligne budgétaire créée'
        );
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
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

    $sql = "
         SELECT 
             e.id,
             e.project_id,
             p.name AS project_name,
             e.project_budget_line_id,
             bl.name AS budget_line_name,
             pbl.allocated_amount,
             (
                 SELECT IFNULL(SUM(e2.amount), 0) 
                 FROM expenses e2 
                 WHERE e2.project_budget_line_id = e.project_budget_line_id
             ) AS spent,
             e.amount,
             e.paid_amount,
             e.expense_date,
             e.description,
             e.documents,
             e.user_id,
             u.name    AS user_name,
             e.supplier_id,
             s.name    AS supplier_name,
             e.created_at,
             e.updated_at
         FROM expenses e
         JOIN projects p             ON p.id  = e.project_id
         JOIN project_budget_lines pbl ON pbl.id = e.project_budget_line_id
         JOIN budget_lines bl        ON bl.id = pbl.budget_line_id
         LEFT JOIN users u           ON u.id  = e.user_id
         LEFT JOIN suppliers s       ON s.id  = e.supplier_id
         ORDER BY e.created_at DESC
     ";

    try {
        $stmt     = $pdo->query($sql);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (
            strpos($e->getMessage(), 'user_id')     !== false ||
            strpos($e->getMessage(), 'supplier_id') !== false ||
            strpos($e->getMessage(), 'paid_amount') !== false ||
            strpos($e->getMessage(), 'created_by')  !== false
        ) {
            // Fallback sans les colonnes potentiellement absentes
            $sqlFallback = "
                 SELECT 
                     e.id,
                     e.project_id,
                     p.name AS project_name,
                     e.project_budget_line_id,
                     bl.name AS budget_line_name,
                     pbl.allocated_amount,
                     (
                         SELECT IFNULL(SUM(e2.amount), 0) 
                         FROM expenses e2 
                         WHERE e2.project_budget_line_id = e.project_budget_line_id
                     ) AS spent,
                     e.amount,
                     e.expense_date,
                     e.description,
                     e.documents,
                     e.created_at,
                     e.updated_at
                 FROM expenses e
                 JOIN projects p               ON p.id  = e.project_id
                 JOIN project_budget_lines pbl ON pbl.id = e.project_budget_line_id
                 JOIN budget_lines bl          ON bl.id = pbl.budget_line_id
                 ORDER BY e.created_at DESC
             ";
            $stmt     = $pdo->query($sqlFallback);
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($expenses as &$row) {
                $row['user_id']       = null;
                $row['user_name']     = null;
                $row['supplier_id']   = null;
                $row['supplier_name'] = null;
                $row['paid_amount']   = null;
            }
            unset($row);
        } else {
            throw $e;
        }
    }

    jsonSuccess($expenses);
}

function getNotifications()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $currentUserId = $_SESSION['user_id'] ?? null;
    if (!$currentUserId) {
        jsonError('Utilisateur non connecté', 401);
        return;
    }

    $pdo = getPDO();

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                user_id,
                user_name,
                is_read,
                description,
                created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $currentUserId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonSuccess($notifications);
    } catch (PDOException $e) {
        jsonError('Erreur lors de la récupération des notifications : ' . $e->getMessage(), 500);
    }
}



function removeExpenseDocument()
{
    $pdo = getPDO();

    // Récupérer l'ID de la dépense
    $expenseId = $_GET['id'] ?? null;
    if (!$expenseId) {
        jsonError('ID de dépense manquant', 400);
    }

    try {
        // Récupérer le chemin actuel du document
        $stmt = $pdo->prepare("SELECT document FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense) {
            jsonError('Dépense introuvable', 404);
        }

        $documentPath = $expense['document'];

        if ($documentPath) {
            $fullPath = __DIR__ . '/../' . $documentPath; // dossier images à la racine

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Mettre à jour la dépense pour supprimer le document
        $update = $pdo->prepare("UPDATE expenses SET document = NULL, updated_at = NOW() WHERE id = ?");
        $update->execute([$expenseId]);

        jsonSuccess([], 'Document supprimé avec succès');
    } catch (PDOException $e) {
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
}





function deleteExpense()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentUserId = $_SESSION['user_id'] ?? null;
    $currentUserRole = $_SESSION['user_role'] ?? null;
    $isAdmin = ($currentUserRole === 'admin');
    if (!$currentUserId) {
        jsonError('Utilisateur non connecté', 401);
    }

    $pdo = getPDO();
    $id = $_GET['id'] ?? null;
    if (!$id) {
        jsonError('ID manquant', 400);
    }

    $canDelete = true;
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$expense) {
            jsonError('Dépense introuvable', 404);
        }
        if (!$isAdmin && isset($expense['user_id']) && $expense['user_id'] !== null && (int) $expense['user_id'] !== (int) $currentUserId) {
            $canDelete = false;
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'user_id') !== false || strpos($e->getMessage(), 'created_by') !== false) {
            $stmt = $pdo->prepare("SELECT id FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                jsonError('Dépense introuvable', 404);
            }
        } else {
            throw $e;
        }
    }

    if (!$canDelete) {
        jsonError('Vous ne pouvez supprimer que les dépenses que vous avez insérées.', 403);
    }

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
