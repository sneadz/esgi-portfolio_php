<?php
// ===============================
//  Page de gestion des compétences utilisateur
// ===============================
require_once 'config/init.php';

$page_title = 'Mes Compétences';

// Vérification de la connexion utilisateur
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success_message = '';

$pdo = getDBConnection();

// Traitement de l'ajout ou modification de compétences
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    // Vérification du token CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    }

    $selected_skills = $_POST['skills'] ?? [];
    $levels = $_POST['levels'] ?? [];

    // Vérification qu'au moins une compétence est sélectionnée
    if (empty($selected_skills)) {
        $errors[] = 'Veuillez sélectionner au moins une compétence.';
    }

    // Si pas d'erreurs, mise à jour des compétences en base
    if (empty($errors)) {
        try {
            // Suppression des anciennes compétences de l'utilisateur
            $stmt = $pdo->prepare('DELETE FROM user_skills WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);

            // Ajout des nouvelles compétences sélectionnées
            $stmt = $pdo->prepare('INSERT INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)');
            foreach ($selected_skills as $skill_id) {
                $level = $levels[$skill_id] ?? 'débutant';
                $stmt->execute([$_SESSION['user_id'], $skill_id, $level]);
            }
            $success_message = 'Compétences mises à jour avec succès !';
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la mise à jour des compétences.';
        }
    }
}

// Récupération de toutes les compétences disponibles
$stmt = $pdo->prepare('SELECT * FROM skills ORDER BY category, name');
$stmt->execute();
$all_skills = $stmt->fetchAll();

// Récupération des compétences de l'utilisateur
$stmt = $pdo->prepare('SELECT skill_id, level FROM user_skills WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user_skills = $stmt->fetchAll();
$user_skills_map = [];
foreach ($user_skills as $us) {
    $user_skills_map[$us['skill_id']] = $us['level'];
}

// Récupération des détails des compétences sélectionnées
$selected_skills_details = [];
if (!empty($user_skills_map)) {
    $skill_ids = array_keys($user_skills_map);
    $placeholders = str_repeat('?,', count($skill_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE id IN ($placeholders) ORDER BY category, name");
    $stmt->execute($skill_ids);
    $selected_skills_details = $stmt->fetchAll();
}

// Définition des niveaux de compétence
$levels = [
    'débutant' => 'Débutant',
    'intermédiaire' => 'Intermédiaire',
    'avancé' => 'Avancé',
    'expert' => 'Expert'
];

// Inclusion de l'en-tête HTML
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- Section des compétences actuelles -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-star me-2"></i>Mes Compétences Actuelles</h4>
            </div>
            <div class="card-body">
                <?php if (empty($selected_skills_details)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune compétence sélectionnée</h5>
                        <p class="text-muted">Vous n'avez pas encore sélectionné de compétences. Utilisez le formulaire ci-dessous pour ajouter vos compétences.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($selected_skills_details as $skill): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <?php echo htmlspecialchars($skill['name']); ?>
                                            <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($skill['category']); ?></span>
                                        </h6>
                                        <p class="card-text">
                                            <strong>Niveau :</strong> 
                                            <span class="badge bg-<?php 
                                                echo match($user_skills_map[$skill['id']]) {
                                                    'débutant' => 'success',
                                                    'intermédiaire' => 'info',
                                                    'avancé' => 'warning',
                                                    'expert' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo $levels[$user_skills_map[$skill['id']]]; ?>
                                            </span>
                                        </p>
                                        <?php if ($skill['description']): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($skill['description']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section de gestion des compétences -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-edit me-2"></i>Gérer mes Compétences</h4>
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

                <!-- Formulaire de gestion des compétences -->
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="skill_filter" placeholder="Filtrer les compétences...">
                    </div>
                    <div class="row">
                        <?php foreach ($all_skills as $skill): ?>
                            <div class="col-md-6 mb-3 skill-card">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                id="skill_<?php echo $skill['id']; ?>" 
                                                name="skills[]" value="<?php echo $skill['id']; ?>"
                                                <?php if (isset($user_skills_map[$skill['id']])) echo 'checked'; ?>>
                                            <label class="form-check-label skill-name" for="skill_<?php echo $skill['id']; ?>">
                                                <?php echo htmlspecialchars($skill['name']); ?>
                                                <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($skill['category']); ?></span>
                                            </label>
                                        </div>
                                        <div class="mt-2">
                                            <label for="level_<?php echo $skill['id']; ?>" class="form-label">Niveau :</label>
                                            <select class="form-select skill-level" name="levels[<?php echo $skill['id']; ?>]" id="level_<?php echo $skill['id']; ?>">
                                                <?php foreach ($levels as $key => $label): ?>
                                                    <option value="<?php echo $key; ?>" <?php if ((isset($user_skills_map[$skill['id']]) && $user_skills_map[$skill['id']] === $key)) echo 'selected'; ?>><?php echo $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php if ($skill['description']): ?>
                                            <div class="mt-2 text-muted"><small><?php echo htmlspecialchars($skill['description']); ?></small></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer mes compétences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php // Inclusion du pied de page HTML
include 'includes/footer.php'; ?> 