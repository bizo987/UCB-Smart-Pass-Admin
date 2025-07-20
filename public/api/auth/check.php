<?php
/**
 * API pour vérifier l'authentification
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../../api/BaseApiController.php';
require_once __DIR__ . '/../../../core/Session.php';

class AuthCheckController extends BaseApiController {
    private $session;

    public function __construct() {
        parent::__construct();
        $this->session = Session::getInstance();
    }

    public function handle() {
        try {
            $method = $this->validateMethod(['GET']);
            
            if ($method === 'GET') {
                $this->checkAuth();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function checkAuth() {
        if ($this->session->isAdminLoggedIn() && $this->session->isSessionValid()) {
            $admin = $this->session->getLoggedAdmin();
            $this->sendSuccess([
                'user' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nom' => $admin['nom'],
                    'prenom' => $admin['prenom'],
                    'email' => $admin['email']
                ]
            ]);
        } else {
            $this->sendError('Non authentifié', 401);
        }
    }
}

$controller = new AuthCheckController();
$controller->handle();
?>