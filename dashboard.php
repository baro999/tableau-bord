<?php
// dashboard.php
require_once 'includes/header.php';

// Récupérer les données pour les KPIs
$stats = [];

// Nombre total d'indicateurs
$query = "SELECT COUNT(*) as total FROM indicateurs";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['indicateurs'] = $stmt->fetch()['total'];

// Nombre total de problèmes
$query = "SELECT COUNT(*) as total FROM problemes";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['problemes'] = $stmt->fetch()['total'];

// Nombre total de projets
$query = "SELECT COUNT(*) as total FROM projets";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['projets'] = $stmt->fetch()['total'];

// Nombre total d'activités PTA
$query = "SELECT COUNT(*) as total FROM pta_activites";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['activites'] = $stmt->fetch()['total'];

// Récupérer les directions pour le filtre
$directions = getDirections($conn);

// Récupérer l'historique récent
$query = "SELECT h.*, u.nom_complet 
          FROM historique h 
          LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id 
          ORDER BY h.date_action DESC 
          LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->execute();
$historique_recent = $stmt->fetchAll();
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <!-- Vue d'ensemble -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-building me-2"></i>Vue d'ensemble
            </div>
            <div class="card-body">
                <p>
                    <span class="direction-indicator" id="direction-indicator">
                        <i class="fas fa-filter me-2"></i>
                        <span id="direction-indicator-text">Toutes les directions</span>
                    </span>
                </p>
                <div class="small">
                    <strong>Utilisateur :</strong><br>
                    <span class="badge bg-info"><?php echo htmlspecialchars($_SESSION['nom_complet']); ?></span>
                    <span class="badge bg-secondary"><?php echo $_SESSION['role']; ?></span>
                </div>
                <hr>
                <div class="small">
                    <strong>Modules :</strong><br>
                    <span class="badge bg-primary me-1">Indicateurs</span>
                    <span class="badge bg-warning text-dark me-1">Problem Solving</span>
                    <span class="badge bg-success me-1">PTA</span>
                    <span class="badge bg-danger me-1">Points Focaux</span>
                </div>
            </div>
        </div>

        <!-- KPI -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tachometer-alt me-2"></i>KPIs
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="kpi-value" id="kpi-indicateurs"><?php echo $stats['indicateurs']; ?></div>
                        <div class="kpi-label">Indicateurs</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="kpi-value" id="kpi-problemes"><?php echo $stats['problemes']; ?></div>
                        <div class="kpi-label">Problèmes</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="kpi-value" id="kpi-projets"><?php echo $stats['projets']; ?></div>
                        <div class="kpi-label">Projets</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="kpi-value" id="kpi-activites"><?php echo $stats['activites']; ?></div>
                        <div class="kpi-label">Activités PTA</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Légende -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Légende
            </div>
            <div class="card-body">
                <h6>Indicateurs</h6>
                <div><span class="statut-vert me-2">Vert</span> ≥ 100%</div>
                <div><span class="statut-orange me-2">Orange</span> ≥ 80%</div>
                <div><span class="statut-rouge me-2">Rouge</span> < 80%</div>
                <hr>
                <h6>Problèmes</h6>
                <div><span class="statut-resolu me-2">Résolu</span></div>
                <div><span class="statut-encours me-2">En cours</span></div>
                <div><span class="statut-nonresolu me-2">Non résolu</span></div>
            </div>
        </div>

        <!-- Historique récent -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Historique récent
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;" id="historique-sidebar">
                <?php foreach ($historique_recent as $h): ?>
                <div class="historique-item small">
                    <div><strong><?php echo date('d/m/Y H:i', strtotime($h['date_action'])); ?></strong></div>
                    <div>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($h['module']); ?></span>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($h['action']); ?></span>
                    </div>
                    <div><?php echo htmlspecialchars($h['details']); ?></div>
                    <div><small class="text-muted">par <?php echo htmlspecialchars($h['nom_complet'] ?? 'Système'); ?></small></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="col-lg-9">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#indicateurs"><i class="fas fa-chart-line me-2"></i>Indicateurs</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#problemes"><i class="fas fa-puzzle-piece me-2 text-warning"></i>Problem Solving</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#projets"><i class="fas fa-clipboard-list me-2 text-primary"></i>Suivi Projet</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pta"><i class="fas fa-tasks me-2 text-success"></i>PTA</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#points"><i class="fas fa-users me-2 text-danger"></i>Points Focaux</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historique"><i class="fas fa-history me-2"></i>Historique</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide"><i class="fas fa-book me-2"></i>Guide</button></li>
        </ul>

        <div class="tab-content">
            <!-- Indicateurs -->
            <div class="tab-pane fade show active" id="indicateurs">
                <div class="d-flex justify-content-between mb-3">
                    <h5><i class="fas fa-chart-line me-2"></i>Indicateurs Stratégiques</h5>
                    <button class="btn btn-sm btn-exporter" onclick="ajouterIndicateur()"><i class="fas fa-plus"></i> Nouvel indicateur</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-indicateurs">
                        <thead>
                            <tr>
                                <th>Direction</th>
                                <th>Axe Stratégique</th>
                                <th>Objectif</th>
                                <th>Indicateur</th>
                                <th>Actions/Activités</th>
                                <th>Valeur</th>
                                <th>Cible</th>
                                <th>%</th>
                                <th>Statut</th>
                                <th>Méthode de calcul</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="indicators-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Problem Solving -->
            <div class="tab-pane fade" id="problemes">
                <div class="d-flex justify-content-between mb-3">
                    <h5><i class="fas fa-puzzle-piece me-2"></i>Problem Solving</h5>
                    <button class="btn btn-sm btn-exporter" onclick="ajouterProbleme()"><i class="fas fa-plus"></i> Nouveau problème</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-problemes">
                        <thead><tr><th>N°</th><th>Dir.</th><th>Contrainte</th><th>Solution</th><th>Échéance</th><th>Resp.</th><th>Inc.</th><th>Montant</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody id="problemes-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Suivi Projet -->
            <div class="tab-pane fade" id="projets">
                <div class="d-flex justify-content-between mb-4">
                    <h5><i class="fas fa-clipboard-list me-2"></i>Suivi des Projets</h5>
                    <button class="btn btn-projet" onclick="ajouterProjet()">
                        <i class="fas fa-plus me-2"></i>Nouveau projet
                    </button>
                </div>
                <div id="projets-container" class="projects-list"></div>
            </div>

            <!-- PTA -->
            <div class="tab-pane fade" id="pta">
                <div class="d-flex justify-content-between mb-3">
                    <h5><i class="fas fa-tasks me-2"></i>PTA</h5>
                    <button class="btn btn-sm btn-pta" onclick="ajouterActivitePTA()"><i class="fas fa-plus"></i> Nouvelle activité</button>
                </div>
                <select class="form-select mb-3 w-25" id="pta-direction-filter" onchange="filtrerPTA()"></select>
                <div class="table-responsive">
                    <table class="table table-hover table-pta">
                        <thead><tr><th>Dir.</th><th>Réf.</th><th>Activité</th><th>Livrable</th><th>Budget</th><th>Resp.</th><th>Début</th><th>Fin</th><th>Avancement</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody id="pta-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Points Focaux -->
             <!-- Points Focaux - Modifier l'en-tête -->
<!-- Points Focaux - Modifier l'en-tête -->

            <div class="tab-pane fade" id="points">
            
                <div class="table-responsive">
                    <table class="table table-hover table-points">
                        <thead><tr><th>N°</th><th>Direction</th><th>Point Focal</th><th>Contact</th><th>Actions</th></tr></thead>
                        <tbody id="points-body"></tbody>
                        <div class="d-flex justify-content-between mb-3">
    <h5><i class="fas fa-users me-2"></i>Points Focaux</h5>
    <div>
        <a href="directions_disponibles.php" class="btn btn-sm btn-info me-2">
            <i class="fas fa-list me-2"></i>Voir directions disponibles
        </a>
        <button class="btn btn-sm btn-point" onclick="ajouterPointFocal()">
            <i class="fas fa-plus"></i> Nouveau point focal
        </button>
    </div>
</div>
                    </table>
                </div>
                
            </div>
            

            <!-- Historique complet -->
            <div class="tab-pane fade" id="historique">
                <h5><i class="fas fa-history me-2"></i>Historique des modifications</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <select class="form-select" id="historique-filtre" onchange="chargerHistorique()">
                            <option value="all">Tous les modules</option>
                            <option value="indicateurs">Indicateurs</option>
                            <option value="problemes">Problem Solving</option>
                            <option value="projets">Projets</option>
                            <option value="pta">PTA</option>
                            <option value="points">Points Focaux</option>
                        </select>
                    </div>
                </div>
                <div id="historique-complet"></div>
            </div>

            <!-- Guide complet -->
            <div class="tab-pane fade" id="guide">
                <h5><i class="fas fa-book me-2"></i>Guide d'utilisation</h5>
                
                <div class="guide-section">
                    <h6><i class="fas fa-rocket me-2 text-primary"></i>Démarrage rapide</h6>
                    <ol>
                        <li><strong>Sélectionnez une direction</strong> en haut à gauche pour filtrer les données</li>
                        <li><strong>Naviguez entre les onglets</strong> pour accéder aux différents modules</li>
                        <li><strong>Ajoutez des données</strong> avec les boutons "+" dans chaque onglet</li>
                        <li><strong>Utilisez les icônes</strong> ✏️ pour modifier et 🗑️ pour supprimer</li>
                    </ol>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-keyboard me-2 text-warning"></i>Raccourcis clavier</h6>
                    <div class="row">
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + N</span> Nouvel indicateur</div>
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + P</span> Nouveau problème</div>
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + J</span> Nouveau projet</div>
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + A</span> Nouvelle activité PTA</div>
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + F</span> Rechercher</div>
                        <div class="col-md-4"><span class="shortcut-key">Ctrl + E</span> Exporter</div>
                    </div>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-calculator me-2 text-success"></i>Méthodes de calcul</h6>
                    <table class="table table-sm">
                        <tr><th>Module</th><th>Règle</th></tr>
                        <tr><td>Indicateurs</td><td>Vert ≥ 100%, Orange ≥ 80%, Rouge < 80%</td></tr>
                        <tr><td>Problèmes</td><td>Statut manuel (Résolu/En cours/Non résolu)</td></tr>
                        <tr><td>PTA</td><td>Statut basé sur avancement : 100% = Réalisé, >0% = En cours, 0% = Non lancé</td></tr>
                        <tr><td>Projets</td><td>Avancement manuel</td></tr>
                    </table>
                </div>

                <div class="guide-section">
                    <h6><i class="fas fa-download me-2 text-info"></i>Exports</h6>
                    <ul>
                        <li><strong>PDF</strong> : Rapport complet avec graphiques</li>
                        <li><strong>Excel</strong> : Tous les tableaux avec formules</li>
                        <li><strong>CSV</strong> : Données brutes pour analyse</li>
                        <li><strong>Export par module</strong> : Sélectionnez un module spécifique</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'modals/indicateur_modal.php'; ?>
<?php include 'modals/probleme_modal.php'; ?>
<?php include 'modals/projet_modal.php'; ?>
<?php include 'modals/pta_modal.php'; ?>
<?php include 'modals/point_modal.php'; ?>

<script>
    // Passer les données PHP au JavaScript
    window.directions = <?php echo json_encode($directions); ?>;
    window.utilisateur = <?php echo json_encode([
        'id' => $_SESSION['utilisateur_id'],
        'nom' => $_SESSION['nom_complet'],
        'role' => $_SESSION['role']
    ]); ?>;
</script>

<?php require_once 'includes/footer.php'; ?>