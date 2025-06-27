<?php
require_once '../config/init.php';

$page_title = 'Gestion des Compétences';

// Vérifier si l'utilisateur est connecté et admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$errors = [];
$success_message = '';

$pdo = getDBConnection();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    if (empty($errors)) {
        if ($action === 'add') {
            // Ajout d'une nouvelle compétence
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');

            if (empty($name)) {
                $errors[] = 'Le nom de la compétence est requis.';
            }

            if (empty($errors)) {
                try {
                    // Vérifier si la compétence existe déjà
                    $stmt = $pdo->prepare("SELECT id FROM skills WHERE name = ?");
                    $stmt->execute([$name]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Cette compétence existe déjà.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO skills (name, description, category) VALUES (?, ?, ?)");
                        if ($stmt->execute([$name, $description, $category])) {
                            $success_message = 'Compétence ajoutée avec succès !';
                        } else {
                            $errors[] = 'Erreur lors de l\'ajout de la compétence.';
                        }
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur de connexion à la base de données.';
                }
            }
        } elseif ($action === 'edit') {
            // Modification d'une compétence
            $skill_id = (int)($_POST['skill_id'] ?? 0);
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');

            if (empty($name)) {
                $errors[] = 'Le nom de la compétence est requis.';
            }

            if (empty($errors)) {
                try {
                    // Vérifier si la compétence existe déjà (sauf celle qu'on modifie)
                    $stmt = $pdo->prepare("SELECT id FROM skills WHERE name = ? AND id != ?");
                    $stmt->execute([$name, $skill_id]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Cette compétence existe déjà.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE skills SET name = ?, description = ?, category = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        if ($stmt->execute([$name, $description, $category, $skill_id])) {
                            $success_message = 'Compétence mise à jour avec succès !';
                        } else {
                            $errors[] = 'Erreur lors de la mise à jour de la compétence.';
                        }
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur de connexion à la base de données.';
                }
            }
        } elseif ($action === 'delete') {
            // Suppression d'une compétence
            $skill_id = (int)($_POST['skill_id'] ?? 0);

            try {
                // Vérifier si la compétence est utilisée
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_skills WHERE skill_id = ?");
                $stmt->execute([$skill_id]);
                $usage_count = $stmt->fetch()['count'];

                if ($usage_count > 0) {
                    $errors[] = 'Cette compétence est utilisée par ' . $usage_count . ' utilisateur(s) et ne peut pas être supprimée.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
                    if ($stmt->execute([$skill_id])) {
                        $success_message = 'Compétence supprimée avec succès !';
                    } else {
                        $errors[] = 'Erreur lors de la suppression de la compétence.';
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Erreur de connexion à la base de données.';
            }
        }
    }
}

// Récupérer toutes les compétences
$stmt = $pdo->prepare("SELECT * FROM skills ORDER BY category, name");
$stmt->execute();
$skills = $stmt->fetchAll();

// Récupérer les statistiques d'utilisation
$stmt = $pdo->prepare("
    SELECT s.id, s.name, COUNT(us.id) as usage_count 
    FROM skills s 
    LEFT JOIN user_skills us ON s.id = us.skill_id 
    GROUP BY s.id 
    ORDER BY usage_count DESC
");
$stmt->execute();
$skill_stats = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tools me-2"></i>Gestion des Compétences</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                <i class="fas fa-plus me-2"></i>Ajouter une compétence
            </button>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list me-2"></i>Liste des Compétences</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($skills)): ?>
                            <p class="text-muted">Aucune compétence définie.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Catégorie</th>
                                            <th>Description</th>
                                            <th>Utilisations</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($skills as $skill): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($skill['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($skill['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($skill['description']): ?>
                                                        <?php echo htmlspecialchars(substr($skill['description'], 0, 50)); ?>
                                                        <?php if (strlen($skill['description']) > 50): ?>...<?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucune description</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $usage = 0;
                                                    foreach ($skill_stats as $stat) {
                                                        if ($stat['id'] == $skill['id']) {
                                                            $usage = $stat['usage_count'];
                                                            break;
                                                        }
                                                    }
                                                    echo $usage;
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary btn-sm"
                                                                onclick="editSkill(<?php echo htmlspecialchars(json_encode($skill)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($usage == 0): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette compétence ?')">
                                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Compétence utilisée">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar me-2"></i>Statistiques</h4>
                    </div>
                    <div class="card-body">
                        <h6>Compétences les plus populaires :</h6>
                        <?php if (empty($skill_stats)): ?>
                            <p class="text-muted">Aucune donnée disponible.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($skill_stats, 0, 5) as $stat): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?php echo htmlspecialchars($stat['name']); ?></span>
                                    <span class="badge bg-primary"><?php echo $stat['usage_count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout de compétence -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter une compétence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Nom de la compétence *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir le nom de la compétence.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">
                            <i class="fas fa-folder me-1"></i>Catégorie
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="category" 
                               name="category" 
                               placeholder="ex: Frontend, Backend, Base de données...">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Description de la compétence..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Ajouter la compétence
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modification de compétence -->
<div class="modal fade" id="editSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Modifier la compétence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="skill_id" id="edit_skill_id">

                    <div class="mb-3">
                        <label for="edit_name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Nom de la compétence *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_name" 
                               name="name" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir le nom de la compétence.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_category" class="form-label">
                            <i class="fas fa-folder me-1"></i>Catégorie
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_category" 
                               name="category" 
                               placeholder="ex: Frontend, Backend, Base de données...">
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea class="form-control" 
                                  id="edit_description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Description de la compétence..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSkill(skill) {
    document.getElementById('edit_skill_id').value = skill.id;
    document.getElementById('edit_name').value = skill.name;
    document.getElementById('edit_category').value = skill.category;
    document.getElementById('edit_description').value = skill.description;
    
    new bootstrap.Modal(document.getElementById('editSkillModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?> 