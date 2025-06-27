<?php
// ===============================
//  Page de profil utilisateur
// ===============================
require_once 'config/init.php';

$page_title = 'Mon Profil';

// Redirection si l'utilisateur n'est pas connecté
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success_message = '';

// Récupération des informations de l'utilisateur
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    redirect('logout.php');
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification du token CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    // Validation des champs du formulaire
    if (empty($first_name)) {
        $errors[] = 'Le prénom est requis.';
    } elseif (strlen($first_name) < 2) {
        $errors[] = 'Le prénom doit contenir au moins 2 caractères.';
    }

    if (empty($last_name)) {
        $errors[] = 'Le nom est requis.';
    } elseif (strlen($last_name) < 2) {
        $errors[] = 'Le nom doit contenir au moins 2 caractères.';
    }

    // Mise à jour du profil utilisateur
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, bio = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$first_name, $last_name, $bio, $_SESSION['user_id']])) {
                $success_message = 'Profil mis à jour avec succès !';
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                
                // Mise à jour des données affichées
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['bio'] = $bio;
            } else {
                $errors[] = 'Erreur lors de la mise à jour du profil.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion à la base de données.';
        }
    }
}

// Récupération des statistiques de l'utilisateur
$stmt = $pdo->prepare("SELECT COUNT(*) as project_count FROM projects WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$project_count = $stmt->fetch()['project_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as skill_count FROM user_skills WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$skill_count = $stmt->fetch()['skill_count'];

// Inclusion de l'en-tête HTML
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-user me-2"></i>Informations</h4>
            </div>
            <div class="card-body text-center">
                <?php if ($user['avatar']): ?>
                    <img src="<?php echo UPLOAD_PATH . $user['avatar']; ?>" 
                         class="profile-avatar mb-3" 
                         alt="Avatar">
                <?php else: ?>
                    <div class="profile-avatar mb-3 bg-secondary d-flex align-items-center justify-content-center text-white">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                <?php endif; ?>
                
                <h5><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <?php if ($user['bio']): ?>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php endif; ?>
                
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <h6 class="text-primary"><?php echo $project_count; ?></h6>
                        <small class="text-muted">Projets</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-success"><?php echo $skill_count; ?></h6>
                        <small class="text-muted">Compétences</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-edit me-2"></i>Modifier mon profil</h4>
            </div>
            <div class="card-body">
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

                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Prénom *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre prénom.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nom *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre nom.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Adresse email
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               disabled>
                        <div class="form-text">
                            L'adresse email ne peut pas être modifiée.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="fas fa-info-circle me-1"></i>Biographie
                        </label>
                        <textarea class="form-control" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4" 
                                  placeholder="Parlez-nous un peu de vous..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4><i class="fas fa-cog me-2"></i>Actions</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <a href="projects.php" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-project-diagram me-2"></i>Gérer mes projets
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="skills.php" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-tools me-2"></i>Gérer mes compétences
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <a href="settings.php" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-cog me-2"></i>Paramètres du compte
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="logout.php" class="btn btn-outline-danger w-100 mb-2">
                            <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 