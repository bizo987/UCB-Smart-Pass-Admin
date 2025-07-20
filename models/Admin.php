<?php
/**
 * Modèle Admin
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/BaseModel.php';

class Admin extends BaseModel {
    protected $table = 'admins';

    /**
     * Trouver un admin par nom d'utilisateur
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM {$this->table} WHERE username = ?";
        $result = $this->db->executeQuery($query, [$username], 's');
        return $result->fetch_assoc();
    }

    /**
     * Vérifier les identifiants de connexion
     */
    public function authenticate($username, $password) {
        $admin = $this->findByUsername($username);
        
        if ($admin && $password === $admin['password']) {
            return $admin;
        }
        
        return false;
    }

    /**
     * Créer un nouvel administrateur
     */
    public function createAdmin($data) {
        // Validation
        $rules = [
            'username' => ['required' => true, 'min_length' => 3, 'max_length' => 50],
            'password' => ['required' => true, 'min_length' => 6],
            'email' => ['email' => true],
            'nom' => ['required' => true, 'max_length' => 100],
            'prenom' => ['required' => true, 'max_length' => 100]
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            throw new Exception('Données invalides: ' . json_encode($errors));
        }

        // Vérifier l'unicité du nom d'utilisateur
        if ($this->findByUsername($data['username'])) {
            throw new Exception('Ce nom d\'utilisateur existe déjà');
        }

        return $this->create($data);
    }
}