<?php
// ===============================
//  Page de gestion des projets utilisateur
// ===============================
require_once 'config/init.php';

$page_title = 'Mes Projets';

// Redirection si l'utilisateur n'est pas connecté
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success_message = '';

// Création du dossier d'upload s'il n'existe pas
createUploadDirectory();

// Traitement de l'ajout, modification ou suppression de projet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification du token CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    if (empty($errors)) {
        if ($action === 'add') {
            // Ajout d'un nouveau projet
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $link = sanitizeInput($_POST['link'] ?? '');
            
            // Validation des champs du formulaire
            if (empty($title)) {
                $errors[] = 'Le titre du projet est requis.';
            }

            if (empty($errors)) {
                try {
                    $pdo = getDBConnection();
                    
                    // Gestion de l'upload d'image
                    $image_path = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['image'];
                        
                        if (!validateFileType($file['name'])) {
                            $errors[] = 'Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.';
                        } elseif ($file['size'] > MAX_FILE_SIZE) {
                            $errors[] = 'Le fichier est trop volumineux. Taille maximum : 5MB.';
                        } else {
                            $filename = generateUniqueFilename($file['name']);
                            $upload_path = UPLOAD_PATH . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                $image_path = $filename;
                            } else {
                                $errors[] = "Erreur lors de l'upload de l'image.";
                            }
                        }
                    }

                    if (empty($errors)) {
                        $stmt = $pdo->prepare("
                            INSERT INTO projects (user_id, title, description, image, link) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $image_path, $link])) {
                            $success_message = 'Projet ajouté avec succès !';
                        } else {
                            $errors[] = "Erreur lors de l'ajout du projet.";
                        }
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur de connexion à la base de données.';
                }
            }
        } elseif ($action === 'edit') {
            // Modification d'un projet existant
            $project_id = (int)($_POST['project_id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $link = sanitizeInput($_POST['link'] ?? '');
            
            // Validation des champs du formulaire
            if (empty($title)) {
                $errors[] = 'Le titre du projet est requis.';
            }

            if (empty($errors)) {
                try {
                    $pdo = getDBConnection();
                    
                    // Vérification que le projet appartient à l'utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
                    $stmt->execute([$project_id, $_SESSION['user_id']]);
                    $project = $stmt->fetch();
                    
                    if (!$project) {
                        $errors[] = 'Projet non trouvé.';
                    } else {
                        // Gestion de l'upload d'image
                        $image_path = $project['image'];
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['image'];
                            
                            if (!validateFileType($file['name'])) {
                                $errors[] = 'Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.';
                            } elseif ($file['size'] > MAX_FILE_SIZE) {
                                $errors[] = 'Le fichier est trop volumineux. Taille maximum : 5MB.';
                            } else {
                                $filename = generateUniqueFilename($file['name']);
                                $upload_path = UPLOAD_PATH . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    // Suppression de l'ancienne image
                                    if ($project['image'] && file_exists(UPLOAD_PATH . $project['image'])) {
                                        unlink(UPLOAD_PATH . $project['image']);
                                    }
                                    $image_path = $filename;
                                } else {
                                    $errors[] = "Erreur lors de l'upload de l'image.";
                                }
                            }
                        }

                        if (empty($errors)) {
                            $stmt = $pdo->prepare("
                                UPDATE projects 
                                SET title = ?, description = ?, image = ?, link = ?, updated_at = CURRENT_TIMESTAMP 
                                WHERE id = ? AND user_id = ?
                            ");
                            
                            if ($stmt->execute([$title, $description, $image_path, $link, $project_id, $_SESSION['user_id']])) {
                                $success_message = 'Projet mis à jour avec succès !';
                            } else {
                                $errors[] = 'Erreur lors de la mise à jour du projet.';
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur de connexion à la base de données.';
                }
            }
        } elseif ($action === 'delete') {
            // Suppression d'un projet
            $project_id = (int)($_POST['project_id'] ?? 0);
            
            try {
                $pdo = getDBConnection();
                
                // Récupération des informations du projet
                $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
                $stmt->execute([$project_id, $_SESSION['user_id']]);
                $project = $stmt->fetch();
                
                if ($project) {
                    // Suppression de l'image si elle existe
                    if ($project['image'] && file_exists(UPLOAD_PATH . $project['image'])) {
                        unlink(UPLOAD_PATH . $project['image']);
                    }
                    
                    // Suppression du projet
                    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
                    if ($stmt->execute([$project_id, $_SESSION['user_id']])) {
                        $success_message = 'Projet supprimé avec succès !';
                    } else {
                        $errors[] = 'Erreur lors de la suppression du projet.';
                    }
                } else {
                    $errors[] = 'Projet non trouvé.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Erreur de connexion à la base de données.';
            }
        }
    }
}

// Récupération des projets de l'utilisateur
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Inclusion de l'en-tête HTML
include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-project-diagram me-2"></i>Mes Projets</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                <i class="fas fa-plus me-2"></i>Ajouter un projet
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

        <?php if (empty($projects)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun projet</h4>
                    <p class="text-muted">Vous n'avez pas encore ajouté de projets à votre portfolio.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                        <i class="fas fa-plus me-2"></i>Ajouter votre premier projet
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card project-card">
                            <?php if ($project['image']): ?>
                                <img src="<?php echo UPLOAD_PATH . $project['image']; ?>" 
                                     class="card-img-top project-image" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>
                                    <?php if (strlen($project['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                                    </small>
                                    
                                    <div class="btn-group" role="group">
                                        <?php if ($project['link']): ?>
                                            <a href="<?php echo htmlspecialchars($project['link']); ?>" 
                                               class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm"
                                                onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajout de projet -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter un projet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading me-1"></i>Titre du projet *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir le titre du projet.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Décrivez votre projet..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">
                            <i class="fas fa-image me-1"></i>Image du projet
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="image" 
                               name="image" 
                               accept="image/*">
                        <div class="form-text">
                            Formats acceptés : JPG, PNG, GIF. Taille maximum : 5MB.
                        </div>
                        <img id="image_preview" class="mt-2" style="max-width: 200px; display: none;">
                    </div>

                    <div class="mb-3">
                        <label for="link" class="form-label">
                            <i class="fas fa-link me-1"></i>Lien vers le projet
                        </label>
                        <input type="url" 
                               class="form-control" 
                               id="link" 
                               name="link" 
                               placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Ajouter le projet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modification de projet -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Modifier le projet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="project_id" id="edit_project_id">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">
                            <i class="fas fa-heading me-1"></i>Titre du projet *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_title" 
                               name="title" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir le titre du projet.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea class="form-control" 
                                  id="edit_description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Décrivez votre projet..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_image" class="form-label">
                            <i class="fas fa-image me-1"></i>Image du projet
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="edit_image" 
                               name="image" 
                               accept="image/*">
                        <div class="form-text">
                            Formats acceptés : JPG, PNG, GIF. Taille maximum : 5MB.
                        </div>
                        <img id="edit_image_preview" class="mt-2" style="max-width: 200px;">
                    </div>

                    <div class="mb-3">
                        <label for="edit_link" class="form-label">
                            <i class="fas fa-link me-1"></i>Lien vers le projet
                        </label>
                        <input type="url" 
                               class="form-control" 
                               id="edit_link" 
                               name="link" 
                               placeholder="https://...">
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
function editProject(project) {
    document.getElementById('edit_project_id').value = project.id;
    document.getElementById('edit_title').value = project.title;
    document.getElementById('edit_description').value = project.description;
    document.getElementById('edit_link').value = project.link;
    
    const preview = document.getElementById('edit_image_preview');
    if (project.image) {
        preview.src = '<?php echo UPLOAD_PATH; ?>' + project.image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?> 