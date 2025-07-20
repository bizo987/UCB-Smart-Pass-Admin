<?php
/**
 * Contrôleur API de base
 * SmartAccess UCB - Université Catholique de Bukavu
 */

abstract class BaseApiController {
    
    public function __construct() {
        $this->setHeaders();
        $this->handleCors();
    }

    /**
     * Définir les en-têtes HTTP
     */
    private function setHeaders() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /**
     * Gérer les requêtes CORS
     */
    private function handleCors() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Vérifier l'authentification
     */
    protected function requireAuth() {
        $session = Session::getInstance();
        if (!$session->isAdminLoggedIn()) {
            $this->sendError('Non authentifié', 401);
        }
    }

    /**
     * Obtenir les données JSON de la requête
     */
    protected function getJsonInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Données JSON invalides', 400);
        }
        return $input;
    }

    /**
     * Envoyer une réponse de succès
     */
    protected function sendSuccess($data = null, $message = null, $code = 200) {
        http_response_code($code);
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Envoyer une réponse d'erreur
     */
    protected function sendError($message, $code = 500, $details = null) {
        http_response_code($code);
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($details) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Valider la méthode HTTP
     */
    protected function validateMethod($allowedMethods) {
        $method = $_SERVER['REQUEST_METHOD'];
        if (!in_array($method, $allowedMethods)) {
            $this->sendError('Méthode non autorisée', 405);
        }
        return $method;
    }

    /**
     * Gérer les exceptions
     */
    protected function handleException(Exception $e) {
        error_log("Erreur API: " . $e->getMessage());
        $this->sendError($e->getMessage());
    }
}