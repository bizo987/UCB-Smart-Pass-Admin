<?php
/**
 * API pour les statistiques du tableau de bord
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../../api/BaseApiController.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../models/Etudiant.php';
require_once __DIR__ . '/../../models/Salle.php';
require_once __DIR__ . '/../../models/Autorisation.php';
require_once __DIR__ . '/../../models/HistoriqueAcces.php';

class DashboardStatsController extends BaseApiController {
    private $etudiantModel;
    private $salleModel;
    private $autorisationModel;
    private $historiqueModel;

    public function __construct() {
        parent::__construct();
        $this->etudiantModel = new Etudiant();
        $this->salleModel = new Salle();
        $this->autorisationModel = new Autorisation();
        $this->historiqueModel = new HistoriqueAcces();
    }

    public function handle() {
        $this->requireAuth();
        
        try {
            $method = $this->validateMethod(['GET']);
            
            if ($method === 'GET') {
                $this->getStats();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    private function getStats() {
        try {
            $stats = [
                'etudiants' => $this->etudiantModel->count('actif = 1'),
                'salles' => $this->salleModel->count('actif = 1'),
                'autorisations' => $this->autorisationModel->count('actif = 1'),
                'acces_aujourd_hui' => $this->historiqueModel->count('DATE(date_entree) = CURDATE()')
            ];

            $this->sendSuccess(['stats' => $stats]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}

$controller = new DashboardStatsController();
$controller->handle();
?>