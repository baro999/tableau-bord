<?php
// profil.php
require_once 'includes/header.php';

$message = '';
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = securiser($_POST['nom_complet']);
    $email = securiser($_POST['email']);
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $_SESSION['utilisateur_id']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $erreur = "Cet email est déjà utilisé par un autre compte";
    } else {
        // Mise à jour des informations
        $query = "UPDATE utilisateurs SET nom_complet = :nom, email = :email WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nom', $nom_complet);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $_SESSION['utilisateur_id']);
        
        if ($stmt->execute()) {
            $_SESSION['nom_complet'] = $nom_complet;
            $_SESSION['email'] = $email;
            $message = "Profil mis à jour avec succès";
            
            // Changement de mot de passe si demandé
            if (!empty($_POST['nouveau_mot_de_passe'])) {
                $ancien = $_POST['ancien_mot_de_passe'];
                $nouveau = $_POST['nouveau_mot_de_passe'];
                $confirmation = $_POST['confirmation_mot_de_passe'];
                
                // Vérifier l'ancien mot de passe
                $query = "SELECT mot_de_passe FROM utilisateurs WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $_SESSION['utilisateur_id']);
                $stmt->execute();
                $user = $stmt->fetch();
                
                if (password_verify($ancien, $user['mot_de_passe'])) {
                    if ($nouveau === $confirmation) {
                        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
                        $query = "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id";
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':mdp', $hash);
                        $stmt->bindParam(':id', $_SESSION['utilisateur_id']);
                        $stmt->execute();
                        $message = "Profil et mot de passe mis à jour";
                    } else {
                        $erreur = "Les nouveaux mots de passe ne correspondent pas";
                    }
                } else {
                    $erreur = "L'ancien mot de passe est incorrect";
                }
            }
            
            ajouterHistorique('Profil', 'Modification', "Modification du profil", $conn);
        } else {
            $erreur = "Erreur lors de la mise à jour";
        }
    }
}

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $_SESSION['utilisateur_id']);
$stmt->execute();
$user = $stmt->fetch();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-circle me-2"></i>Mon Profil
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?php echo $erreur; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <h5 class="mb-3">Informations personnelles</h5>
                    
                    <div class="mb-3">
                        <label>Nom complet</label>
                        <input type="text" name="nom_complet" class="form-control" 
                               value="<?php echo htmlspecialchars($user['nom_complet']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Rôle</label>
                        <input type="text" class="form-control" value="<?php echo $user['role']; ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label>Direction</label>
                        <input type="text" class="form-control" value="<?php echo $user['direction'] ?: 'Non assigné'; ?>" disabled>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Changer le mot de passe</h5>
                    <p class="text-muted small">Laissez vide pour ne pas changer</p>
                    
                    <div class="mb-3">
                        <label>Ancien mot de passe</label>
                        <input type="password" name="ancien_mot_de_passe" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="nouveau_mot_de_passe" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirmation_mot_de_passe" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn btn-exporter">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>