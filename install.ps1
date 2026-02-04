# 🚀 Script d'Installation Automatique
# WR602D - PDF Generator

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  WR602D - PDF Generator - Installation" -ForegroundColor Cyan
Write-Host "  Par Mohamed Amine (@aminedel)" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Composer est installé
Write-Host "📦 Vérification de Composer..." -ForegroundColor Yellow
if (!(Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Composer n'est pas installé. Veuillez l'installer depuis https://getcomposer.org" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Composer trouvé" -ForegroundColor Green

# Vérifier si PHP est installé
Write-Host "🐘 Vérification de PHP..." -ForegroundColor Yellow
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "❌ PHP n'est pas installé. Veuillez l'installer (version 8.2+)" -ForegroundColor Red
    exit 1
}
$phpVersion = php -v
Write-Host "✅ PHP trouvé: $($phpVersion.Split([Environment]::NewLine)[0])" -ForegroundColor Green

# Installation des dépendances Composer
Write-Host ""
Write-Host "📥 Installation des dépendances Composer..." -ForegroundColor Yellow
composer install --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur lors de l'installation des dépendances" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Dépendances installées" -ForegroundColor Green

# Installation de Node.js et Cypress
Write-Host ""
Write-Host "📥 Installation de Cypress..." -ForegroundColor Yellow
if (Get-Command npm -ErrorAction SilentlyContinue) {
    npm install
    Write-Host "✅ Cypress installé" -ForegroundColor Green
} else {
    Write-Host "⚠️  npm non trouvé. Cypress ne sera pas installé." -ForegroundColor Yellow
    Write-Host "   Installez Node.js depuis https://nodejs.org" -ForegroundColor Yellow
}

# Configuration de la base de données
Write-Host ""
Write-Host "🗄️  Configuration de la base de données..." -ForegroundColor Yellow
Write-Host "   Assurez-vous que MySQL est démarré sur localhost:3306" -ForegroundColor Cyan

$createDb = Read-Host "Voulez-vous créer la base de données maintenant? (o/n)"
if ($createDb -eq "o" -or $createDb -eq "O") {
    php bin/console doctrine:database:create --if-not-exists
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Base de données créée" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Erreur lors de la création de la base de données" -ForegroundColor Yellow
    }
    
    # Migrations
    Write-Host ""
    Write-Host "🔄 Exécution des migrations..." -ForegroundColor Yellow
    php bin/console doctrine:migrations:migrate --no-interaction
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Migrations exécutées" -ForegroundColor Green
    }
    
    # Fixtures
    Write-Host ""
    Write-Host "📊 Chargement des fixtures (plans FREE, BASIC, PREMIUM)..." -ForegroundColor Yellow
    php bin/console doctrine:fixtures:load --no-interaction
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Fixtures chargées" -ForegroundColor Green
    }
}

# Résumé
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  ✅ Installation terminée!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📝 Prochaines étapes:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Démarrer Gotenberg (dans un terminal séparé):" -ForegroundColor White
Write-Host "   docker run --rm -p 3000:3000 gotenberg/gotenberg:8" -ForegroundColor Cyan
Write-Host ""
Write-Host "2. Démarrer le serveur Symfony:" -ForegroundColor White
Write-Host "   symfony server:start" -ForegroundColor Cyan
Write-Host "   ou" -ForegroundColor Gray
Write-Host "   php -S localhost:8000 -t public/" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Ouvrir l'application:" -ForegroundColor White
Write-Host "   http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "4. Créer un compte et tester!" -ForegroundColor White
Write-Host ""
Write-Host "📚 Documentation:" -ForegroundColor Yellow
Write-Host "   - README.md - Documentation complète" -ForegroundColor White
Write-Host "   - QUICKSTART.md - Guide de démarrage rapide" -ForegroundColor White
Write-Host "   - SUBMISSION.md - Instructions de rendu" -ForegroundColor White
Write-Host ""
Write-Host "🧪 Tests:" -ForegroundColor Yellow
Write-Host "   vendor/bin/phpunit - Tests unitaires" -ForegroundColor White
Write-Host "   npx cypress open - Tests E2E" -ForegroundColor White
Write-Host ""
Write-Host "Bon développement! 🚀" -ForegroundColor Green
