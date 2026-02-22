<?php
// modals/indicateur_modal.php
?>
<!-- Modal Indicateur -->
<div class="modal fade" id="modal-indicateur" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-indicateur-title">Ajouter un indicateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-indicateur">
                    <input type="hidden" id="indicateur-id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Direction <span class="text-danger">*</span></label>
                            <select class="form-select" id="indicateur-direction" required>
                                <option value="">Sélectionner une direction</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Axe stratégique <span class="text-danger">*</span></label>
                            <select class="form-select" id="indicateur-axe" required>
                                <option value="">Sélectionner un axe</option>
                                <option value="Performance">Performance</option>
                                <option value="Satisfaction client">Satisfaction client</option>
                                <option value="Maîtrise des coûts">Maîtrise des coûts</option>
                                <option value="Développement">Développement</option>
                                <option value="Innovation">Innovation</option>
                                <option value="Qualité">Qualité</option>
                                <option value="Sécurité">Sécurité</option>
                                <option value="Ressources humaines">Ressources humaines</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Objectif stratégique <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="indicateur-objectif" rows="2" required placeholder="Ex: Améliorer la satisfaction client..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nom de l'indicateur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="indicateur-nom" required placeholder="Ex: Taux de satisfaction client">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Actions / Activités prioritaires</label>
                        <textarea class="form-control" id="indicateur-actions" rows="2" placeholder="Ex: Enquête trimestrielle, analyse des réclamations..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Valeur actuelle <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="indicateur-valeur" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Cible <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="indicateur-cible" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Unité</label>
                            <select class="form-select" id="indicateur-unite">
                                <option value="%">%</option>
                                <option value="Nombre">Nombre</option>
                                <option value="FCFA">FCFA</option>
                                <option value="Jours">Jours</option>
                                <option value="Points">Points</option>
                                <option value="Taux">Taux</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Méthode de calcul</label>
                            <textarea class="form-control" id="indicateur-methode" rows="2" placeholder="Ex: (Nombre de clients satisfaits / Total clients) * 100"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Responsable</label>
                            <input type="text" class="form-control" id="indicateur-responsable" placeholder="Nom du responsable">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Périodicité</label>
                            <select class="form-select" id="indicateur-periodicite">
                                <option value="Mensuelle">Mensuelle</option>
                                <option value="Bimestrielle">Bimestrielle</option>
                                <option value="Trimestrielle">Trimestrielle</option>
                                <option value="Semestrielle">Semestrielle</option>
                                <option value="Annuelle">Annuelle</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Source des données</label>
                            <input type="text" class="form-control" id="indicateur-source" placeholder="Ex: Rapport d'enquête, système d'information...">
                        </div>
                    </div>
                    
                    <small class="text-muted">Les champs marqués d'un <span class="text-danger">*</span> sont obligatoires</small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-exporter" onclick="sauvegarderIndicateur()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>