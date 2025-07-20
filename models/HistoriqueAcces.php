<?php
/**
 * Modèle HistoriqueAcces
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseModel.php';

class HistoriqueAcces extends BaseModel {
    protected $table = 'historiques_acces';

    /**
     * Enregistrer un accès
     */
    public function enregistrerAcces($etudiant_id, $salle_id, $matricule, $type_acces, $statut) {
        $data = [
            'etudiant_id' => $etudiant_id,
            'salle_id' => $salle_id,
            'matricule_utilise' => $matricule,
            'type_acces' => $type_acces,
            'statut' => $statut,
            'date_entree' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        return $this->create($data);
    }

    /**
     * Obtenir l'historique avec détails
     */
    public function getHistoriqueWithDetails($limit = 100) {
        $query = "SELECT h.*, e.nom, e.prenom, s.nom_salle
                  FROM {$this->table} h
                  LEFT JOIN etudiants e ON h.etudiant_id = e.id
                  LEFT JOIN salles s ON h.salle_id = s.id
                  ORDER BY h.date_entree DESC
                  LIMIT ?";
        
        $result = $this->db->executeQuery($query, [$limit], 'i');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les statistiques d'accès
     */
    public function getStatistics() {
        $stats = [];
        
        // Total des accès
        $stats['total'] = $this->count();
        
        // Accès autorisés
        $stats['autorises'] = $this->count('statut = ?', ['AUTORISE'], 's');
        
        // Accès refusés
        $stats['refuses'] = $this->count('statut = ?', ['REFUSE'], 's');
        
        // Accès aujourd'hui
        $stats['aujourd_hui'] = $this->count('DATE(date_entree) = CURDATE()');
        
        return $stats;
    }

    /**
     * Rechercher dans l'historique
     */
    public function searchHistorique($filters = []) {
        $conditions = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['search'])) {
            $conditions[] = "(e.nom LIKE ? OR e.prenom LIKE ? OR h.matricule_utilise LIKE ? OR s.nom_salle LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'ssss';
        }
        
        if (!empty($filters['statut'])) {
            $conditions[] = "h.statut = ?";
            $params[] = $filters['statut'];
            $types .= 's';
        }
        
        if (!empty($filters['date_debut'])) {
            $conditions[] = "DATE(h.date_entree) >= ?";
            $params[] = $filters['date_debut'];
            $types .= 's';
        }
        
        if (!empty($filters['date_fin'])) {
            $conditions[] = "DATE(h.date_entree) <= ?";
            $params[] = $filters['date_fin'];
            $types .= 's';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $query = "SELECT h.*, e.nom, e.prenom, s.nom_salle
                  FROM {$this->table} h
                  LEFT JOIN etudiants e ON h.etudiant_id = e.id
                  LEFT JOIN salles s ON h.salle_id = s.id
                  $whereClause
                  ORDER BY h.date_entree DESC";
        
        $result = $this->db->executeQuery($query, $params, $types);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}