-- Création de l'utilisateur et de la base de données pour le projet Portfolio
-- Création de l'utilisateur projetb2
CREATE USER IF NOT EXISTS 'projetb2'@'localhost' IDENTIFIED BY 'password';

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS projet_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Attribution des droits à l'utilisateur
GRANT ALL PRIVILEGES ON projet_web.* TO 'projetb2'@'localhost';
FLUSH PRIVILEGES;

-- Utilisation de la base de données
USE projet_web;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    bio TEXT,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    remember_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expires_at TIMESTAMP NULL
);

-- Table des compétences
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table de liaison utilisateurs-compétences
CREATE TABLE user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    level ENUM('débutant', 'intermédiaire', 'avancé', 'expert') DEFAULT 'débutant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id)
);

-- Table des projets
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion des données de test

-- Compte administrateur
INSERT INTO users (email, password, first_name, last_name, role) VALUES 
('admin@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Comptes utilisateurs (3 utilisateurs comme demandé)
INSERT INTO users (email, password, first_name, last_name, role) VALUES 
('user1@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Dupont', 'user'),
('user2@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Martin', 'user'),
('user3@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre', 'Durand', 'user');

-- Compétences de base
INSERT INTO skills (name, description, category) VALUES 
('PHP', 'Langage de programmation côté serveur', 'Backend'),
('MySQL', 'Système de gestion de base de données', 'Base de données'),
('HTML', 'Langage de balisage pour le web', 'Frontend'),
('CSS', 'Langage de style pour le web', 'Frontend'),
('JavaScript', 'Langage de programmation côté client', 'Frontend'),
('React', 'Framework JavaScript pour interfaces utilisateur', 'Frontend'),
('Node.js', 'Runtime JavaScript côté serveur', 'Backend'),
('Python', 'Langage de programmation polyvalent', 'Backend'),
('Git', 'Système de contrôle de version', 'Outils'),
('Docker', 'Plateforme de conteneurisation', 'DevOps'),
('Laravel', 'Framework PHP moderne', 'Backend'),
('Vue.js', 'Framework JavaScript progressif', 'Frontend');

-- Attribution de compétences aux utilisateurs
INSERT INTO user_skills (user_id, skill_id, level) VALUES 
-- User 1
(2, 1, 'avancé'),
(2, 2, 'intermédiaire'),
(2, 3, 'expert'),
(2, 4, 'avancé'),
(2, 5, 'intermédiaire'),
-- User 2
(3, 6, 'expert'),
(3, 7, 'avancé'),
(3, 8, 'intermédiaire'),
(3, 9, 'avancé'),
-- User 3
(4, 10, 'débutant'),
(4, 11, 'intermédiaire'),
(4, 12, 'avancé');

-- Projets de test (3 projets par utilisateur comme demandé)
INSERT INTO projects (user_id, title, description, link) VALUES 
-- Projets User 1
(2, 'Portfolio Personnel', 'Un portfolio web développé en PHP et MySQL avec une interface moderne et responsive', 'https://github.com/user1/portfolio'),
(2, 'Application E-commerce', 'Une application de commerce électronique complète avec gestion des produits et panier', 'https://github.com/user1/ecommerce'),
(2, 'Système de Gestion', 'Application de gestion d\'entreprise avec modules RH et comptabilité', 'https://github.com/user1/management'),

-- Projets User 2
(3, 'Application React Native', 'Application mobile cross-platform pour la gestion de tâches', 'https://github.com/user2/taskapp'),
(3, 'API REST Node.js', 'API RESTful pour un service de réservation en ligne', 'https://github.com/user2/booking-api'),
(3, 'Dashboard Analytics', 'Tableau de bord interactif pour l\'analyse de données', 'https://github.com/user2/analytics'),

-- Projets User 3
(4, 'Site Vitrine Vue.js', 'Site vitrine moderne développé avec Vue.js et animations CSS', 'https://github.com/user3/showcase'),
(4, 'Application Laravel', 'Application web complète avec authentification et gestion de contenu', 'https://github.com/user3/laravel-app'),
(4, 'Plugin WordPress', 'Plugin WordPress pour la gestion d\'événements', 'https://github.com/user3/wordpress-plugin'); 