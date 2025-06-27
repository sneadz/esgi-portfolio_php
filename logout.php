<?php
// ===============================
//  Script de déconnexion utilisateur
// ===============================
require_once 'config/init.php';

// Suppression du cookie "Se souvenir de moi" si présent
if (isset($_COOKIE['remember_token'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        // On ignore les erreurs de base de données ici
    }
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destruction de la session
session_destroy();

// Message de confirmation et redirection
$_SESSION['message'] = 'Vous avez été déconnecté avec succès.';
$_SESSION['message_type'] = 'info';

redirect('index.php');
?> 