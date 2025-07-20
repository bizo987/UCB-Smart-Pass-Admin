<?php
/**
 * API REST pour la gestion des autorisations d'accès
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Autorisation.php';
require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Salle.php';

class AutorisationsApiController extends BaseApiController {
    private $autorisationModel;
    private $etudiantModel;
    private $salleModel;

    public function __construct() {
        parent::__construct();
        $this->autorisationModel = new Autorisation();
        $this->etudiantModel = new Etudiant();
        $this->salleModel = new Salle();
    }

    /**
     * Point d'entrée principal
     */
    public function handle() {
        $this->requireAuth();
        
        try {
            $method = $this->validateMethod(['GET', 'POST', 'DELETE']);
            
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
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
     * Récupérer la liste des autorisations
     */
    private function handleGet() {
        $autorisations = $this->autorisationModel->getAllWithDetails();
        
        $this->sendSuccess([
            'autorisations' => $autorisations,
            'count' => count($autorisations)
        ]);
    }

    /**
     * Créer une nouvelle autorisation
     */
    private function handlePost() {
        $input = $this->getJsonInput();
        
        if (!$input) {
            $this->sendError('Données JSON invalides', 400);
        }
        
        $type = $input['type'] ?? 'individual';
        
        if ($type === 'individual') {
            $this->handleIndividualAutorisation($input);
        } elseif ($type === 'group') {
            $this->handleGroupAutorisation($input);
        } else {
            $this->sendError('Type d\'autorisation non supporté', 400);
        }
    }

    /**
     * Gérer l'attribution individuelle
     */
    private function handleIndividualAutorisation($input) {
        // Vérifier l'existence de l'étudiant et de la salle
        if (!$this->etudiantModel->findById($input['etudiant_id'])) {
            $this->sendError('Étudiant introuvable', 404);
        }
        
        if (!$this->salleModel->findById($input['salle_id'])) {
            $this->sendError('Salle introuvable', 404);
        }
        
        $autorisationId = $this->autorisationModel->createAutorisation($input);
        
        $this->sendSuccess([
            'autorisation_id' => $autorisationId
        ], 'Autorisation individuelle créée avec succès', 201);
    }

    /**
     * Gérer l'attribution groupée
     */
    private function handleGroupAutorisation($input) {
        // Vérifier l'existence de la salle
        if (!$this->salleModel->findById($input['salle_id'])) {
            $this->sendError('Salle introuvable', 404);
        }
        
        $count = $this->autorisationModel->createGroupAutorisation(
            $input['faculte'],
            $input['promotion'],
            $input['salle_id'],
            $input['date_debut'],
            $input['date_fin']
        );
        
        if ($count === 0) {
            $this->sendError('Aucun étudiant trouvé pour cette faculté/promotion', 404);
        }
        
        $this->sendSuccess([
            'count' => $count
        ], "Attribution groupée réussie pour $count étudiant(s)", 201);
    }

    /**
     * Supprimer/révoquer une autorisation
     */
    private function handleDelete() {
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->sendError('ID autorisation requis', 400);
        }
        
        $autorisationId = (int)$input['id'];
        
        // Vérifier l'existence
        if (!$this->autorisationModel->findById($autorisationId)) {
            $this->sendError('Autorisation introuvable', 404);
        }
        
        $this->autorisationModel->revokeAutorisation($autorisationId);
        
        $this->sendSuccess(null, 'Autorisation révoquée avec succès');
    }
}