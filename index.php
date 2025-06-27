<?php
require_once 'config/init.php';

$page_title = 'Accueil';

// Récupérer les derniers projets publics
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name 
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$recent_projects = $stmt->fetchAll();

// Récupérer les compétences les plus populaires
$stmt = $pdo->prepare("
    SELECT s.name, s.description, s.category, COUNT(us.id) as user_count
    FROM skills s
    LEFT JOIN user_skills us ON s.id = us.skill_id
    GROUP BY s.id
    ORDER BY user_count DESC
    LIMIT 8
");
$stmt->execute();
$popular_skills = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Section Hero moderne -->
<div class="hero-section mb-5">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="hero-icon mb-4">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h1 class="hero-title mb-3">
                        Bienvenue sur <?php echo APP_NAME; ?>
                    </h1>
                    <p class="hero-subtitle mb-4">
                        Créez et gérez votre portfolio professionnel en toute simplicité. 
                        Partagez vos compétences, projets et expériences avec le monde.
                    </p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-user-plus me-2"></i>Créer un compte
                            </a>
                            <a href="login.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="hero-buttons">
                            <a href="profile.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-user me-2"></i>Mon Profil
                            </a>
                            <a href="projects.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-project-diagram me-2"></i>Mes Projets
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-project-diagram me-2"></i>Derniers Projets</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recent_projects)): ?>
                    <p class="text-muted">Aucun projet publié pour le moment.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($recent_projects as $project): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card project-card">
                                    <?php if ($project['image']): ?>
                                        <img src="<?php echo UPLOAD_PATH . $project['image']; ?>" 
                                             class="card-img-top project-image" 
                                             alt="<?php echo htmlspecialchars($project['title']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                                            </small>
                                        </p>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <?php if ($project['link']): ?>
                                            <a href="<?php echo htmlspecialchars($project['link']); ?>" 
                                               class="btn btn-primary btn-sm" target="_blank">
                                                <i class="fas fa-external-link-alt me-1"></i>Voir le projet
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-tools me-2"></i>Compétences Populaires</h3>
            </div>
            <div class="card-body">
                <?php if (empty($popular_skills)): ?>
                    <p class="text-muted">Aucune compétence disponible.</p>
                <?php else: ?>
                    <?php foreach ($popular_skills as $skill): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary skill-badge">
                                <?php echo htmlspecialchars($skill['name']); ?>
                            </span>
                            <small class="text-muted">
                                <?php echo $skill['user_count']; ?> utilisateur<?php echo $skill['user_count'] > 1 ? 's' : ''; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3><i class="fas fa-info-circle me-2"></i>À propos</h3>
            </div>
            <div class="card-body">
                <p>
                    <?php echo APP_NAME; ?> est une plateforme moderne pour créer et gérer 
                    votre portfolio professionnel. Partagez vos compétences, projets et 
                    expériences avec la communauté.
                </p>
                <div class="row text-center">
                    <div class="col-4">
                        <i class="fas fa-users icon-large"></i>
                        <p class="mt-2"><small>Communauté</small></p>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-shield-alt icon-large"></i>
                        <p class="mt-2"><small>Sécurisé</small></p>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-mobile-alt icon-large"></i>
                        <p class="mt-2"><small>Responsive</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 