<?php
/**
 * Point d'entrée principal - SmartAccess UCB
 * Redirection vers le tableau de bord ou la page de connexion
 */

require_once __DIR__ . '/../core/Session.php';

$session = Session::getInstance();

// Redirection selon l'état de connexion
if ($session->isAdminLoggedIn()) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;