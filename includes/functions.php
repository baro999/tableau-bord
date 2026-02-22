<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

// Rediriger si non connecté
function exigerConnexion() {
    if (!estConnecte()) {
        header("Location: login.php");
        exit();
    }
}

// Vérifier le rôle
function aRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Vérifier si l'utilisateur est admin
function estAdmin() {
    return aRole('admin');
}

// Ajouter à l'historique
function ajouterHistorique($module, $action, $details, $conn = null) {
    if ($conn === null) {
        $conn = getDB();
    }
    
    $utilisateur_id = $_SESSION['utilisateur_id'] ?? null;
    
    $query = "INSERT INTO historique (module, action, details, utilisateur_id) 
              VALUES (:module, :action, :details, :utilisateur_id)";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':module', $module);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':details', $details);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur historique: " . $e->getMessage());
        return false;
    }
}

// Sécuriser les entrées
function securiser($data) {
    if (is_array($data)) {
        return array_map('securiser', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Obtenir la liste des directions
function getDirections($conn = null) {
    if ($conn === null) {
        $conn = getDB();
    }
    
    try {
        $query = "SELECT DISTINCT direction FROM points_focaux ORDER BY direction";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Erreur getDirections: " . $e->getMessage());
        return [];
    }
}

// Formater le montant
function formaterMontant($montant) {
    if (empty($montant) || $montant === '-') {
        return '-';
    }
    return number_format(floatval(str_replace(' ', '', $montant)), 0, ',', ' ') . ' FCFA';
}

// Obtenir le nom du mois en français
function moisEnFrancais($mois) {
    $mois_fr = [
        1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    return $mois_fr[$mois] ?? $mois;
}

// Générer un token CSRF
function genererTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifier le token CSRF
function verifierTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>