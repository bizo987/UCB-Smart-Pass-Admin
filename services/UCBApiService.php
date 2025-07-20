<?php
/**
 * Service d'intégration avec l'API UCB
 * SmartAccess UCB - Université Catholique de Bukavu
 */

class UCBApiService {
    private $baseUrl = 'https://akhademie.ucbukavu.ac.cd/api/v1';

    /**
     * Obtenir les informations d'un étudiant par matricule
     */
    public function getStudentByMatricule($matricule) {
        $url = $this->baseUrl . '/school-students/read-by-matricule?matricule=' . urlencode($matricule);
        
        try {
            $response = $this->makeRequest($url);
            
            if ($response && isset($response['data']) && $response['message'] === "Request was successful") {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la requête: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir la liste des facultés et promotions
     */
    public function getFacultiesAndPromotions() {
        $url = $this->baseUrl . '/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined';
        
        try {
            $response = $this->makeRequest($url);
            
            if ($response && isset($response['data']) && $response['message'] === "Request was successful") {
                return [
                    'success' => true,
                    'entities' => $response['data']['entities'] ?? [],
                    'promotions' => $response['data']['promotions'] ?? []
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Données non disponibles'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la requête: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Effectuer une requête HTTP
     */
    private function makeRequest($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'header' => [
                    'User-Agent: SmartAccess UCB/1.0',
                    'Accept: application/json'
                ]
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Impossible de contacter l\'API UCB');
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Réponse JSON invalide');
        }
        
        return $data;
    }
}