<?php
// ===============================
//  Page de demande de réinitialisation du mot de passe
// ===============================
require_once 'config/init.php';

$page_title = 'Mot de Passe Oublié';

// Redirection si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success_message = '';

// Traitement du formulaire de demande de réinitialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification du token CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    // Validation de l'email
    if (empty($email)) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!validateEmail($email)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Si pas d'erreurs, génération du lien de réinitialisation
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Génération d'un token de réinitialisation
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', time() + 3600); // Expire dans 1 heure

                // Sauvegarde du token en base
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
                if ($stmt->execute([$reset_token, $expires_at, $user['id']])) {
                    // En production, envoi du mail. Ici, affichage du lien pour la démo
                    $reset_link = APP_URL . '/reset_password.php?token=' . $reset_token;
                    $success_message = 'Un lien de réinitialisation a été généré. En production, il serait envoyé par email.<br><br>';
                    $success_message .= '<strong>Lien de réinitialisation (démo) :</strong><br>';
                    $success_message .= '<a href="' . $reset_link . '" class="btn btn-primary mt-2">Réinitialiser le mot de passe</a>';
                } else {
                    $errors[] = 'Erreur lors de la génération du lien de réinitialisation.';
                }
            } else {
                // Message générique pour la sécurité
                $success_message = 'Si cette adresse email existe dans notre base de données, un lien de réinitialisation a été généré.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion à la base de données.';
        }
    }
}

// Inclusion de l'en-tête HTML
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">
                    <i class="fas fa-key me-2"></i>Mot de Passe Oublié
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
                <?php else: ?>
                    <p class="text-muted mb-4">
                        Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                    </p>

                    <!-- Formulaire de demande de réinitialisation -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Adresse email
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   required>
                            <div class="invalid-feedback">
                                Veuillez saisir une adresse email valide.
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le lien
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

<?php // Inclusion du pied de page HTML
include 'includes/footer.php'; ?> 