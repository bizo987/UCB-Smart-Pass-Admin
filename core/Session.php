<?php
/**
 * Gestionnaire de sessions sécurisées
 * SmartAccess UCB - Université Catholique de Bukavu
 */

class Session {
    private static $instance = null;

    private function __construct() {
        $this->startSecureSession();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }

    public function isAdminLoggedIn() {
        return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
    }

    public function getLoggedAdmin() {
        return $_SESSION['admin'] ?? null;
    }

    public function loginAdmin($adminData) {
        $_SESSION['admin'] = [
            'id' => $adminData['id'],
            'username' => $adminData['username'],
            'email' => $adminData['email'],
            'nom' => $adminData['nom'],
            'prenom' => $adminData['prenom'],
            'login_time' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        session_regenerate_id(true);
    }

    public function logoutAdmin() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }

    public function requireAdmin($redirectUrl = null) {
        if (!$this->isAdminLoggedIn()) {
            if ($redirectUrl) {
                $_SESSION['redirect_after_login'] = $redirectUrl;
            }
            header('Location: /login.php');
            exit;
        }
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function isSessionValid($maxLifetime = 7200) {
        if (!$this->isAdminLoggedIn()) {
            return false;
        }
        
        $admin = $this->getLoggedAdmin();
        $loginTime = $admin['login_time'] ?? 0;
        
        if (time() - $loginTime > $maxLifetime) {
            $this->logoutAdmin();
            return false;
        }
        
        return true;
    }

    public function updateLastActivity() {
        if ($this->isAdminLoggedIn()) {
            $_SESSION['admin']['last_activity'] = time();
        }
    }
}