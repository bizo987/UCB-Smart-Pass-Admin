<?php
/**
 * API REST pour la gestion des salles
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/SallesApiController.php';

$controller = new SallesApiController();
$controller->handle();