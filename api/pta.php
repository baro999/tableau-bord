<?php
// api/pta.php
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
        // Récupérer toutes les activités PTA
        try {
            $query = "SELECT * FROM pta_activites ORDER BY date_debut DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Ajouter une activité PTA
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation des données
            if (empty($data['direction']) || empty($data['activite'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données incomplètes']);
                exit();
            }
            
            $query = "INSERT INTO pta_activites 
                      (direction, reference, activite, livrable, montant, responsable, 
                       date_debut, date_fin, avancement, created_by) 
                      VALUES 
                      (:direction, :reference, :activite, :livrable, :montant, :responsable,
                       :date_debut, :date_fin, :avancement, :created_by)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':reference', $data['reference']);
            $stmt->bindParam(':activite', $data['activite']);
            $stmt->bindParam(':livrable', $data['livrable']);
            $stmt->bindParam(':montant', $data['montant']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':avancement', $data['avancement']);
            $stmt->bindParam(':created_by', $_SESSION['utilisateur_id']);
            
            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                $activite_courte = substr($data['activite'], 0, 50) . '...';
                ajouterHistorique('PTA', 'Ajout', "Nouvelle activité: {$activite_courte}", $conn);
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
        // Modifier une activité PTA
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            $query = "UPDATE pta_activites SET 
                      direction = :direction,
                      reference = :reference,
                      activite = :activite,
                      livrable = :livrable,
                      montant = :montant,
                      responsable = :responsable,
                      date_debut = :date_debut,
                      date_fin = :date_fin,
                      avancement = :avancement
                      WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':reference', $data['reference']);
            $stmt->bindParam(':activite', $data['activite']);
            $stmt->bindParam(':livrable', $data['livrable']);
            $stmt->bindParam(':montant', $data['montant']);
            $stmt->bindParam(':responsable', $data['responsable']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':avancement', $data['avancement']);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                $activite_courte = substr($data['activite'], 0, 50) . '...';
                ajouterHistorique('PTA', 'Modification', "Activité modifiée: {$activite_courte}", $conn);
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
        // Supprimer une activité PTA
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Récupérer l'activité pour l'historique
            $query = "SELECT activite FROM pta_activites WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $pta = $stmt->fetch();
            
            if (!$pta) {
                http_response_code(404);
                echo json_encode(['error' => 'Activité non trouvée']);
                exit();
            }
            
            $query = "DELETE FROM pta_activites WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $activite_courte = substr($pta['activite'], 0, 50) . '...';
                ajouterHistorique('PTA', 'Suppression', "Activité supprimée: {$activite_courte}", $conn);
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