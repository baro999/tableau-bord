<?php
// api/problemes.php
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
        // Récupérer tous les problèmes
        try {
            $query = "SELECT * FROM problemes ORDER BY date_creation DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Ajouter un problème
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation des données
            if (empty($data['direction']) || empty($data['contrainte'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données incomplètes']);
                exit();
            }
            
            $query = "INSERT INTO problemes 
                      (direction, contrainte, solution, echeance, responsable, 
                       incidence, montant, statut, created_by) 
                      VALUES 
                      (:direction, :contrainte, :solution, :echeance, :responsable,
                       :incidence, :montant, :statut, :created_by)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':contrainte', $data['contrainte']);
            $stmt->bindParam(':solution', $data['solution']);
            $stmt->bindParam(':echeance', $data['echeance']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':incidence', $data['incidence']);
            $stmt->bindParam(':montant', $data['montant']);
            $stmt->bindParam(':statut', $data['statut']);
            $stmt->bindParam(':created_by', $_SESSION['utilisateur_id']);
            
            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                $contrainte_courte = substr($data['contrainte'], 0, 50) . '...';
                ajouterHistorique('Problem Solving', 'Ajout', "Nouveau problème: {$contrainte_courte}", $conn);
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
        // Modifier un problème
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            $query = "UPDATE problemes SET 
                      direction = :direction,
                      contrainte = :contrainte,
                      solution = :solution,
                      echeance = :echeance,
                      responsable = :responsable,
                      incidence = :incidence,
                      montant = :montant,
                      statut = :statut
                      WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':contrainte', $data['contrainte']);
            $stmt->bindParam(':solution', $data['solution']);
            $stmt->bindParam(':echeance', $data['echeance']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':incidence', $data['incidence']);
            $stmt->bindParam(':montant', $data['montant']);
            $stmt->bindParam(':statut', $data['statut']);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                $contrainte_courte = substr($data['contrainte'], 0, 50) . '...';
                ajouterHistorique('Problem Solving', 'Modification', "Problème modifié: {$contrainte_courte}", $conn);
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
        // Supprimer un problème
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Récupérer la contrainte pour l'historique
            $query = "SELECT contrainte FROM problemes WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $prob = $stmt->fetch();
            
            if (!$prob) {
                http_response_code(404);
                echo json_encode(['error' => 'Problème non trouvé']);
                exit();
            }
            
            $query = "DELETE FROM problemes WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $contrainte_courte = substr($prob['contrainte'], 0, 50) . '...';
                ajouterHistorique('Problem Solving', 'Suppression', "Problème supprimé: {$contrainte_courte}", $conn);
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