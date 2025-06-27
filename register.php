<?php
require_once 'config/init.php';

$page_title = 'Inscription';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$form_data = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'bio' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'bio' => sanitizeInput($_POST['bio'] ?? '')
    ];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validation CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    // Validation des champs
    if (empty($form_data['first_name'])) {
        $errors[] = 'Le prénom est requis.';
    } elseif (strlen($form_data['first_name']) < 2) {
        $errors[] = 'Le prénom doit contenir au moins 2 caractères.';
    }

    if (empty($form_data['last_name'])) {
        $errors[] = 'Le nom est requis.';
    } elseif (strlen($form_data['last_name']) < 2) {
        $errors[] = 'Le nom doit contenir au moins 2 caractères.';
    }

    if (empty($form_data['email'])) {
        $errors[] = 'L\'adresse email est requise.';
    } elseif (!validateEmail($form_data['email'])) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }

    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    } elseif (!validatePassword($password)) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    if (empty($confirm_password)) {
        $errors[] = 'La confirmation du mot de passe est requise.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$form_data['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion à la base de données.';
        }
    }

    // Création du compte
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password, bio, role) 
                VALUES (?, ?, ?, ?, ?, 'user')
            ");
            
            if ($stmt->execute([
                $form_data['first_name'],
                $form_data['last_name'],
                $form_data['email'],
                $hashed_password,
                $form_data['bio']
            ])) {
                $_SESSION['message'] = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                $_SESSION['message_type'] = 'success';
                redirect('login.php');
            } else {
                $errors[] = 'Erreur lors de la création du compte.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du compte.';
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">
                    <i class="fas fa-user-plus me-2"></i>Créer un compte
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
                                       value="<?php echo htmlspecialchars($form_data['first_name']); ?>"
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
                                       value="<?php echo htmlspecialchars($form_data['last_name']); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre nom.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Adresse email *
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>"
                               required>
                        <div class="invalid-feedback">
                            Veuillez saisir une adresse email valide.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="fas fa-info-circle me-1"></i>Biographie
                        </label>
                        <textarea class="form-control" 
                                  id="bio" 
                                  name="bio" 
                                  rows="3" 
                                  placeholder="Parlez-nous un peu de vous..."><?php echo htmlspecialchars($form_data['bio']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Mot de passe *
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                        <div class="invalid-feedback">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </div>
                        <div class="form-text">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirmer le mot de passe *
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
                            <i class="fas fa-user-plus me-2"></i>Créer mon compte
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0">
                        Déjà un compte ? 
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>Se connecter
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 