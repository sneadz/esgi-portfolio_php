<?php
require_once 'config/init.php';

$page_title = 'Connexion';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validation CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    // Validation des champs
    if (empty($email)) {
        $errors[] = 'L\'adresse email est requise.';
    } elseif (!validateEmail($email)) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }

    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    }

    // Tentative de connexion
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Gestion du "Se souvenir de moi"
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                    
                    setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
                }

                $_SESSION['message'] = 'Connexion réussie ! Bienvenue ' . $user['first_name'] . ' !';
                $_SESSION['message_type'] = 'success';
                redirect('index.php');
            } else {
                $errors[] = 'Adresse email ou mot de passe incorrect.';
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
                    <i class="fas fa-sign-in-alt me-2"></i>Connexion
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
                               value="<?php echo htmlspecialchars($email); ?>"
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir une adresse email valide.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Mot de passe
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir votre mot de passe.
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="remember_me" 
                               name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Se souvenir de moi
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-2">
                        <a href="forgot_password.php" class="text-decoration-none">
                            <i class="fas fa-key me-1"></i>Mot de passe oublié ?
                        </a>
                    </p>
                    <p class="mb-0">
                        Pas encore de compte ? 
                        <a href="register.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>S'inscrire
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Comptes de test -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Comptes de test
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <strong>Admin :</strong><br>
                        <small class="text-muted">admin@example.com</small><br>
                        <small class="text-muted">password</small>
                    </div>
                    <div class="col-6">
                        <strong>Utilisateur :</strong><br>
                        <small class="text-muted">user3@example.com</small><br>
                        <small class="text-muted">password</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 