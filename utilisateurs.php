<?php
// utilisateurs.php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est admin
if (!estAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$erreur = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'ajouter':
                $nom = securiser($_POST['nom_complet']);
                $email = securiser($_POST['email']);
                $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
                $role = securiser($_POST['role']);
                $direction = !empty($_POST['direction']) ? securiser($_POST['direction']) : null;
                
                // Vérifier si l'email existe déjà
                $query = "SELECT id FROM utilisateurs WHERE email = :email";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $erreur = "Cet email est déjà utilisé";
                } else {
                    $query = "INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, role, direction) 
                              VALUES (:nom, :email, :mdp, :role, :direction)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nom', $nom);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':mdp', $mot_de_passe);
                    $stmt->bindParam(':role', $role);
                    $stmt->bindParam(':direction', $direction);
                    
                    if ($stmt->execute()) {
                        ajouterHistorique('Utilisateurs', 'Ajout', "Nouvel utilisateur: $nom", $conn);
                        $message = "Utilisateur ajouté avec succès";
                    } else {
                        $erreur = "Erreur lors de l'ajout";
                    }
                }
                break;
                
            case 'modifier':
                $id = $_POST['id'];
                $nom = securiser($_POST['nom_complet']);
                $email = securiser($_POST['email']);
                $role = securiser($_POST['role']);
                $direction = !empty($_POST['direction']) ? securiser($_POST['direction']) : null;
                
                // Vérifier si l'email existe déjà pour un autre utilisateur
                $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $erreur = "Cet email est déjà utilisé";
                } else {
                    $query = "UPDATE utilisateurs SET nom_complet = :nom, email = :email, 
                              role = :role, direction = :direction WHERE id = :id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nom', $nom);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':role', $role);
                    $stmt->bindParam(':direction', $direction);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        // Si changement de mot de passe
                        if (!empty($_POST['nouveau_mot_de_passe'])) {
                            $hash = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
                            $query = "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':mdp', $hash);
                            $stmt->bindParam(':id', $id);
                            $stmt->execute();
                        }
                        
                        ajouterHistorique('Utilisateurs', 'Modification', "Utilisateur modifié: $nom", $conn);
                        $message = "Utilisateur modifié avec succès";
                    } else {
                        $erreur = "Erreur lors de la modification";
                    }
                }
                break;
        }
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['utilisateur_id']) { // Empêcher l'auto-suppression
        $query = "DELETE FROM utilisateurs WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            ajouterHistorique('Utilisateurs', 'Suppression', "Utilisateur supprimé ID: $id", $conn);
            $message = "Utilisateur supprimé";
        }
    }
    header("Location: utilisateurs.php");
    exit();
}

// Récupérer la liste des utilisateurs
$query = "SELECT * FROM utilisateurs ORDER BY nom_complet";
$stmt = $conn->prepare($query);
$stmt->execute();
$utilisateurs = $stmt->fetchAll();

// Récupérer les directions pour le select
$directions = getDirections($conn);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Gestion des utilisateurs</h5>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUtilisateur" onclick="resetForm()">
                        <i class="fas fa-plus me-2"></i>Nouvel utilisateur
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $erreur; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Direction</th>
                                    <th>Date création</th>
                                    <th>Dernière connexion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $u['role'] === 'admin' ? 'danger' : 
                                                ($u['role'] === 'utilisateur' ? 'success' : 'secondary'); 
                                        ?>">
                                            <?php echo $u['role']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $u['direction'] ?: '-'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($u['date_creation'])); ?></td>
                                    <td><?php echo $u['derniere_connexion'] ? date('d/m/Y H:i', strtotime($u['derniere_connexion'])) : '-'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editerUtilisateur(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($u['id'] != $_SESSION['utilisateur_id']): ?>
                                        <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout/Modification Utilisateur -->
<div class="modal fade" id="modalUtilisateur" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUtilisateurTitle">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formUtilisateur">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="ajouter">
                    <input type="hidden" name="id" id="userId">
                    
                    <div class="mb-3">
                        <label>Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="nom_complet" id="nomComplet" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3" id="mdpFields">
                        <label>Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="mot_de_passe" id="motDePasse" class="form-control">
                        <small class="text-muted">Laissez vide pour ne pas changer en modification</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Rôle</label>
                        <select name="role" id="role" class="form-select">
                            <option value="utilisateur">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                            <option value="lecteur">Lecteur</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Direction</label>
                        <select name="direction" id="direction" class="form-select">
                            <option value="">-- Aucune --</option>
                            <?php foreach ($directions as $dir): ?>
                            <option value="<?php echo $dir; ?>"><?php echo $dir; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalUtilisateurTitle').textContent = 'Ajouter un utilisateur';
    document.getElementById('formAction').value = 'ajouter';
    document.getElementById('userId').value = '';
    document.getElementById('nomComplet').value = '';
    document.getElementById('email').value = '';
    document.getElementById('motDePasse').value = '';
    document.getElementById('motDePasse').required = true;
    document.getElementById('role').value = 'utilisateur';
    document.getElementById('direction').value = '';
}

function editerUtilisateur(user) {
    document.getElementById('modalUtilisateurTitle').textContent = 'Modifier un utilisateur';
    document.getElementById('formAction').value = 'modifier';
    document.getElementById('userId').value = user.id;
    document.getElementById('nomComplet').value = user.nom_complet;
    document.getElementById('email').value = user.email;
    document.getElementById('motDePasse').value = '';
    document.getElementById('motDePasse').required = false;
    document.getElementById('role').value = user.role;
    document.getElementById('direction').value = user.direction || '';
    
    new bootstrap.Modal(document.getElementById('modalUtilisateur')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>