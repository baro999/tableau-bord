<?php
// modals/pta_modal.php
?>
<!-- Modal PTA -->
<div class="modal fade" id="modal-pta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une activité PTA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-pta">
                    <input type="hidden" id="pta-id">
                    <div class="mb-3">
                        <label>Direction</label>
                        <select class="form-select" id="pta-direction" required></select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Référence</label>
                            <input type="text" class="form-control" id="pta-ref">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Responsable</label>
                            <input type="text" class="form-control" id="pta-responsable">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Activité</label>
                        <input type="text" class="form-control" id="pta-activite" required>
                    </div>
                    <div class="mb-3">
                        <label>Livrable</label>
                        <input type="text" class="form-control" id="pta-livrable">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Budget</label>
                            <input type="text" class="form-control" id="pta-montant" placeholder="1 500 000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Avancement (%)</label>
                            <input type="number" class="form-control" id="pta-avancement" min="0" max="100" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date début</label>
                            <input type="date" class="form-control" id="pta-debut">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date fin</label>
                            <input type="date" class="form-control" id="pta-fin">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Statut</label>
                        <select class="form-select" id="pta-statut">
                            <option value="Non lancé">Non lancé</option>
                            <option value="En cours">En cours</option>
                            <option value="Réalisé">Réalisé</option>
                            <option value="Retard">Retard</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-pta" onclick="sauvegarderActivitePTA()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>