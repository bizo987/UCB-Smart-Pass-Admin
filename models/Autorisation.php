<?php
/**
 * Modèle Autorisation
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../core/Validator.php';

class Autorisation extends BaseModel {
    protected $table = 'autorisations';

    /**
     * Obtenir les autorisations avec détails
     */
    public function getAllWithDetails() {
        $query = "SELECT a.*, e.matricule, e.nom, e.prenom, s.nom_salle 
                  FROM {$this->table} a
                  JOIN etudiants e ON a.etudiant_id = e.id
                  JOIN salles s ON a.salle_id = s.id
                  WHERE a.actif = 1
                  ORDER BY a.date_creation DESC";
        
        $result = $this->db->executeQuery($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Vérifier l'accès d'un étudiant à une salle
     */
    public function verifyAccess($matricule, $salle_id) {
        $query = "SELECT a.*, e.nom, e.prenom, s.nom_salle
                  FROM {$this->table} a
                  JOIN etudiants e ON a.etudiant_id = e.id
                  JOIN salles s ON a.salle_id = s.id
                  WHERE e.matricule = ? AND a.salle_id = ? AND a.actif = 1
                  AND NOW() BETWEEN a.date_debut AND a.date_fin";
        
        $result = $this->db->executeQuery($query, [$matricule, $salle_id], 'si');
        return $result->fetch_assoc();
    }

    /**
     * Créer une autorisation
     */
    public function createAutorisation($data) {
        // Validation
        $rules = [
            'etudiant_id' => ['required' => true, 'integer' => true],
            'salle_id' => ['required' => true, 'integer' => true],
            'date_debut' => ['required' => true],
            'date_fin' => ['required' => true]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Validation des dates
        if (!Validator::isDateAfter($data['date_fin'], $data['date_debut'])) {
            throw new Exception('La date de fin doit être postérieure à la date de début');
        }

        // Vérifier les conflits d'autorisations
        if ($this->hasConflict($data['etudiant_id'], $data['salle_id'], $data['date_debut'], $data['date_fin'])) {
            throw new Exception('Une autorisation existe déjà pour cette période');
        }

        return $this->create($data);
    }

    /**
     * Vérifier les conflits d'autorisations
     */
    private function hasConflict($etudiant_id, $salle_id, $date_debut, $date_fin, $exclude_id = null) {
        $query = "SELECT id FROM {$this->table} 
                  WHERE etudiant_id = ? AND salle_id = ? AND actif = 1
                  AND (
                      (date_debut <= ? AND date_fin >= ?) OR
                      (date_debut <= ? AND date_fin >= ?) OR
                      (date_debut >= ? AND date_fin <= ?)
                  )";
        
        $params = [$etudiant_id, $salle_id, $date_debut, $date_debut, $date_fin, $date_fin, $date_debut, $date_fin];
        $types = 'iissssss';
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }
        
        $result = $this->db->executeQuery($query, $params, $types);
        return $result->num_rows > 0;
    }

    /**
     * Attribution groupée d'autorisations
     */
    public function createGroupAutorisation($faculte, $promotion, $salle_id, $date_debut, $date_fin) {
        // Récupérer les étudiants de la faculté/promotion
        $query = "SELECT id FROM etudiants WHERE faculte = ? AND promotion = ? AND actif = 1";
        $result = $this->db->executeQuery($query, [$faculte, $promotion], 'ss');
        
        $count = 0;
        while ($etudiant = $result->fetch_assoc()) {
            // Vérifier si l'autorisation n'existe pas déjà
            if (!$this->hasConflict($etudiant['id'], $salle_id, $date_debut, $date_fin)) {
                $this->create([
                    'etudiant_id' => $etudiant['id'],
                    'salle_id' => $salle_id,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin
                ]);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Révoquer une autorisation
     */
    public function revokeAutorisation($id) {
        return $this->delete($id);
    }
}