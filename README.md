# 🚗 Touche pas au klaxon

**Touche pas au klaxon** est une application web réalisée dans le cadre d’un projet scolaire.  
Développée en **PHP** avec une architecture **MVC**, elle propose un système complet de gestion de trajets, d’agences et d’utilisateurs pour un service de covoiturage simplifié.

## 📌 Fonctionnalités principales
- Gestion des trajets (CRUD : création, modification, suppression, affichage)
- Gestion des agences et des utilisateurs
- Authentification avec gestion de session et rôles (utilisateur / admin)
- Messages flash après les opérations d’écriture (ajout, modification, suppression)
- Respect de l’architecture **MVC** (Models, Views, Controllers)
- Utilisation d’un **routeur PHP** ([izniburak/router](https://packagist.org/packages/izniburak/router))

---

## ⚙️ Installation et lancement

### Prérequis
- PHP >= 8.1  
- MySQL >= 8.0  
- Composer installé  
- (Optionnel) Node.js + npm, pour la compilation du CSS (via bootstrap et SASS)

### Étapes d’installation

#### 1. Cloner le dépôt
```bash
git clone https://github.com/luddu73/CEF-Devoir9
cd CEF-Devoir9
```

#### 2. Installer les dépendances PHP
```bash
composer install
```

#### 3. Configurer la base de données
- Importer les fichiers `sql` fournis pour construire la base de donnée
- Créer un fichier `.env` à la racine du projet
```env
DB_HOST= "localhost"
DB_DATABASE= "touchepasauklaxon "
DB_USER= "admin"
DB_PASS= "P@ssw0rd*"
DB_CHARSET=utf8mb4
# Variables de connexion à la base de donnée
```

#### 4. (Optionnel) Compiler le CSS avec npm
Le projet utilise Bootstrap et SASS pour le front-end.
```bash
npm install
npm run dev
```

#### 5. Lancer le serveur PHP
```bash
php -S localhost:8000 -t public
```
L'application sera accessible sur [http://localhost:8000](http://localhost:8000)


## 📁 Dossier du projet

Vous retrouverez le dossier de présentation et de développement du projet à l'adresse suivante : [https://drive.google.com/file/d/110x8hcd9mt5239VjqYGw1XFK_gQ3cX8s/view?usp=sharing](https://drive.google.com/file/d/110x8hcd9mt5239VjqYGw1XFK_gQ3cX8s/view?usp=sharing)
