<?php
/**
 * API REST pour la gestion des autorisations
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/AutorisationsApiController.php';

$controller = new AutorisationsApiController();
$controller->handle();