<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit();
}

$conn = getDB();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Stratégique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (estConnecte() && $current_page != 'login.php'): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Tableau de Bord Stratégique
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    <?php if (estAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'utilisateurs.php' ? 'active' : ''; ?>" href="utilisateurs.php">
                            <i class="fas fa-users me-1"></i>Utilisateurs
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-direction dropdown-toggle" type="button" id="directionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-building me-2"></i>
                            <span id="current-direction">Toutes les directions</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="direction-menu" style="max-height: 300px; overflow-y: auto;"></ul>
                    </div>
                    
                    <span class="me-3 d-none d-md-block">Période: <strong><?php echo date('Y'); ?></strong></span>
                    
                    <div class="dropdown me-2">
                        <button class="btn btn-exporter dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i>Exporter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exporterPDF()"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterExcel()"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterCSV()"><i class="fas fa-file-csv me-2 text-primary"></i>CSV</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterModule('indicateurs')">Indicateurs</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterModule('problemes')">Problem Solving</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterModule('projets')">Projets</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterModule('pta')">PTA</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exporterModule('points')">Points Focaux</a></li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-outline-secondary me-3" onclick="imprimer()">
                        <i class="fas fa-print"></i>
                    </button>
                    
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['nom_complet'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div class="d-none d-lg-block">
                            <strong><?php echo htmlspecialchars($_SESSION['nom_complet'] ?? 'Utilisateur'); ?></strong>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-user-tag me-1"></i><?php echo $_SESSION['role'] ?? 'utilisateur'; ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="profil.php">
                                    <i class="fas fa-user me-2"></i>Mon profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div class="container-fluid mt-4">