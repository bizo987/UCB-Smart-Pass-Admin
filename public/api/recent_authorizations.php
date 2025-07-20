<?php
/**
 * API pour les autorisations récentes
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/BaseApiController.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../models/Autorisation.php';

class RecentAuthorizationsController extends BaseApiController {
    private $autorisationModel;

    public function __construct() {
        parent::__construct();
        $this->autorisationModel = new Autorisation();
    }

    public function handle() {
        $this->requireAuth();
        
        try {
            $method = $this->validateMethod(['GET']);
            
            if ($method === 'GET') {
                $this->getRecentAuthorizations();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function getRecentAuthorizations() {
        try {
            $limit = (int)($_GET['limit'] ?? 5);
            $authorizations = array_slice($this->autorisationModel->getAllWithDetails(), 0, $limit);

            $this->sendSuccess(['authorizations' => $authorizations]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}

$controller = new RecentAuthorizationsController();
$controller->handle();
?>