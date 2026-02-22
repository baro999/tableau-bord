<?php
// config/database.php
class Database {
    private $host = "mysql5.alwaysdata.com"; // L'hôte de votre base
    private $db_name = "performance_tableau_bord"; // Votre nom de base
    private $username = "mansourbaro@gamil.com"; // Votre utilisateur
    private $password = "Mouhamadou@1"; // Votre mot de passe
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log("Erreur de connexion: " . $e->getMessage());
            die("Erreur de connexion à la base de données");
        }
        return $this->conn;
    }
}

function getDB() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}
?>