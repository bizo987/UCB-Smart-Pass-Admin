<?php
/**
 * Configuration de la base de données
 * SmartAccess UCB - Université Catholique de Bukavu
 */

class DatabaseConfig {
    private static $instance = null;
    private $connection;
    
    // Configuration de la base de données
    private $config = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '1234',
        'database' => 'smartaccess_ucb',
        'charset' => 'utf8mb4'
    ];

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $this->connection = new mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database']
            );

            if ($this->connection->connect_error) {
                throw new Exception("Erreur de connexion à la base de données: " . $this->connection->connect_error);
            }

            $this->connection->set_charset($this->config['charset']);
            $this->connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

        } catch (Exception $e) {
            error_log("Erreur DB: " . $e->getMessage());
            
            if (getenv('APP_ENV') === 'production') {
                die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
            } else {
                die("Erreur de développement: " . $e->getMessage());
            }
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function executeQuery($query, $params = [], $types = '') {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $result = $stmt->execute();
        if (!$result) {
            throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
}