<?php
// modals/point_modal.php
?>
<!-- Modal Point Focal -->
<div class="modal fade" id="modal-point" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-point-title">Ajouter un point focal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="point-direction-alert" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="point-direction-message"></span>
                </div>
                
                <form id="form-point">
                    <input type="hidden" id="point-id">
                    
                    <div class="mb-3">
                        <label>Direction <span class="text-danger">*</span></label>
                        <select class="form-select" id="point-direction" required>
                            <option value="">Sélectionner une direction</option>
                            <?php
                            // Récupérer les directions déjà utilisées
                            $conn = getDB();
                            $query = "SELECT direction FROM points_focaux";
                            $stmt = $conn->query($query);
                            $directions_utilisees = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Récupérer toutes les directions disponibles
                            $toutes_directions = getDirections($conn);
                            
                            foreach ($toutes_directions as $dir): 
                                $disabled = in_array($dir, $directions_utilisees) ? 'disabled' : '';
                                $selected = isset($_GET['direction']) && $_GET['direction'] == $dir ? 'selected' : '';
                            ?>
                            <option value="<?php echo $dir; ?>" <?php echo $disabled; ?> <?php echo $selected; ?>>
                                <?php echo $dir; ?>
                                <?php echo $disabled ? ' (déjà utilisé)' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Les directions grisées ont déjà un point focal</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Nom complet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="point-nom" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" id="point-email">
                    </div>
                    
                    <div class="mb-3">
                        <label>Téléphone</label>
                        <input type="text" class="form-control" id="point-telephone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-point" onclick="sauvegarderPointFocal()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Ajouter un écouteur pour vérifier la direction en temps réel
document.getElementById('point-direction')?.addEventListener('change', function(e) {
    const selectedOption = this.options[this.selectedIndex];
    const alertDiv = document.getElementById('point-direction-alert');
    const messageSpan = document.getElementById('point-direction-message');
    
    if (selectedOption.disabled) {
        alertDiv.style.display = 'block';
        messageSpan.textContent = 'Cette direction a déjà un point focal. Veuillez en choisir une autre.';
        document.querySelector('#modal-point .btn-point').disabled = true;
    } else {
        alertDiv.style.display = 'none';
        document.querySelector('#modal-point .btn-point').disabled = false;
    }
});
</script>