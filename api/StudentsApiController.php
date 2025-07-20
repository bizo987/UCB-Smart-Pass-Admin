<?php
/**
 * API REST pour la gestion des étudiants
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Etudiant.php';

class StudentsApiController extends BaseApiController {
    private $etudiantModel;

    public function __construct() {
        parent::__construct();
        $this->etudiantModel = new Etudiant();
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
     * Récupérer la liste des étudiants
     */
    private function handleGet() {
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 50);
        
        $students = $this->etudiantModel->search($search, $page, $limit);
        
        $this->sendSuccess([
            'students' => $students,
            'count' => count($students)
        ]);
    }

    /**
     * Créer un nouvel étudiant
     */
    private function handlePost() {
        $input = $this->getJsonInput();
        
        if (!$input) {
            $this->sendError('Données JSON invalides', 400);
        }
        
        $studentId = $this->etudiantModel->createEtudiant($input);
        
        $this->sendSuccess([
            'student_id' => $studentId
        ], 'Étudiant créé avec succès', 201);
    }

    /**
     * Mettre à jour un étudiant
     */
    private function handlePut() {
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->sendError('ID étudiant requis', 400);
        }
        
        $studentId = (int)$input['id'];
        
        // Vérifier l'existence
        if (!$this->etudiantModel->findById($studentId)) {
            $this->sendError('Étudiant introuvable', 404);
        }
        
        $this->etudiantModel->updateEtudiant($studentId, $input);
        
        $this->sendSuccess(null, 'Étudiant mis à jour avec succès');
    }

    /**
     * Supprimer un étudiant
     */
    private function handleDelete() {
        $input = $this->getJsonInput();
        
        if (!$input || empty($input['id'])) {
            $this->sendError('ID étudiant requis', 400);
        }
        
        $studentId = (int)$input['id'];
        
        // Vérifier l'existence
        if (!$this->etudiantModel->findById($studentId)) {
            $this->sendError('Étudiant introuvable', 404);
        }
        
        $this->etudiantModel->delete($studentId);
        
        $this->sendSuccess(null, 'Étudiant supprimé avec succès');
    }
}