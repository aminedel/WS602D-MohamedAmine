# 📄 WR602D - PDF Generation Microservice

![Symfony](https://img.shields.io/badge/Symfony-7.2-black?style=for-the-badge&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Gotenberg](https://img.shields.io/badge/Gotenberg-PDF-orange?style=for-the-badge)

## 🎯 Description

Application Symfony de génération de PDF utilisant le micro-service **Gotenberg**. 

Le projet inclut :
- 🔐 **Authentification complète** (inscription, connexion, mot de passe oublié)
- 💳 **Gestion d'abonnements** (FREE, BASIC, PREMIUM)
- 📄 **Génération de PDF** (URL, Fichier, WYSIWYG)
- 📊 **Historique** des générations
- ✅ **Tests unitaires** (PHPUnit) et **E2E** (Cypress)
- 🚀 **CI/CD** avec GitHub Actions

---

## 🚀 Installation Rapide

### Prérequis
- Docker & Docker Compose
- Git
- Composer
- Node.js & npm (pour Cypress)

### 1️⃣ Cloner le projet
```bash
git clone https://github.com/aminedel/WS602D-MohamedAmine.git
cd WS602D-MohamedAmine
```

### 2️⃣ Installer les dépendances
```bash
composer install
```

### 3️⃣ Configuration
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Modifier les variables dans .env.local si nécessaire
```

### 4️⃣ Base de données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (plans FREE, BASIC, PREMIUM)
php bin/console doctrine:fixtures:load
```

### 5️⃣ Lancer l'application
```bash
# Démarrer le serveur Symfony
symfony server:start

# Ou avec PHP
php -S localhost:8000 -t public/
```

### 6️⃣ Lancer Gotenberg (Docker)
```bash
docker run --rm -p 3000:3000 gotenberg/gotenberg:8
```

---

## 📚 Documentation

### Structure du projet
```
WS602D-MohamedAmine/
├── config/              # Configuration Symfony
├── src/
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine
│   ├── Form/            # Formulaires Twig
│   ├── Repository/      # Repositories personnalisés
│   ├── Service/         # Services (Gotenberg, etc.)
│   └── Security/        # Authenticators
├── templates/           # Templates Twig
├── tests/               # Tests PHPUnit
├── cypress/             # Tests E2E Cypress
├── public/              # Assets publics
└── var/                 # Cache, logs
```

### Entités principales

#### 👤 User
- email, password, firstname, lastname
- dob (date of birth), phone, photo, favorite_color
- Relations : Plan, Generations

#### 📋 Plan
- name, description, price, special_price
- limit_generation (nombre de PDFs autorisés)
- active, created_at

#### 📄 Generation
- user_id, file, created_at
- Relations : User, UserContacts

#### 📇 UserContact
- Contacts de l'utilisateur pour partage de PDF

---

## 🧪 Tests

### Tests unitaires (PHPUnit)
```bash
# Lancer tous les tests
vendor/bin/phpunit

# Tests spécifiques
vendor/bin/phpunit tests/Entity/UserTest.php
vendor/bin/phpunit tests/Entity/PlanTest.php
vendor/bin/phpunit tests/Entity/GenerationTest.php
```

### Tests E2E (Cypress)
```bash
# Installer Cypress
npm install

# Ouvrir Cypress UI
npx cypress open

# Lancer les tests en headless
npx cypress run
```

### Qualité de code
```bash
# PHP_CodeSniffer
vendor/bin/phpcs src/

# PHPStan
vendor/bin/phpstan analyse src/

# PHPMD
vendor/bin/phpmd src/ text cleancode,codesize,controversial,design,naming,unusedcode
```

---

## 🔐 Sécurité

### Routes publiques
- `/` - Homepage
- `/register` - Inscription
- `/login` - Connexion
- `/reset-password` - Mot de passe oublié

### Routes sécurisées (ROLE_USER)
- `/dashboard` - Tableau de bord
- `/subscription/change` - Changement d'abonnement
- `/pdf/generate` - Génération de PDF
- `/history` - Historique des générations

---

## 📊 Fonctionnalités

### 1. Génération de PDF
- **URL to PDF** : Convertir une URL en PDF
- **Fichier to PDF** : Convertir un fichier en PDF
- **WYSIWYG to PDF** : Éditeur HTML vers PDF

### 2. Système d'abonnements
| Plan | Prix | Limite PDF/jour |
|------|------|-----------------|
| FREE | 0€ | 2 |
| BASIC | 9.99€ | 50 |
| PREMIUM | 29.99€ | Illimité |

### 3. Historique
- Consultation des PDFs générés
- Re-téléchargement des PDFs
- Filtrage par date

---

## 🌐 API Gotenberg

### Configuration
```env
# .env.local
GOTENBERG_URL=http://localhost:3000
```

### Endpoints utilisés
- `POST /forms/chromium/convert/url` - URL to PDF
- `POST /forms/chromium/convert/html` - HTML to PDF
- `POST /forms/libreoffice/convert` - Document to PDF

---

## 🔄 GitFlow

### Branches
- `main` - Production
- `develop` - Développement
- `feature/*` - Nouvelles fonctionnalités
- `hotfix/*` - Corrections urgentes

### Conventional Commits
```
feat: Ajout de la génération PDF depuis URL
fix: Correction du bug de limitation d'abonnement
docs: Mise à jour du README
test: Ajout des tests Cypress pour la connexion
```

---

## 🚀 CI/CD (GitHub Actions)

### Workflows automatiques
- ✅ PHP_CodeSniffer (PSR-12)
- ✅ PHPStan (niveau 6)
- ✅ PHPMD
- ✅ PHPUnit
- ✅ Cypress E2E

---

## 👨‍💻 Auteur

**Mohamed Amine**
- GitHub: [@aminedel](https://github.com/aminedel)
- Projet: WR602D - Développement Web et dispositif interactif

---

## 📝 Licence

Ce projet est réalisé dans le cadre du module WR602D.

---

## 🎓 Critères de notation respectés

- ✅ GitFlow obligatoire
- ✅ Conventional commits
- ✅ Entités Doctrine complètes
- ✅ Fixtures pour les plans
- ✅ Tests PHPUnit (User, Plan, Generation)
- ✅ GitHub Actions (PSR, PHPStan, PHPMD)
- ✅ Service Gotenberg (HttpClient)
- ✅ Sécurité Symfony (Authentication, Authorization)
- ✅ Pages Twig (Homepage, Login, Register, Subscription, PDF, History)
- ✅ Custom queries (limitation par abonnement)
- ✅ Tests Cypress E2E
- ✅ Frontend professionnel
- ✅ Documentation complète

**Objectif : 20/20** 🎯
