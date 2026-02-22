<?php
// api/projets.php
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
        // Récupérer tous les projets avec leurs marchés et équipes
        try {
            $query = "SELECT * FROM projets ORDER BY date_creation DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $projets = $stmt->fetchAll();
            
            // Pour chaque projet, récupérer les marchés et l'équipe
            foreach ($projets as &$projet) {
                // Récupérer les marchés
                $query = "SELECT * FROM marches WHERE projet_id = :projet_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':projet_id', $projet['id']);
                $stmt->execute();
                $projet['marches'] = $stmt->fetchAll();
                
                // Récupérer l'équipe
                $query = "SELECT * FROM equipe_projet WHERE projet_id = :projet_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':projet_id', $projet['id']);
                $stmt->execute();
                $projet['equipe'] = $stmt->fetchAll();
            }
            
            echo json_encode($projets);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Ajouter un projet
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation des données
            if (empty($data['titre']) || empty($data['direction'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données incomplètes']);
                exit();
            }
            
            $conn->beginTransaction();
            
            // Insertion du projet
            $query = "INSERT INTO projets 
                      (titre, direction, phase, duree, zone, description, objectif, 
                       maitrise_ouvrage, date_debut, date_fin, budget, avancement, created_by) 
                      VALUES 
                      (:titre, :direction, :phase, :duree, :zone, :description, :objectif,
                       :maitrise_ouvrage, :date_debut, :date_fin, :budget, :avancement, :created_by)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':titre', $data['titre']);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':phase', $data['phase']);
            $stmt->bindParam(':duree', $data['duree']);
            $stmt->bindParam(':zone', $data['zone']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':objectif', $data['objectif']);
            $stmt->bindParam(':maitrise_ouvrage', $data['maitrise_ouvrage']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':budget', $data['budget']);
            $stmt->bindParam(':avancement', $data['avancement']);
            $stmt->bindParam(':created_by', $_SESSION['utilisateur_id']);
            
            if (!$stmt->execute()) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'insertion du projet']);
                exit();
            }
            
            $projet_id = $conn->lastInsertId();
            
            // Insertion des marchés
            if (!empty($data['marches']) && is_array($data['marches'])) {
                $query = "INSERT INTO marches (projet_id, marche, titulaire, montant, bailleur, type) 
                          VALUES (:projet_id, :marche, :titulaire, :montant, :bailleur, :type)";
                $stmt = $conn->prepare($query);
                
                foreach ($data['marches'] as $marche) {
                    $stmt->bindParam(':projet_id', $projet_id);
                    $stmt->bindParam(':marche', $marche['marche']);
                    $stmt->bindParam(':titulaire', $marche['titulaire']);
                    $stmt->bindParam(':montant', $marche['montant']);
                    $stmt->bindParam(':bailleur', $marche['bailleur']);
                    $stmt->bindParam(':type', $marche['type']);
                    $stmt->execute();
                }
            }
            
            // Insertion de l'équipe
            if (!empty($data['equipe']) && is_array($data['equipe'])) {
                $query = "INSERT INTO equipe_projet (projet_id, poste, nom, prenom) 
                          VALUES (:projet_id, :poste, :nom, :prenom)";
                $stmt = $conn->prepare($query);
                
                foreach ($data['equipe'] as $membre) {
                    $stmt->bindParam(':projet_id', $projet_id);
                    $stmt->bindParam(':poste', $membre['poste']);
                    $stmt->bindParam(':nom', $membre['nom']);
                    $stmt->bindParam(':prenom', $membre['prenom']);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            ajouterHistorique('Projets', 'Ajout', "Nouveau projet: {$data['titre']}", $conn);
            echo json_encode(['success' => true, 'id' => $projet_id]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Modifier un projet
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            $conn->beginTransaction();
            
            // Mise à jour du projet
            $query = "UPDATE projets SET 
                      titre = :titre,
                      direction = :direction,
                      phase = :phase,
                      duree = :duree,
                      zone = :zone,
                      description = :description,
                      objectif = :objectif,
                      maitrise_ouvrage = :maitrise_ouvrage,
                      date_debut = :date_debut,
                      date_fin = :date_fin,
                      budget = :budget,
                      avancement = :avancement
                      WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':titre', $data['titre']);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':phase', $data['phase']);
            $stmt->bindParam(':duree', $data['duree']);
            $stmt->bindParam(':zone', $data['zone']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':objectif', $data['objectif']);
            $stmt->bindParam(':maitrise_ouvrage', $data['maitrise_ouvrage']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':budget', $data['budget']);
            $stmt->bindParam(':avancement', $data['avancement']);
            $stmt->bindParam(':id', $data['id']);
            
            if (!$stmt->execute()) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du projet']);
                exit();
            }
            
            // Supprimer les anciens marchés et équipe
            $query = "DELETE FROM marches WHERE projet_id = :projet_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':projet_id', $data['id']);
            $stmt->execute();
            
            $query = "DELETE FROM equipe_projet WHERE projet_id = :projet_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':projet_id', $data['id']);
            $stmt->execute();
            
            // Réinsérer les nouveaux marchés
            if (!empty($data['marches']) && is_array($data['marches'])) {
                $query = "INSERT INTO marches (projet_id, marche, titulaire, montant, bailleur, type) 
                          VALUES (:projet_id, :marche, :titulaire, :montant, :bailleur, :type)";
                $stmt = $conn->prepare($query);
                
                foreach ($data['marches'] as $marche) {
                    $stmt->bindParam(':projet_id', $data['id']);
                    $stmt->bindParam(':marche', $marche['marche']);
                    $stmt->bindParam(':titulaire', $marche['titulaire']);
                    $stmt->bindParam(':montant', $marche['montant']);
                    $stmt->bindParam(':bailleur', $marche['bailleur']);
                    $stmt->bindParam(':type', $marche['type']);
                    $stmt->execute();
                }
            }
            
            // Réinsérer la nouvelle équipe
            if (!empty($data['equipe']) && is_array($data['equipe'])) {
                $query = "INSERT INTO equipe_projet (projet_id, poste, nom, prenom) 
                          VALUES (:projet_id, :poste, :nom, :prenom)";
                $stmt = $conn->prepare($query);
                
                foreach ($data['equipe'] as $membre) {
                    $stmt->bindParam(':projet_id', $data['id']);
                    $stmt->bindParam(':poste', $membre['poste']);
                    $stmt->bindParam(':nom', $membre['nom']);
                    $stmt->bindParam(':prenom', $membre['prenom']);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            ajouterHistorique('Projets', 'Modification', "Projet modifié: {$data['titre']}", $conn);
            echo json_encode(['success' => true]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Supprimer un projet
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Récupérer le titre pour l'historique
            $query = "SELECT titre FROM projets WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $projet = $stmt->fetch();
            
            if (!$projet) {
                http_response_code(404);
                echo json_encode(['error' => 'Projet non trouvé']);
                exit();
            }
            
            // La suppression en cascade va supprimer les marchés et équipe automatiquement
            $query = "DELETE FROM projets WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                ajouterHistorique('Projets', 'Suppression', "Projet supprimé: {$projet['titre']}", $conn);
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