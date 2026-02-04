# 🚀 Guide de Démarrage Rapide

## Installation

### 1. Cloner le projet
```bash
git clone https://github.com/aminedel/WS602D-MohamedAmine.git
cd WS602D-MohamedAmine
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration
```bash
cp .env .env.local
# Modifier DATABASE_URL et GOTENBERG_URL dans .env.local si nécessaire
```

### 4. Base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Lancer l'application

#### Option A : Avec Docker (recommandé)
```bash
docker-compose up -d
```
L'application sera accessible sur http://localhost:8000

#### Option B : Sans Docker
```bash
# Terminal 1 - Gotenberg
docker run --rm -p 3000:3000 gotenberg/gotenberg:8

# Terminal 2 - Symfony
symfony server:start
# ou
php -S localhost:8000 -t public/
```

## Tests

### Tests unitaires (PHPUnit)
```bash
vendor/bin/phpunit
```

### Tests E2E (Cypress)
```bash
# Interface graphique
npx cypress open

# Headless
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

## Comptes de test

Après avoir chargé les fixtures, vous pouvez créer un compte de test :

```bash
php bin/console doctrine:fixtures:load
```

Puis créez un compte via l'interface web : http://localhost:8000/register

## Structure du projet

```
WS602D-MohamedAmine/
├── .github/workflows/     # CI/CD GitHub Actions
├── config/                # Configuration Symfony
├── cypress/               # Tests E2E
├── public/                # Point d'entrée web
├── src/
│   ├── Controller/        # Contrôleurs
│   ├── Entity/            # Entités Doctrine
│   ├── Form/              # Formulaires
│   ├── Repository/        # Repositories
│   ├── Service/           # Services (Gotenberg)
│   └── DataFixtures/      # Fixtures
├── templates/             # Templates Twig
├── tests/                 # Tests PHPUnit
└── var/                   # Cache, logs
```

## Fonctionnalités

✅ Authentification complète (inscription, connexion, déconnexion)
✅ 3 plans d'abonnement (FREE, BASIC, PREMIUM)
✅ Génération PDF (URL, Fichier, WYSIWYG)
✅ Limitation par abonnement
✅ Historique des générations
✅ Tests unitaires et E2E
✅ CI/CD avec GitHub Actions
✅ GitFlow

## Support

Pour toute question : mohamedamine@example.com
