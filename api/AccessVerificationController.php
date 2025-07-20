<?php
/**
 * API de vérification d'accès
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../services/AccessService.php';
require_once __DIR__ . '/../core/Validator.php';

class AccessVerificationController extends BaseApiController {
    private $accessService;

    public function __construct() {
        parent::__construct();
        $this->accessService = new AccessService();
    }

    /**
     * Point d'entrée principal
     */
    public function handle() {
        try {
            $method = $this->validateMethod(['GET']);
            
            if ($method === 'GET') {
                $this->verifyAccess();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Vérifier l'accès d'un étudiant
     */
    private function verifyAccess() {
        // Récupération et validation des paramètres
        $matricule = $_GET['matricule'] ?? '';
        $salle_id = $_GET['salle_id'] ?? '';

        if (empty($matricule)) {
            $this->sendError('Le paramètre "matricule" est requis.', 400);
        }

        if (empty($salle_id) || !is_numeric($salle_id)) {
            $this->sendError('Le paramètre "salle_id" est requis et doit être numérique.', 400);
        }

        // Validation du format du matricule
        if (!Validator::isValidMatricule($matricule)) {
            $result = [
                'status' => 'ACCES REFUSE',
                'message' => 'Format de matricule invalide. Format attendu: XX/YY.ZZZZZ',
                'matricule' => $matricule,
                'salle_id' => (int)$salle_id,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            http_response_code(400);
            echo json_encode($result, JSON_PRETTY_PRINT);
            exit;
        }

        // Vérification de l'accès
        $result = $this->accessService->verifyAccess($matricule, (int)$salle_id);

        // Ajout d'informations supplémentaires
        $result['matricule'] = $matricule;
        $result['salle_id'] = (int)$salle_id;
        $result['timestamp'] = date('Y-m-d H:i:s');

        // Code de statut HTTP selon le résultat
        if ($result['status'] === 'ACCES AUTORISE') {
            http_response_code(200);
        } else {
            http_response_code(403);
        }

        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}