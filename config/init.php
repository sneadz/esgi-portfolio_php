<?php
// Configuration des sessions (avant session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Définir le chemin racine du projet
define('PROJECT_ROOT', dirname(__DIR__));

// Inclure la configuration de la base de données
require_once PROJECT_ROOT . '/config/database.php';

// Fonction de connexion à la base de données
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Fonction pour vérifier un token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Fonction pour nettoyer les données d'entrée
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour afficher les messages d'erreur
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Fonction pour afficher les messages de succès
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Fonction pour valider l'email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour valider le mot de passe
function validatePassword($password) {
    return strlen($password) >= 8;
}

// Fonction pour créer le dossier d'upload s'il n'existe pas
function createUploadDirectory() {
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
}

// Fonction pour valider le type de fichier
function validateFileType($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

// Fonction pour générer un nom de fichier unique
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}
