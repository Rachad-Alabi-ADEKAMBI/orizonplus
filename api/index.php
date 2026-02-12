<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Autoriser certains headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 0); // n'affiche plus les erreurs à l'écran
ini_set('log_errors', 1);     // les log dans le fichier error_log
error_reporting(E_ALL);

// Répondre aux requêtes OPTIONS (préflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once __DIR__ . '/functions.php';

$action = $_GET['action'] ?? null;
$publicActions = ['login'];

if (!$action) {
    jsonError("Paramètre 'action' manquant");
}

// Vérifier connexion (sauf login)
/*
if (!in_array($action, $publicActions) && !isLoggedIn()) {
    jsonError("Utilisateur non authentifié", 401);
}
*/
switch ($action) {

    // 🔐 AUTH
    case 'login':
        login();
        break;

    case 'logout':
        logout();
        break;

    // 📁 PROJETS
    case 'getProjects':
        getProjects();
        break;

    case 'createProject':
        createProject();
        break;

    case 'getProject':
        getProject();
        break;

    case 'updateProject':
        updateProject();
        break;

    case 'deleteProject':
        deleteProject();
        break;

    case 'getProjectBudgetLines':
        getProjectBudgetLines();
        break;

    case 'getNotifications':
        getNotifications();
        break;

    case 'createUser':
        createUser();
        break;

    case 'updateUser':
        updateUser();
        break;

    case 'banUser':
        banUser();
        break;

    case 'unbanUser':
        unbanUser();
        break;

    case 'getUsers':
        getUsers();
        break;

    case 'removeExpenseDocument':
        removeExpenseDocument();
        break;

    // 💼 LIGNES BUDGÉTAIRES
    case 'createBudgetLine':
        createBudgetLine();
        break;

    case 'createSimpleBudgetLine':
        createSimpleBudgetLine();
        break;

    case 'updateBudgetLine':
        updateBudgetLine();
        break;

    case 'getBudgetLines':
        getBudgetLines();
        break;

    case 'getProjectDetails':
        getProjectDetails();
        break;

    case 'deleteProjectBudgetLine':
        deleteProjectBudgetLine();
        break;


    case 'deleteBudgetLine':
        deleteBudgetLine();
        break;

    // 💰 DÉPENSES
    case 'getExpenses':
        getExpenses();
        break;

    case 'createExpense':
        createExpense();
        break;

    case 'updateExpense':
        updateExpense();
        break;

    case 'deleteExpense':
        deleteExpense();
        break;

    // 📊 RÉCAPS
    case 'getGlobalSummary':
        getGlobalSummary();
        break;

    case 'getProjectsSummary':
        getProjectsSummary();
        break;

    default:
        jsonError("Action inconnue : $action");
}
