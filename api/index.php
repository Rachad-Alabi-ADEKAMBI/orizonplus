<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

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

    // 💼 LIGNES BUDGÉTAIRES
    case 'createBudgetLine':
        createBudgetLine();
        break;

    case 'updateBudgetLine':
        updateBudgetLine();
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
