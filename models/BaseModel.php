<?php
/**
 * Modèle de base pour tous les modèles
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = DatabaseConfig::getInstance();
    }

    /**
     * Trouver un enregistrement par ID
     */
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->executeQuery($query, [$id], 'i');
        return $result->fetch_assoc();
    }

    /**
     * Trouver tous les enregistrements
     */
    public function findAll($conditions = '', $params = [], $types = '') {
        $query = "SELECT * FROM {$this->table}";
        if ($conditions) {
            $query .= " WHERE " . $conditions;
        }
        $query .= " ORDER BY {$this->primaryKey} DESC";
        
        $result = $this->db->executeQuery($query, $params, $types);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Créer un nouvel enregistrement
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $types = str_repeat('s', count($fields));
        
        $query = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $this->db->executeQuery($query, array_values($data), $types);
        
        return $this->db->getLastInsertId();
    }

    /**
     * Mettre à jour un enregistrement
     */
    public function update($id, $data) {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        $types = str_repeat('s', count($fields)) . 'i';
        
        $query = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        $params = array_merge(array_values($data), [$id]);
        
        $this->db->executeQuery($query, $params, $types);
        return true;
    }

    /**
     * Supprimer un enregistrement (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET actif = 0 WHERE {$this->primaryKey} = ?";
        $this->db->executeQuery($query, [$id], 'i');
        return true;
    }

    /**
     * Compter les enregistrements
     */
    public function count($conditions = '', $params = [], $types = '') {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($conditions) {
            $query .= " WHERE " . $conditions;
        }
        
        $result = $this->db->executeQuery($query, $params, $types);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Vérifier si un enregistrement existe
     */
    public function exists($conditions, $params = [], $types = '') {
        return $this->count($conditions, $params, $types) > 0;
    }
}