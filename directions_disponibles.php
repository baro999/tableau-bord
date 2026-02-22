<?php
// directions_disponibles.php
require_once 'includes/header.php';

// Récupérer toutes les directions
$toutes_directions = getDirections($conn);

// Récupérer les directions déjà utilisées
$query = "SELECT direction FROM points_focaux";
$stmt = $conn->query($query);
$directions_utilisees = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculer les directions disponibles
$directions_disponibles = array_diff($toutes_directions, $directions_utilisees);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>Directions disponibles
                </div>
                <div class="card-body">
                    <?php if (count($directions_disponibles) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($directions_disponibles as $dir): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $dir; ?>
                                    <button class="btn btn-sm btn-success" onclick="ajouterPourDirection('<?php echo $dir; ?>')">
                                        <i class="fas fa-plus"></i> Ajouter
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Toutes les directions ont déjà un point focal.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle me-2"></i>Directions déjà attribuées
                </div>
                <div class="card-body">
                    <?php if (count($directions_utilisees) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($directions_utilisees as $dir): ?>
                                <div class="list-group-item list-group-item-warning">
                                    <?php echo $dir; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucune direction attribuée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ajouterPourDirection(direction) {
    // Fermer le modal s'il est ouvert
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-point'));
    if (modal) {
        modal.hide();
    }
    
    // Ouvrir le modal avec la direction présélectionnée
    document.getElementById('form-point').reset();
    document.getElementById('point-id').value = '';
    document.getElementById('point-direction').value = direction;
    document.getElementById('point-direction-alert').style.display = 'none';
    document.querySelector('#modal-point .btn-point').disabled = false;
    
    new bootstrap.Modal(document.getElementById('modal-point')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>