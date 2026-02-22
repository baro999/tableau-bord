<?php
// api/historique.php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier la connexion
if (!estConnecte()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

$conn = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Récupérer l'historique
        try {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $module = isset($_GET['module']) ? $_GET['module'] : '';
            
            $query = "SELECT h.*, u.nom_complet 
                      FROM historique h 
                      LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id 
                      ORDER BY h.date_action DESC 
                      LIMIT :limit";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $historique = $stmt->fetchAll();
            
            // Filtrer par module si spécifié
            if (!empty($module) && $module !== 'all') {
                $historique = array_filter($historique, function($item) use ($module) {
                    return stripos($item['module'], $module) !== false;
                });
                $historique = array_values($historique);
            }
            
            echo json_encode($historique);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Effacer l'historique (admin seulement)
        if (!estAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès interdit']);
            exit();
        }
        
        try {
            $query = "DELETE FROM historique";
            $stmt = $conn->prepare($query);
            
            if ($stmt->execute()) {
                ajouterHistorique('Historique', 'Nettoyage', 'Historique effacé', $conn);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
?>