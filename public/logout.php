<?php
/**
 * Déconnexion administrateur
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();
$authController->logout();