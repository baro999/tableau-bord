# Tableau de Bord Stratégique

Application de suivi de performance avec indicateurs, projets, PTA et points focaux.

## Installation

1. Clonez ce dépôt
2. Copiez `config/database.example.php` vers `config/database.php` et configurez vos accès MySQL
3. Importez `sql/database.sql` dans votre base de données
4. Configurez votre serveur web pour pointer vers le dossier du projet

## Configuration requise

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PDO MySQL

## Identifiants par défaut

- Email: admin@example.com
- Mot de passe: admin123

## Structure

- `index.php` - Redirection vers login ou dashboard
- `login.php` - Page de connexion
- `dashboard.php` - Tableau de bord principal
- `api/` - Endpoints API RESTful
- `includes/` - Fichiers communs (header, footer, fonctions)
- `assets/` - CSS et JavaScript
- `modals/` - Modals pour les formulaires
- `sql/` - Structure de la base de données