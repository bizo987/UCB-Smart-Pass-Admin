<?php
/**
 * API pour les accès récents
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/BaseApiController.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../models/HistoriqueAcces.php';

class RecentAccessController extends BaseApiController {
    private $historiqueModel;

    public function __construct() {
        parent::__construct();
        $this->historiqueModel = new HistoriqueAcces();
    }

    public function handle() {
        $this->requireAuth();
        
        try {
            $method = $this->validateMethod(['GET']);
            
            if ($method === 'GET') {
                $this->getRecentAccess();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function getRecentAccess() {
        try {
            $limit = (int)($_GET['limit'] ?? 10);
            $access = $this->historiqueModel->getHistoriqueWithDetails($limit);

            $this->sendSuccess(['access' => $access]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}

$controller = new RecentAccessController();
$controller->handle();
?>