<?php
// config/database.example.php
// Copiez ce fichier vers database.php et remplissez vos informations

class Database {
    private $host = "localhost";
    private $db_name = "votre_base";
    private $username = "votre_utilisateur";
    private $password = "votre_mot_de_passe";
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
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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