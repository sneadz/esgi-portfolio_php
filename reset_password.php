<?php
require_once 'config/init.php';

$page_title = 'Réinitialisation du Mot de Passe';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success_message = '';
$token = $_GET['token'] ?? '';

// Vérifier si le token est valide
if (empty($token)) {
    redirect('login.php');
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'Le lien de réinitialisation est invalide ou a expiré.';
    }
} catch (PDOException $e) {
    $errors[] = 'Erreur de connexion à la base de données.';
}

// Traitement du formulaire de réinitialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validation CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    // Validation des mots de passe
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

    if (empty($errors)) {
        try {
            // Mettre à jour le mot de passe et supprimer le token
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user['id']])) {
                $success_message = 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.';
            } else {
                $errors[] = 'Erreur lors de la réinitialisation du mot de passe.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion à la base de données.';
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">
                    <i class="fas fa-lock me-2"></i>Réinitialisation du Mot de Passe
                </h3>
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
                    <div class="d-grid">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </a>
                    </div>
                <?php elseif (empty($errors)): ?>
                    <p class="text-muted mb-4">
                        Bonjour <?php echo htmlspecialchars($user['first_name']); ?>, 
                        vous pouvez maintenant définir votre nouveau mot de passe.
                    </p>

                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
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
                                <i class="fas fa-save me-2"></i>Réinitialiser le mot de passe
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 