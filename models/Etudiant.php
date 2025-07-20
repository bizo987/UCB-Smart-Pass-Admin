<?php
/**
 * Modèle Etudiant
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../core/Validator.php';

class Etudiant extends BaseModel {
    protected $table = 'etudiants';

    /**
     * Trouver un étudiant par matricule
     */
    public function findByMatricule($matricule) {
        $query = "SELECT * FROM {$this->table} WHERE matricule = ? AND actif = 1";
        $result = $this->db->executeQuery($query, [$matricule], 's');
        return $result->fetch_assoc();
    }

    /**
     * Rechercher des étudiants
     */
    public function search($search = '', $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        $conditions = "actif = 1";
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $conditions .= " AND (matricule LIKE ? OR nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            $types = 'ssss';
        }
        
        $query = "SELECT * FROM {$this->table} WHERE $conditions ORDER BY nom, prenom LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $result = $this->db->executeQuery($query, $params, $types);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Créer un nouvel étudiant
     */
    public function createEtudiant($data) {
        // Validation
        $rules = [
            'matricule' => ['required' => true, 'matricule' => true],
            'nom' => ['required' => true, 'max_length' => 100],
            'prenom' => ['required' => true, 'max_length' => 100],
            'email' => ['email' => true],
            'faculte' => ['max_length' => 100],
            'promotion' => ['max_length' => 50]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Vérifier l'unicité du matricule
        if ($this->findByMatricule($data['matricule'])) {
            throw new Exception('Un étudiant avec ce matricule existe déjà');
        }

        return $this->create($data);
    }

    /**
     * Mettre à jour un étudiant
     */
    public function updateEtudiant($id, $data) {
        // Validation
        $rules = [
            'matricule' => ['required' => true, 'matricule' => true],
            'nom' => ['required' => true, 'max_length' => 100],
            'prenom' => ['required' => true, 'max_length' => 100],
            'email' => ['email' => true],
            'faculte' => ['max_length' => 100],
            'promotion' => ['max_length' => 50]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Vérifier l'unicité du matricule (sauf pour l'étudiant actuel)
        $existing = $this->findByMatricule($data['matricule']);
        if ($existing && $existing['id'] != $id) {
            throw new Exception('Un autre étudiant avec ce matricule existe déjà');
        }

        return $this->update($id, $data);
    }

    /**
     * Obtenir les étudiants par faculté et promotion
     */
    public function findByFaculteAndPromotion($faculte, $promotion) {
        $query = "SELECT * FROM {$this->table} WHERE faculte = ? AND promotion = ? AND actif = 1";
        $result = $this->db->executeQuery($query, [$faculte, $promotion], 'ss');
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}