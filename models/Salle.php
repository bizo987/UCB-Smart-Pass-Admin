<?php
/**
 * Modèle Salle
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../core/Validator.php';

class Salle extends BaseModel {
    protected $table = 'salles';

    /**
     * Obtenir toutes les salles actives
     */
    public function getAllActive() {
        return $this->findAll('actif = 1');
    }

    /**
     * Trouver une salle par nom
     */
    public function findByName($nom) {
        $query = "SELECT * FROM {$this->table} WHERE nom_salle = ? AND actif = 1";
        $result = $this->db->executeQuery($query, [$nom], 's');
        return $result->fetch_assoc();
    }

    /**
     * Créer une nouvelle salle
     */
    public function createSalle($data) {
        // Validation
        $rules = [
            'nom_salle' => ['required' => true, 'max_length' => 100],
            'localisation' => ['max_length' => 200],
            'description' => ['max_length' => 1000],
            'capacite' => ['integer' => true]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Vérifier l'unicité du nom
        if ($this->findByName($data['nom_salle'])) {
            throw new Exception('Une salle avec ce nom existe déjà');
        }

        return $this->create($data);
    }

    /**
     * Mettre à jour une salle
     */
    public function updateSalle($id, $data) {
        // Validation
        $rules = [
            'nom_salle' => ['required' => true, 'max_length' => 100],
            'localisation' => ['max_length' => 200],
            'description' => ['max_length' => 1000],
            'capacite' => ['integer' => true]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Vérifier l'unicité du nom (sauf pour la salle actuelle)
        $existing = $this->findByName($data['nom_salle']);
        if ($existing && $existing['id'] != $id) {
            throw new Exception('Une autre salle avec ce nom existe déjà');
        }

        return $this->update($id, $data);
    }

    /**
     * Supprimer une salle (avec vérification des autorisations)
     */
    public function deleteSalle($id) {
        // Vérifier s'il y a des autorisations actives
        $query = "SELECT COUNT(*) as count FROM autorisations WHERE salle_id = ? AND actif = 1";
        $result = $this->db->executeQuery($query, [$id], 'i');
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            throw new Exception("Impossible de supprimer cette salle car elle a $count autorisation(s) active(s)");
        }

        return $this->delete($id);
    }
}