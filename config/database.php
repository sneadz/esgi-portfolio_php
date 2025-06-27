<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_web');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// Configuration de l'application
define('APP_NAME', 'Portfolio Manager');
define('APP_URL', 'http://localhost:8001');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configuration des sessions
define('SESSION_LIFETIME', 3600); // 1 heure
define('REMEMBER_ME_LIFETIME', 30 * 24 * 3600); // 30 jours

// Configuration de sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_COST', 12); 