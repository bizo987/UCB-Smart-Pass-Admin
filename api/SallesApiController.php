<?php
/**
 * API REST pour la gestion des salles
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Salle.php';

class SallesApiController extends BaseApiController {
    private $salleModel;

    public function __construct() {
        parent::__construct();
        $this->salleModel = new Salle();
    }

    /**
     * Point d'entrée principal
     */
    public function handle() {
        $this->requireAuth();
        
        try {
            $method = $this->validateMethod(['GET', 'POST', 'PUT', 'DELETE']);
            
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Récupérer la liste des salles
     */
    private function handleGet() {
        $salles = $this->salleModel->getAllActive();
        
        $this->sendSuccess([
            'salles' => $salles,
            'count' => count($salles)
        ]);
    }

    /**
     * Créer une nouvelle salle
     */
    private function handlePost() {
        $input = $this->getJsonInput();
        
        if (!$input) {
            $this->sendError('Données JSON invalides', 400);
        }
        
        $salleId = $this->salleModel->createSalle($input);
        
        $this->sendSuccess([
            'salle_id' => $salleId
        ], 'Salle créée avec succès', 201);
    }

    /**
     * Mettre à jour une salle
     */
    private function handlePut() {
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->sendError('ID salle requis', 400);
        }
        
        $salleId = (int)$input['id'];
        
        // Vérifier l'existence
        if (!$this->salleModel->findById($salleId)) {
            $this->sendError('Salle introuvable', 404);
        }
        
        $this->salleModel->updateSalle($salleId, $input);
        
        $this->sendSuccess(null, 'Salle mise à jour avec succès');
    }

    /**
     * Supprimer une salle
     */
    private function handleDelete() {
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->sendError('ID salle requis', 400);
        }
        
        $salleId = (int)$input['id'];
        
        // Vérifier l'existence
        if (!$this->salleModel->findById($salleId)) {
            $this->sendError('Salle introuvable', 404);
        }
        
        $this->salleModel->deleteSalle($salleId);
        
        $this->sendSuccess(null, 'Salle supprimée avec succès');
    }
}