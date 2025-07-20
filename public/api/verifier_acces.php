<?php
/**
 * API de vérification d'accès
 * Endpoint: GET /api/verifier_acces.php?matricule=XXX&salle_id=YYY
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/AccessVerificationController.php';

$controller = new AccessVerificationController();
$controller->handle();