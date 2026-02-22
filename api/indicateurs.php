<?php
// api/indicateurs.php
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
        // Récupérer tous les indicateurs
        try {
            $query = "SELECT * FROM indicateurs ORDER BY direction, nom";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Ajouter un indicateur
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation des données
            if (empty($data['direction']) || empty($data['axe']) || empty($data['objectif']) || 
                empty($data['nom']) || !isset($data['valeur']) || !isset($data['cible'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données incomplètes']);
                exit();
            }
            
            $query = "INSERT INTO indicateurs 
                      (direction, axe, objectif, nom, actions, valeur, cible, unite, 
                       methode, responsable, periodicite, source, created_by) 
                      VALUES 
                      (:direction, :axe, :objectif, :nom, :actions, :valeur, :cible, :unite,
                       :methode, :responsable, :periodicite, :source, :created_by)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':axe', $data['axe']);
            $stmt->bindParam(':objectif', $data['objectif']);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':actions', $data['actions']);
            $stmt->bindParam(':valeur', $data['valeur']);
            $stmt->bindParam(':cible', $data['cible']);
            $stmt->bindParam(':unite', $data['unite']);
            $stmt->bindParam(':methode', $data['methode']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':periodicite', $data['periodicite']);
            $stmt->bindParam(':source', $data['source']);
            $stmt->bindParam(':created_by', $_SESSION['utilisateur_id']);
            
            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                ajouterHistorique('Indicateurs', 'Ajout', "Nouvel indicateur: {$data['nom']}", $conn);
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'insertion']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Modifier un indicateur
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            $query = "UPDATE indicateurs SET 
                      direction = :direction, 
                      axe = :axe, 
                      objectif = :objectif,
                      nom = :nom, 
                      actions = :actions, 
                      valeur = :valeur,
                      cible = :cible, 
                      unite = :unite, 
                      methode = :methode,
                      responsable = :responsable, 
                      periodicite = :periodicite,
                      source = :source
                      WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':axe', $data['axe']);
            $stmt->bindParam(':objectif', $data['objectif']);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':actions', $data['actions']);
            $stmt->bindParam(':valeur', $data['valeur']);
            $stmt->bindParam(':cible', $data['cible']);
            $stmt->bindParam(':unite', $data['unite']);
            $stmt->bindParam(':methode', $data['methode']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':periodicite', $data['periodicite']);
            $stmt->bindParam(':source', $data['source']);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                ajouterHistorique('Indicateurs', 'Modification', "Indicateur modifié: {$data['nom']}", $conn);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Supprimer un indicateur
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Récupérer le nom pour l'historique
            $query = "SELECT nom FROM indicateurs WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $ind = $stmt->fetch();
            
            if (!$ind) {
                http_response_code(404);
                echo json_encode(['error' => 'Indicateur non trouvé']);
                exit();
            }
            
            $query = "DELETE FROM indicateurs WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                ajouterHistorique('Indicateurs', 'Suppression', "Indicateur supprimé: {$ind['nom']}", $conn);
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