# Portfolio Manager - Gestion des Utilisateurs et des Compétences

## Présentation du Projet

Ce projet est une application web développée en PHP & MySQL permettant aux utilisateurs de gérer leur portfolio en ligne. Chaque utilisateur peut renseigner ses compétences, ajouter des projets, et mettre à jour ses informations personnelles. Un administrateur peut gérer les compétences proposées.

## Fonctionnalités Implémentées

### Authentification et Gestion des Comptes
- ✅ Inscription avec validation des champs
- ✅ Connexion sécurisée avec sessions et option "Se souvenir de moi"
- ✅ Gestion des rôles (Admin / Utilisateur)
- ✅ Mise à jour des informations utilisateur
- ✅ Réinitialisation de mot de passe avec envoi d'un lien sécurisé (bonus)
- ✅ Déconnexion et destruction sécurisée de la session

### Gestion des Compétences
- ✅ L'administrateur peut ajouter, modifier et supprimer des compétences
- ✅ Un utilisateur peut sélectionner ses compétences parmi celles proposées
- ✅ Niveau de compétence défini sur une échelle (débutant → expert)

### Gestion des Projets
- ✅ Ajout, modification et suppression de projets
- ✅ Chaque projet contient : titre, description, image, lien externe
- ✅ Upload sécurisé des images avec restrictions de format et taille
- ✅ Affichage structuré des projets sur le portfolio

### Sécurité
- ✅ Protection contre XSS et injections SQL
- ✅ Utilisation de password_hash() pour le stockage sécurisé des mots de passe
- ✅ Gestion des erreurs utilisateur avec affichage des messages et conservation des champs remplis
- ✅ Expiration automatique de la session après inactivité

### Gestion des Rôles
- ✅ Deux types d'utilisateurs :
  - Administrateur : peut gérer les compétences disponibles
  - Utilisateur : peut renseigner des compétences parmi celles proposées
- ✅ Sécurisation des accès pour empêcher un utilisateur d'accéder à l'interface administrateur

## Installation et Configuration

### Prérequis
- Serveur local (XAMPP, WAMP, etc.)
- PHP 8.x et MySQL
- Un navigateur moderne

### Étapes d'Installation

1. **Cloner le projet sur votre serveur local :**
   ```bash
   git clone url_de_votre_repo
   cd portfolio
   ```

2. **Importer la base de données :**
   - Ouvrez phpMyAdmin ou votre client MySQL
   - Exécutez le fichier `database/schema.sql`
   - Ce script créera automatiquement :
     - L'utilisateur `projetb2` avec le mot de passe `password`
     - La base de données `projet_web`
     - Toutes les tables nécessaires
     - Les données de test

3. **Configurer la connexion à la base de données :**
   Le fichier `config/database.php` contient déjà les bonnes informations :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'projetb2');
   define('DB_USER', 'projetb2');
   define('DB_PASS', 'password');
   define('DB_PORT', 3306);
   ```

4. **Créer le dossier d'upload :**
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **Démarrer le serveur PHP et tester l'application :**
   ```bash
   php -S localhost:8000
   ```
   Puis accéder à l'application via http://localhost:8000

## Comptes de Test

### Compte Administrateur
- **Email :** admin@example.com
- **Mot de passe :** password

### Comptes Utilisateurs (3 utilisateurs comme demandé)
- **Email :** user1@example.com
- **Mot de passe :** password

- **Email :** user2@example.com
- **Mot de passe :** password

- **Email :** user3@example.com
- **Mot de passe :** password

## Structure du Projet

```
portfolio/
├── config/
│   ├── database.php      # Configuration de la base de données
│   ├── database.sql      # Paramètres de connexion (spécifications)
│   └── init.php          # Initialisation et fonctions utilitaires
├── database/
│   └── schema.sql        # Script de création de la base de données
├── includes/
│   ├── header.php        # En-tête commun
│   └── footer.php        # Pied de page commun
├── assets/
│   ├── css/
│   │   └── style.css     # Styles personnalisés
│   └── js/
│       └── script.js     # Scripts JavaScript
├── admin/
│   └── skills.php        # Gestion des compétences (admin)
├── uploads/              # Dossier d'upload des images
├── index.php             # Page d'accueil
├── login.php             # Page de connexion
├── register.php          # Page d'inscription
├── logout.php            # Déconnexion
├── profile.php           # Profil utilisateur
├── projects.php          # Gestion des projets
├── skills.php            # Gestion des compétences (utilisateur)
├── settings.php          # Paramètres du compte
├── forgot_password.php   # Mot de passe oublié
├── reset_password.php    # Réinitialisation du mot de passe
├── .htaccess             # Configuration Apache
└── README.md             # Documentation
```

## Données de Test

La base de données est pré-remplie avec :
- **1 compte administrateur** et **3 comptes utilisateurs**
- **12 compétences** prédéfinies dans différentes catégories
- **9 projets exemples** (3 projets par utilisateur)
- **Attributions de compétences** avec différents niveaux

## Fonctionnalités Détaillées

### Pour les Utilisateurs
1. **Inscription/Connexion :** Création de compte avec validation des données
2. **Profil :** Modification des informations personnelles
3. **Projets :** Ajout, modification et suppression de projets avec images
4. **Compétences :** Sélection des compétences et définition des niveaux
5. **Paramètres :** Changement de mot de passe et gestion de la sécurité
6. **Réinitialisation :** Mot de passe oublié avec lien sécurisé

### Pour les Administrateurs
1. **Gestion des Compétences :** Ajout, modification et suppression des compétences disponibles
2. **Statistiques :** Visualisation de l'utilisation des compétences
3. **Sécurité :** Protection contre la suppression de compétences utilisées

## Sécurité

### Mesures Implémentées
- **Protection CSRF :** Tokens uniques pour chaque formulaire
- **Validation des Données :** Nettoyage et validation de toutes les entrées
- **Hachage des Mots de Passe :** Utilisation de `password_hash()` avec coût configurable
- **Protection XSS :** Échappement des données affichées
- **Injection SQL :** Utilisation de requêtes préparées
- **Upload Sécurisé :** Validation des types et tailles de fichiers
- **Sessions Sécurisées :** Configuration des cookies de session
- **Réinitialisation Sécurisée :** Tokens temporaires avec expiration

### Configuration de Sécurité
```php
// Dans config/init.php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
```

## Technologies Utilisées

- **Backend :** PHP 8
- **Base de Données :** MySQL
- **Frontend :** HTML5, CSS3, JavaScript
- **Framework CSS :** Bootstrap 5.3
- **Icônes :** Font Awesome 6.4
- **Sécurité :** Sessions PHP, hachage bcrypt
- **Développement :** Cursor, ChatGPT

## Développement

### Structure de la Base de Données

#### Table `users`
- `id` : Identifiant unique
- `email` : Adresse email (unique)
- `password` : Mot de passe haché
- `first_name` : Prénom
- `last_name` : Nom
- `role` : Rôle (user/admin)
- `bio` : Biographie
- `avatar` : Chemin vers l'avatar
- `created_at` : Date de création
- `updated_at` : Date de mise à jour
- `remember_token` : Token "Se souvenir de moi"
- `reset_token` : Token de réinitialisation
- `reset_token_expires_at` : Expiration du token

#### Table `skills`
- `id` : Identifiant unique
- `name` : Nom de la compétence (unique)
- `description` : Description
- `category` : Catégorie
- `created_at` : Date de création
- `updated_at` : Date de mise à jour

#### Table `user_skills`
- `id` : Identifiant unique
- `user_id` : Référence vers users
- `skill_id` : Référence vers skills
- `level` : Niveau (débutant/intermédiaire/avancé/expert)
- `created_at` : Date de création
- `updated_at` : Date de mise à jour

#### Table `projects`
- `id` : Identifiant unique
- `user_id` : Référence vers users
- `title` : Titre du projet
- `description` : Description
- `image` : Chemin vers l'image
- `link` : Lien externe
- `created_at` : Date de création
- `updated_at` : Date de mise à jour