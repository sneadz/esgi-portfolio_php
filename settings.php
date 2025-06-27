<?php
// ===============================
//  Page des paramètres du compte utilisateur
// ===============================
require_once 'config/init.php';

$page_title = 'Paramètres du Compte';

// Redirection si l'utilisateur n'est pas connecté
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success_message = '';

$pdo = getDBConnection();

// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification du token CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    if (empty($errors)) {
        if ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation des champs du formulaire
            if (empty($current_password)) {
                $errors[] = 'Le mot de passe actuel est requis.';
            }

            if (empty($new_password)) {
                $errors[] = 'Le nouveau mot de passe est requis.';
            } elseif (!validatePassword($new_password)) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            }

            if (empty($confirm_password)) {
                $errors[] = 'La confirmation du mot de passe est requise.';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            // Si pas d'erreurs, changement du mot de passe
            if (empty($errors)) {
                try {
                    // Vérification du mot de passe actuel
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();

                    if (!$user || !password_verify($current_password, $user['password'])) {
                        $errors[] = 'Le mot de passe actuel est incorrect.';
                    } else {
                        // Mise à jour du mot de passe
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]);
                        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        
                        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                            $success_message = 'Mot de passe modifié avec succès !';
                        } else {
                            $errors[] = 'Erreur lors du changement de mot de passe.';
                        }
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erreur de connexion à la base de données.';
                }
            }
        }
    }
}

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Inclusion de l'en-tête HTML
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2><i class="fas fa-cog me-2"></i>Paramètres du Compte</h2>

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

        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-user me-2"></i>Informations du Compte</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Rôle :</strong> 
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </p>
                        <p><strong>Membre depuis :</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-lock me-2"></i>Changer le Mot de Passe</h4>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-key me-1"></i>Mot de passe actuel *
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir votre mot de passe actuel.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Nouveau mot de passe *
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="new_password" 
                               name="new_password" 
                               required>
                        <div class="invalid-feedback">
                            Le nouveau mot de passe doit contenir au moins 8 caractères.
                        </div>
                        <div class="form-text">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirmer le nouveau mot de passe *
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required>
                        <div class="invalid-feedback">
                            Les mots de passe ne correspondent pas.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-shield-alt me-2"></i>Sécurité</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Connexions actives</h6>
                        <p class="text-muted">Vous êtes actuellement connecté sur cette session.</p>
                        <p><small class="text-muted">Dernière activité : <?php echo date('d/m/Y H:i', $_SESSION['last_activity'] ?? time()); ?></small></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Actions de sécurité</h6>
                        <div class="d-grid gap-2">
                            <a href="logout.php" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-info-circle me-2"></i>Informations</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Statistiques du compte</h6>
                        <?php
                        // Compter les projets
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $project_count = $stmt->fetch()['count'];

                        // Compter les compétences
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_skills WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $skill_count = $stmt->fetch()['count'];
                        ?>
                        <p><strong>Projets :</strong> <?php echo $project_count; ?></p>
                        <p><strong>Compétences :</strong> <?php echo $skill_count; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Liens utiles</h6>
                        <ul class="list-unstyled">
                            <li><a href="profile.php" class="text-decoration-none">
                                <i class="fas fa-user me-1"></i>Mon profil
                            </a></li>
                            <li><a href="projects.php" class="text-decoration-none">
                                <i class="fas fa-project-diagram me-1"></i>Mes projets
                            </a></li>
                            <li><a href="skills.php" class="text-decoration-none">
                                <i class="fas fa-tools me-1"></i>Mes compétences
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 