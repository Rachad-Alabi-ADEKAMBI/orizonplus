<?php
session_start();

/**
 * Connexion à la base de données
 */
include 'db.php';

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
                p.id,
                p.name,
                p.description,
                p.department,
                p.location,
                p.documents,
                p.date_of_creation,
                p.created_at,
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
            GROUP BY 
                p.id,
                p.name,
                p.description,
                p.department,
                p.location,
                p.documents,
                p.date_of_creation,
                p.created_at
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
    $pdo = getPDO();

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? null;
    $department = $_POST['department'] ?? null;
    $location = $_POST['location'] ?? null;
    $date_of_creation = $_POST['date_of_creation'] ?? null;
    $lines = json_decode($_POST['lines'] ?? '[]', true);

    if (empty($name)) {
        jsonError('Nom de projet manquant');
    }

    try {
        $pdo->beginTransaction();

        // 📁 Gestion upload fichiers
        $uploadedFiles = [];

        if (!empty($_FILES['documents']['name'][0])) {

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {

                $originalName = $_FILES['documents']['name'][$key];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // Autoriser seulement images et pdf
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                if (!in_array($extension, $allowed)) continue;

                $newName = uniqid() . '.' . $extension;
                $destination = $uploadDir . $newName;

                move_uploaded_file($tmpName, $destination);

                $uploadedFiles[] = $newName;
            }
        }

        // 🗂 Insertion projet
        $stmt = $pdo->prepare("
            INSERT INTO projects
            (name, description, department, location, documents, date_of_creation)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $description,
            $department,
            $location,
            json_encode($uploadedFiles),
            $date_of_creation
        ]);

        $projectId = $pdo->lastInsertId();

        // 💰 Lignes budgétaires
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
                ) continue;

                $stmtLine->execute([
                    $projectId,
                    (int)$line['budget_line_id'],
                    (float)$line['allocated_amount']
                ]);
            }
        }

        $pdo->commit();

        jsonSuccess(['id' => $projectId], 'Projet créé avec succès');
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur création projet');
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
    session_start();
    $pdo = getPDO();

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? null;
    $department = $_POST['department'] ?? null;
    $location = $_POST['location'] ?? null;
    $date_of_creation = $_POST['date_of_creation'] ?? null;

    $lines = json_decode($_POST['lines'] ?? '[]', true);
    $updatedLines = json_decode($_POST['updated_lines'] ?? '[]', true);
    $deletedLines = json_decode($_POST['deleted_lines'] ?? '[]', true);
    $keptDocuments = json_decode($_POST['existing_documents'] ?? '[]', true);

    if (!$id || empty($name)) {
        jsonError('Paramètres manquants');
    }

    try {

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

        $oldDocuments = json_decode($oldProject['documents'] ?? '[]', true);
        $uploadDir = __DIR__ . '/../images/';

        // Sécuriser keptDocuments
        $keptDocuments = array_intersect($oldDocuments, $keptDocuments ?? []);

        // Suppressions
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

        // Ajout nouveaux fichiers
        if (!empty($_FILES['documents']['name'][0])) {

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {

                $originalName = $_FILES['documents']['name'][$key];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                if (!in_array($extension, $allowed)) continue;

                $newName = uniqid() . '.' . $extension;
                $destination = $uploadDir . $newName;

                move_uploaded_file($tmpName, $destination);

                $finalDocuments[] = $newName;

                $changes[] = "Document ajouté : {$newName}";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🔄 Comparaison champs principaux
        |--------------------------------------------------------------------------
        */

        $fieldsToCheck = [
            'name' => $name,
            'description' => $description,
            'department' => $department,
            'location' => $location,
            'date_of_creation' => $date_of_creation
        ];

        foreach ($fieldsToCheck as $field => $newValue) {

            $oldValue = $oldProject[$field];

            if ((string)$oldValue !== (string)$newValue) {
                $changes[] = ucfirst($field) . " modifié : \"{$oldValue}\" → \"{$newValue}\"";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🗂 Mise à jour projet
        |--------------------------------------------------------------------------
        */

        $stmtUpdateProject = $pdo->prepare("
            UPDATE projects SET
                name = ?,
                description = ?,
                department = ?,
                location = ?,
                documents = ?,
                date_of_creation = ?
            WHERE id = ?
        ");

        $stmtUpdateProject->execute([
            $name,
            $description,
            $department,
            $location,
            json_encode($finalDocuments),
            $date_of_creation,
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

                if (
                    empty($line['project_budget_line_id']) ||
                    !isset($line['allocated_amount'])
                ) continue;

                // Récupérer infos ligne
                $stmtLine = $pdo->prepare("
                    SELECT pbl.allocated_amount, bl.name
                    FROM project_budget_lines pbl
                    JOIN budget_lines bl ON bl.id = pbl.budget_line_id
                    WHERE pbl.id = ?
                ");
                $stmtLine->execute([(int)$line['project_budget_line_id']]);
                $lineData = $stmtLine->fetch(PDO::FETCH_ASSOC);

                if (!$lineData) continue;

                $oldAmount = $lineData['allocated_amount'];
                $lineName = $lineData['name'];

                if ((float)$oldAmount !== (float)$line['allocated_amount']) {
                    $changes[] = "Ligne \"{$lineName}\" modifiée : "
                        . $oldAmount . " → "
                        . $line['allocated_amount'];
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

                if (
                    empty($line['budget_line_id']) ||
                    !isset($line['allocated_amount'])
                ) continue;

                // Récupérer nom ligne
                $stmtName = $pdo->prepare("SELECT name FROM budget_lines WHERE id = ?");
                $stmtName->execute([(int)$line['budget_line_id']]);
                $lineName = $stmtName->fetchColumn();

                $changes[] = "Ligne ajoutée : \"{$lineName}\" ("
                    . $line['allocated_amount'] . ")";

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

            $stmtDelete = $pdo->prepare("
                DELETE FROM project_budget_lines WHERE id = ?
            ");

            foreach ($deletedLines as $lineId) {

                // Récupérer nom avant suppression
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

        if (!empty($changes) && isset($_SESSION['user_id'], $_SESSION['user_name'])) {

            $currentUserId = $_SESSION['user_id'];
            $currentUserName = $_SESSION['user_name'];

            $notificationText =
                "Le projet \"{$oldProject['name']}\" a été modifié par {$currentUserName}.\n\n"
                . implode("\n", $changes);

            createNotification(
                $notificationText,
                $currentUserId,
                $currentUserName
            );
        }

        $pdo->commit();

        jsonSuccess([], 'Projet mis à jour avec succès');
    } catch (Exception $e) {

        $pdo->rollBack();
        jsonError('Erreur mise à jour projet');
    }
}

function createExpense()
{
    $pdo = getPDO();

    $currentUserId = $_SESSION['user_id'] ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;

    if (!$currentUserId || !$currentUserName) {
        jsonError('Utilisateur non authentifié', 401);
    }

    $projectId = $_POST['project_id'] ?? null;
    $projectBudgetLineId = $_POST['project_budget_line_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $expenseDate = $_POST['expense_date'] ?? null;
    $description = $_POST['description'] ?? null;

    if (!$projectId || !$projectBudgetLineId || !$amount || !$expenseDate) {
        jsonError('Paramètres manquants', 400);
    }

    try {
        $pdo->beginTransaction();

        // 🔹 Récupérer le nom du projet
        $stmtProject = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
        $stmtProject->execute([$projectId]);
        $project = $stmtProject->fetch(PDO::FETCH_ASSOC);
        if (!$project) {
            jsonError('Projet introuvable', 400);
        }
        $projectName = $project['name'];

        // 🔹 Vérifier que la ligne budgétaire appartient au projet et récupérer budget_line_id
        $stmtPBL = $pdo->prepare("SELECT budget_line_id FROM project_budget_lines WHERE id = ? AND project_id = ?");
        $stmtPBL->execute([$projectBudgetLineId, $projectId]);
        $pbl = $stmtPBL->fetch(PDO::FETCH_ASSOC);
        if (!$pbl) {
            jsonError('Ligne budgétaire introuvable pour ce projet', 400);
        }
        $budgetLineId = $pbl['budget_line_id'];

        // 🔹 Récupérer le nom de la ligne budgétaire
        $stmtBudget = $pdo->prepare("SELECT name FROM budget_lines WHERE id = ?");
        $stmtBudget->execute([$budgetLineId]);
        $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);
        if (!$budget) {
            jsonError('Nom de ligne budgétaire introuvable', 400);
        }
        $budgetName = $budget['name'];

        // 🔹 Gestion du document
        $documentPath = null;
        if (!empty($_FILES['document']['name'])) {
            $uploadDir = __DIR__ . '/../images/'; // images à la racine
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                jsonError('Impossible de créer le dossier pour les documents', 500);
            }

            $originalName = $_FILES['document']['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($extension, $allowed)) {
                jsonError('Format de document non autorisé', 400);
            }

            $newFileName = uniqid() . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['document']['tmp_name'], $destination)) {
                jsonError('Erreur lors de l\'upload du document', 500);
            }

            $documentPath = $newFileName; // chemin relatif pour le front
        }

        // 🔹 Insertion de la dépense
        $insertExpense = $pdo->prepare("
            INSERT INTO expenses (
                project_id,
                project_budget_line_id,
                amount,
                description,
                expense_date,
                document,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $insertExpense->execute([
            $projectId,
            $projectBudgetLineId,
            $amount,
            $description,
            $expenseDate,
            $documentPath
        ]);

        $expenseId = $pdo->lastInsertId();

        // 🔹 Notification
        $notificationText = "Nouvelle dépense enregistrée par {$currentUserName} pour le projet \"{$projectName}\":\n"
            . "Ligne budgétaire: {$budgetName}\n"
            . "Montant: {$amount} FCFA\n"
            . ($description ? "Description: {$description}\n" : '')
            . ($documentPath ? "Document: {$documentPath}" : '');

        createNotification($notificationText, $currentUserId, $currentUserName);

        $pdo->commit();

        jsonSuccess(['expense_id' => $expenseId], 'Dépense enregistrée avec succès');
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur : ' . $e->getMessage(), 500);
    }
}



function updateExpense()
{
    $pdo = getPDO();

    $currentUserId = $_SESSION['user_id'] ?? null;
    $currentUserName = $_SESSION['user_name'] ?? null;

    $id = $_GET['id'] ?? null;
    $projectId = $_POST['project_id'] ?? null;
    $projectBudgetLineId = $_POST['project_budget_line_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $expenseDate = $_POST['expense_date'] ?? null;
    $description = $_POST['description'] ?? null;

    if (!$id || !$projectId || !$projectBudgetLineId || !$amount || !$expenseDate) {
        jsonError('Paramètres manquants', 400);
    }

    try {
        $pdo->beginTransaction();

        // Récupérer l’ancienne dépense
        $stmtOld = $pdo->prepare("
            SELECT e.*, p.name AS project_name, bl.name AS budget_line_name
            FROM expenses e
            JOIN project_budget_lines pbl ON e.project_budget_line_id = pbl.id
            JOIN budget_lines bl ON pbl.budget_line_id = bl.id
            JOIN projects p ON e.project_id = p.id
            WHERE e.id = ?
        ");
        $stmtOld->execute([$id]);
        $oldExpense = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$oldExpense) {
            jsonError('Dépense introuvable', 404);
        }

        // Gestion du document
        $documentPath = $oldExpense['document'] ?? null;
        if (!empty($_FILES['document']['name'])) {
            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $originalName = $_FILES['document']['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($extension, $allowed)) {
                jsonError('Format de document non autorisé', 400);
            }

            // Supprimer ancien fichier si existant
            if ($documentPath && file_exists(__DIR__ . '/../' . $documentPath)) {
                unlink(__DIR__ . '/../' . $documentPath);
            }

            $newFileName = uniqid() . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['document']['tmp_name'], $destination)) {
                jsonError('Erreur lors de l\'upload du document', 500);
            }

            $documentPath = 'images/' . $newFileName;
        }

        // Mise à jour de la dépense
        $stmtUpdate = $pdo->prepare("
            UPDATE expenses SET
                project_id = ?,
                project_budget_line_id = ?,
                amount = ?,
                description = ?,
                expense_date = ?,
                document = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $projectId,
            $projectBudgetLineId,
            $amount,
            $description,
            $expenseDate,
            $documentPath,
            $id
        ]);

        // Préparer la notification détaillée
        $changes = [];
        if ($oldExpense['amount'] != $amount) {
            $changes[] = "Montant: {$oldExpense['amount']} → {$amount}";
        }
        if ($oldExpense['project_budget_line_id'] != $projectBudgetLineId) {
            $changes[] = "Ligne budgétaire: {$oldExpense['budget_line_name']} → ID {$projectBudgetLineId}";
        }
        if ($oldExpense['project_id'] != $projectId) {
            $changes[] = "Projet: {$oldExpense['project_name']} → ID {$projectId}";
        }
        if (($oldExpense['description'] ?? '') != ($description ?? '')) {
            $changes[] = "Description: \"{$oldExpense['description']}\" → \"{$description}\"";
        }
        if (($oldExpense['expense_date'] ?? '') != $expenseDate) {
            $changes[] = "Date de dépense: {$oldExpense['expense_date']} → {$expenseDate}";
        }
        if (($oldExpense['document'] ?? '') != ($documentPath ?? '')) {
            $changes[] = "Document: " . ($oldExpense['document'] ?? 'aucun') . " → " . ($documentPath ?? 'aucun');
        }

        $notificationText = "Dépense modifiée par {$currentUserName}:\n" . implode("\n", $changes);

        createNotification($notificationText, $currentUserId, $currentUserName);

        $pdo->commit();

        jsonSuccess([], 'Dépense mise à jour avec succès');
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonError('Erreur PDO : ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonError('Erreur : ' . $e->getMessage(), 500);
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
            e.document,
            e.created_at,
            e.updated_at
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

function getNotifications()
{
    $pdo = getPDO();

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                description,
                created_at
            FROM notifications
            ORDER BY created_at DESC
        ");

        $stmt->execute();
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
