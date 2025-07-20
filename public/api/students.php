<?php
/**
 * API REST pour la gestion des étudiants
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/StudentsApiController.php';

$controller = new StudentsApiController();
$controller->handle();