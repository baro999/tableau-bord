<?php
// modals/projet_modal.php
?>
<!-- Modal Projet -->
<div class="modal fade" id="modal-projet" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-projet">
                    <input type="hidden" id="projet-id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Titre du projet</label>
                            <input type="text" class="form-control" id="projet-titre" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Direction</label>
                            <select class="form-select" id="projet-direction" required></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Phase</label>
                            <input type="text" class="form-control" id="projet-phase" value="Exécution">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Durée prévue</label>
                            <input type="text" class="form-control" id="projet-duree" placeholder="24 mois">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Zone</label>
                            <input type="text" class="form-control" id="projet-zone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" id="projet-description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Objectif global</label>
                            <input type="text" class="form-control" id="projet-objectif">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Maîtrise d'ouvrage</label>
                            <input type="text" class="form-control" id="projet-mo">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date démarrage</label>
                            <input type="date" class="form-control" id="projet-date-debut">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date clôture</label>
                            <input type="date" class="form-control" id="projet-date-fin">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Budget total (FCFA)</label>
                            <input type="text" class="form-control" id="projet-budget">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Avancement global (%)</label>
                            <input type="number" class="form-control" id="projet-avancement" min="0" max="100" value="0">
                        </div>
                    </div>
                    
                    <h6 class="mt-3">Marchés</h6>
                    <div id="marches-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="ajouterMarche()">
                        <i class="fas fa-plus"></i> Ajouter un marché
                    </button>
                    
                    <h6 class="mt-3">Équipe projet</h6>
                    <div id="equipe-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="ajouterMembre()">
                        <i class="fas fa-plus"></i> Ajouter un membre
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-projet" onclick="sauvegarderProjet()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>