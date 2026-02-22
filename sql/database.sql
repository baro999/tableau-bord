-- Création de la base de données
CREATE DATABASE IF NOT EXISTS tableau_bord_strategique;
USE tableau_bord_strategique;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'utilisateur', 'lecteur') DEFAULT 'utilisateur',
    direction VARCHAR(50),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
);

-- Table des indicateurs
CREATE TABLE indicateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direction VARCHAR(50) NOT NULL,
    axe VARCHAR(100) NOT NULL,
    objectif TEXT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    actions TEXT,
    valeur DECIMAL(10,2) NOT NULL,
    cible DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) DEFAULT '%',
    methode TEXT,
    responsable VARCHAR(100),
    periodicite VARCHAR(50),
    source VARCHAR(255),
    pourcentage DECIMAL(5,2) GENERATED ALWAYS AS ((valeur / cible) * 100) STORED,
    statut VARCHAR(20) GENERATED ALWAYS AS (
        CASE 
            WHEN (valeur / cible) * 100 >= 100 THEN 'Vert'
            WHEN (valeur / cible) * 100 >= 80 THEN 'Orange'
            ELSE 'Rouge'
        END
    ) STORED,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES utilisateurs(id)
);

-- Table des problèmes
CREATE TABLE problemes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direction VARCHAR(50) NOT NULL,
    contrainte TEXT NOT NULL,
    solution TEXT,
    echeance DATE,
    responsable VARCHAR(100),
    incidence ENUM('Oui', 'Non') DEFAULT 'Non',
    montant VARCHAR(50),
    statut ENUM('En cours', 'Résolu', 'Non résolu') DEFAULT 'En cours',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES utilisateurs(id)
);

-- Table des projets
CREATE TABLE projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    direction VARCHAR(50) NOT NULL,
    phase VARCHAR(100),
    duree VARCHAR(50),
    zone VARCHAR(100),
    description TEXT,
    objectif TEXT,
    maitrise_ouvrage VARCHAR(255),
    date_debut DATE,
    date_fin DATE,
    budget VARCHAR(50),
    avancement INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES utilisateurs(id)
);

-- Table des marchés (liés aux projets)
CREATE TABLE marches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    marche VARCHAR(255),
    titulaire VARCHAR(255),
    montant VARCHAR(50),
    bailleur VARCHAR(100),
    type VARCHAR(50),
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- Table de l'équipe projet
CREATE TABLE equipe_projet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    poste VARCHAR(100),
    nom VARCHAR(100),
    prenom VARCHAR(100),
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- Table des activités PTA
CREATE TABLE pta_activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direction VARCHAR(50) NOT NULL,
    reference VARCHAR(20),
    activite TEXT NOT NULL,
    livrable TEXT,
    montant VARCHAR(50),
    responsable VARCHAR(100),
    date_debut DATE,
    date_fin DATE,
    avancement INT DEFAULT 0,
    statut VARCHAR(20) GENERATED ALWAYS AS (
        CASE 
            WHEN avancement >= 100 THEN 'Réalisé'
            WHEN avancement > 0 THEN 'En cours'
            ELSE 'Non lancé'
        END
    ) STORED,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES utilisateurs(id)
);

-- Table des points focaux
CREATE TABLE points_focaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    direction VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telephone VARCHAR(20),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES utilisateurs(id)
);

-- Table de l'historique
CREATE TABLE historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    utilisateur_id INT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Insertion d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, role) VALUES 
('Administrateur', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insertion des points focaux existants
INSERT INTO points_focaux (direction, nom) VALUES
('DGE', 'Mame Awa Florence DIOUF'),
('DCC', 'Oumar NIANG'),
('DCG', 'Boubacar Cyprien BATHE'),
('DQSE', 'Sokhna SENE'),
('DAZE', 'Bintou DIAGNE'),
('DMCC', 'Oumou BALDE'),
('DPIP', 'Mamadou DIONE'),
('DIPE', 'Henriette DIA'),
('SGL', 'Karamba TAMBA'),
('DPM', 'Fatou NDIAYE'),
('DSID', 'Aby Kamara'),
('DFC', 'Adja Ramatoulaye DIOP'),
('DGTx', 'Marie Louise DIAM'),
('PIS', 'Adja Aicha GUEYE'),
('PDTSL', 'Zoe KONE'),
('Audit Interne', 'El Hadji Malick DIOP'),
('DSES', 'Mohamet Lamine NDIAYE'),
('DCH', 'Aminata DIAKHATE'),
('Juridique', 'Pape Diallo DIAW'),
('Archive', 'Moussa DIALLO'),
('DGU PLI', 'Jane DIOUF');