<?php
/**
 * Contrôleur d'authentification
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../core/Validator.php';

class AuthController {
    private $session;
    private $adminModel;

    public function __construct() {
        $this->session = Session::getInstance();
        $this->adminModel = new Admin();
    }

    /**
     * Afficher la page de connexion
     */
    public function showLogin() {
        // Redirection si déjà connecté
        if ($this->session->isAdminLoggedIn()) {
            header('Location: /dashboard.php');
            exit;
        }

        $error = '';
        $success = '';

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processLogin();
            if ($result['success']) {
                $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirectUrl");
                exit;
            } else {
                $error = $result['message'];
            }
        }

        // Inclure la vue
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Traiter la connexion
     */
    private function processLogin() {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs.'];
        }

        try {
            // Authentification
            $admin = $this->adminModel->authenticate($username, $password);
            
            if ($admin) {
                $this->session->loginAdmin($admin);
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Identifiants incorrects.'];
            }
        } catch (Exception $e) {
            error_log("Erreur login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion. Veuillez réessayer.'];
        }
    }

    /**
     * Déconnexion
     */
    public function logout() {
        $this->session->logoutAdmin();
        header('Location: /login.php?message=logout_success');
        exit;
    }

    /**
     * Middleware d'authentification
     */
    public function requireAuth() {
        $this->session->requireAdmin();
        $this->session->updateLastActivity();
    }
}