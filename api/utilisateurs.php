<?php
// api/utilisateurs.php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier la connexion et les droits admin
if (!estConnecte() || !estAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès interdit']);
    exit();
}

$conn = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Récupérer tous les utilisateurs
        try {
            $query = "SELECT id, nom_complet, email, role, direction, date_creation, derniere_connexion 
                      FROM utilisateurs 
                      ORDER BY nom_complet";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Ajouter un utilisateur
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation des données
            if (empty($data['nom_complet']) || empty($data['email']) || empty($data['mot_de_passe'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données incomplètes']);
                exit();
            }
            
            // Vérifier si l'email existe déjà
            $query = "SELECT id FROM utilisateurs WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Cet email est déjà utilisé']);
                exit();
            }
            
            $mot_de_passe_hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO utilisateurs 
                      (nom_complet, email, mot_de_passe, role, direction) 
                      VALUES 
                      (:nom_complet, :email, :mot_de_passe, :role, :direction)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom_complet', $data['nom_complet']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':direction', $data['direction']);
            
            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                ajouterHistorique('Utilisateurs', 'Ajout', "Nouvel utilisateur: {$data['nom_complet']}", $conn);
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
        // Modifier un utilisateur
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':id', $data['id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Cet email est déjà utilisé']);
                exit();
            }
            
            // Construction de la requête de mise à jour
            $query = "UPDATE utilisateurs SET 
                      nom_complet = :nom_complet,
                      email = :email,
                      role = :role,
                      direction = :direction";
            
            // Ajouter le mot de passe si fourni
            if (!empty($data['mot_de_passe'])) {
                $mot_de_passe_hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
                $query .= ", mot_de_passe = :mot_de_passe";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom_complet', $data['nom_complet']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':id', $data['id']);
            
            if (!empty($data['mot_de_passe'])) {
                $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
            }
            
            if ($stmt->execute()) {
                ajouterHistorique('Utilisateurs', 'Modification', "Utilisateur modifié: {$data['nom_complet']}", $conn);
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
        // Supprimer un utilisateur
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit();
            }
            
            // Empêcher la suppression de soi-même
            if ($id == $_SESSION['utilisateur_id']) {
                http_response_code(400);
                echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
                exit();
            }
            
            // Récupérer le nom pour l'historique
            $query = "SELECT nom_complet FROM utilisateurs WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'Utilisateur non trouvé']);
                exit();
            }
            
            $query = "DELETE FROM utilisateurs WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                ajouterHistorique('Utilisateurs', 'Suppression', "Utilisateur supprimé: {$user['nom_complet']}", $conn);
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