<?php
/**
 * Tableau de bord administrateur
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

// Vérification de l'authentification
$authController = new AuthController();
$authController->requireAuth();

// Affichage du tableau de bord
$dashboardController = new DashboardController();
$dashboardController->index();