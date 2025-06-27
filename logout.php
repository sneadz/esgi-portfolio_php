<?php
require_once 'config/init.php';

// Supprimer le cookie "Se souvenir de moi"
if (isset($_COOKIE['remember_token'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        // Ignorer les erreurs de base de données
    }
    
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
$_SESSION['message'] = 'Vous avez été déconnecté avec succès.';
$_SESSION['message_type'] = 'info';

redirect('index.php');
?> 