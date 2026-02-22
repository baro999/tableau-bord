<?php
// api/points.php - Version avec débogage avancé
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capturer toutes les erreurs PHP
function handleError($errno, $errstr, $errfile, $errline) {
    $response = [
        'success' => false,
        'error' => "Erreur PHP: $errstr dans $errfile ligne $errline"
    ];
    echo json_encode($response);
    exit();
}
set_error_handler('handleError');

// Capturer les exceptions fatales
function handleShutdown() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $response = [
            'success' => false,
            'error' => "Erreur fatale: " . $error['message']
        ];
        echo json_encode($response);
        exit();
    }
}
register_shutdown_function('handleShutdown');

// Vérifier la connexion
if (!estConnecte()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

$conn = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Log pour déboguer
error_log("Méthode: " . $method);

switch ($method) {
    case 'GET':
        try {
            $query = "SELECT * FROM points_focaux ORDER BY direction";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {
            error_log("Erreur GET: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        try {
            // Récupérer les données brutes
            $input = file_get_contents('php://input');
            error_log("Données POST reçues: " . $input);
            
            if (empty($input)) {
                echo json_encode(['success' => false, 'error' => 'Aucune donnée reçue']);
                exit();
            }
            
            $data = json_decode($input, true);
            
            if ($data === null) {
                error_log("Erreur JSON: " . json_last_error_msg());
                echo json_encode([
                    'success' => false, 
                    'error' => 'JSON invalide: ' . json_last_error_msg()
                ]);
                exit();
            }
            
            // Validation
            if (empty($data['direction']) || empty($data['nom'])) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Direction et nom requis'
                ]);
                exit();
            }
            
            // Vérifier si la direction existe déjà
            $query = "SELECT id FROM points_focaux WHERE direction = :direction";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Cette direction a déjà un point focal'
                ]);
                exit();
            }
            
            // Préparer les valeurs
            $email = isset($data['email']) && !empty($data['email']) ? $data['email'] : null;
            $telephone = isset($data['telephone']) && !empty($data['telephone']) ? $data['telephone'] : null;
            
            error_log("Insertion: direction={$data['direction']}, nom={$data['nom']}, email=$email, tel=$telephone");
            
            // Insertion
            $query = "INSERT INTO points_focaux 
                      (direction, nom, email, telephone, created_by) 
                      VALUES 
                      (:direction, :nom, :email, :telephone, :created_by)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':created_by', $_SESSION['utilisateur_id']);
            
            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                error_log("Insertion réussie, ID: " . $id);
                
                ajouterHistorique('Points Focaux', 'Ajout', 
                    "Nouveau point focal: {$data['nom']} ({$data['direction']})", $conn);
                
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                $error = $stmt->errorInfo();
                error_log("Erreur SQL: " . print_r($error, true));
                echo json_encode([
                    'success' => false, 
                    'error' => 'Erreur SQL: ' . $error[2]
                ]);
            }
        } catch (PDOException $e) {
            error_log("Exception PDO: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur base de données: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("Exception générale: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'PUT':
        try {
            $input = file_get_contents('php://input');
            error_log("Données PUT reçues: " . $input);
            
            if (empty($input)) {
                echo json_encode(['success' => false, 'error' => 'Aucune donnée reçue']);
                exit();
            }
            
            $data = json_decode($input, true);
            
            if ($data === null) {
                echo json_encode(['success' => false, 'error' => 'JSON invalide']);
                exit();
            }
            
            if (empty($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit();
            }
            
            // Vérifier si la direction existe déjà pour un autre ID
            $query = "SELECT id FROM points_focaux WHERE direction = :direction AND id != :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':id', $data['id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'error' => 'Cette direction a déjà un point focal']);
                exit();
            }
            
            $email = isset($data['email']) && !empty($data['email']) ? $data['email'] : null;
            $telephone = isset($data['telephone']) && !empty($data['telephone']) ? $data['telephone'] : null;
            
            $query = "UPDATE points_focaux SET 
                      direction = :direction,
                      nom = :nom,
                      email = :email,
                      telephone = :telephone
                      WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':direction', $data['direction']);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                ajouterHistorique('Points Focaux', 'Modification', 
                    "Point focal modifié: {$data['nom']} ({$data['direction']})", $conn);
                echo json_encode(['success' => true]);
            } else {
                $error = $stmt->errorInfo();
                echo json_encode(['success' => false, 'error' => 'Erreur SQL: ' . $error[2]]);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit();
            }
            
            // Récupérer les infos pour l'historique
            $query = "SELECT nom, direction FROM points_focaux WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $point = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$point) {
                echo json_encode(['success' => false, 'error' => 'Point focal non trouvé']);
                exit();
            }
            
            $query = "DELETE FROM points_focaux WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                ajouterHistorique('Points Focaux', 'Suppression', 
                    "Point focal supprimé: {$point['nom']} ({$point['direction']})", $conn);
                echo json_encode(['success' => true]);
            } else {
                $error = $stmt->errorInfo();
                echo json_encode(['success' => false, 'error' => 'Erreur SQL: ' . $error[2]]);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        break;
}
?>