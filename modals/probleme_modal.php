<?php
// modals/probleme_modal.php
?>
<!-- Modal Problème -->
<div class="modal fade" id="modal-probleme" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un problème</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-probleme">
                    <input type="hidden" id="probleme-id">
                    <div class="mb-3">
                        <label>Direction</label>
                        <select class="form-select" id="probleme-direction" required></select>
                    </div>
                    <div class="mb-3">
                        <label>Contrainte / Point d'attention</label>
                        <textarea class="form-control" id="probleme-contrainte" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Solution proposée</label>
                        <textarea class="form-control" id="probleme-solution" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Échéance</label>
                            <input type="date" class="form-control" id="probleme-echeance">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Responsable</label>
                            <input type="text" class="form-control" id="probleme-responsable">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Incidence financière?</label>
                            <select class="form-select" id="probleme-incidence">
                                <option value="Non">Non</option>
                                <option value="Oui">Oui</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Montant</label>
                            <input type="text" class="form-control" id="probleme-montant" placeholder="Ex: 5 000 000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Statut</label>
                            <select class="form-select" id="probleme-statut">
                                <option value="En cours">En cours</option>
                                <option value="Résolu">Résolu</option>
                                <option value="Non résolu">Non résolu</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-exporter" onclick="sauvegarderProbleme()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>