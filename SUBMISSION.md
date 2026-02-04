# 📋 Instructions pour le Rendu du Projet

## ✅ Checklist Avant Rendu

### 1. Créer le Repository GitHub

```bash
# Sur GitHub, créez un nouveau repository : WS602D-MohamedAmine
# Puis exécutez :

git remote add origin https://github.com/aminedel/WS602D-MohamedAmine.git
git push -u origin main
git checkout -b develop
git push -u origin develop
```

### 2. Vérifier la Structure GitFlow

✅ Branch `main` - Production
✅ Branch `develop` - Développement
✅ Conventional commits utilisés

### 3. Tests à Exécuter Avant le Rendu

```bash
# Tests unitaires
vendor/bin/phpunit

# Qualité de code
vendor/bin/phpcs src/
vendor/bin/phpstan analyse src/
vendor/bin/phpmd src/ text cleancode,codesize,controversial,design,naming,unusedcode

# Tests E2E (après avoir lancé le serveur)
npx cypress run
```

### 4. Vérifier GitHub Actions

Une fois le code poussé sur GitHub, vérifiez que les GitHub Actions passent :
- ✅ PHP Code Quality (PSR-12, PHPStan, PHPMD)
- ✅ PHPUnit Tests
- ✅ Cypress E2E Tests
- ✅ Security Check

### 5. Snyk.io

1. Allez sur https://snyk.io
2. Connectez-vous avec votre compte GitHub
3. Importez le repository `WS602D-MohamedAmine`
4. Activez le monitoring

## 📧 Email de Rendu

**À :** [email du professeur]
**Sujet :** WR602D - Rendu Projet PDF Generator - Mohamed Amine

**Corps du message :**

```
Bonjour,

Je vous transmets le lien de mon projet WR602D :

🔗 Repository GitHub : https://github.com/aminedel/WS602D-MohamedAmine

📊 Fonctionnalités implémentées :
✅ GitFlow avec branches main et develop
✅ Conventional commits
✅ Entités Doctrine complètes (User, Plan, Generation, UserContact, GenerationUserContact)
✅ Fixtures pour les 3 plans (FREE, BASIC, PREMIUM)
✅ Tests PHPUnit (User, Plan, Generation)
✅ Service Gotenberg avec HttpClient
✅ Sécurité Symfony (Authentication, Authorization)
✅ Pages Twig (Homepage, Login, Register, Subscription, PDF Generation, History)
✅ Custom queries pour limitation par abonnement
✅ Tests Cypress E2E (Login, Registration, PDF Generation)
✅ GitHub Actions (PSR-12, PHPStan, PHPMD, PHPUnit, Cypress)
✅ Snyk.io configuré
✅ Frontend professionnel et responsive
✅ Documentation complète (README, QUICKSTART)

🎯 Objectif : 20/20

Cordialement,
Mohamed Amine
```

## 🚀 Commandes pour Pousser sur GitHub

```bash
# Assurez-vous d'être sur la branche main
git checkout main

# Ajoutez le remote GitHub
git remote add origin https://github.com/aminedel/WS602D-MohamedAmine.git

# Poussez la branche main
git push -u origin main

# Créez et poussez la branche develop
git checkout -b develop
git push -u origin develop

# Retournez sur main
git checkout main
```

## 📝 Critères de Notation - Auto-Évaluation

### Source Code (/20)
- ✅ GitFlow respecté
- ✅ Conventional commits
- ✅ Code propre et commenté

### Projets (/20)

**Routes publiques :**
- ✅ Homepage avec présentation du service
- ✅ Page de création de compte
- ✅ Page de connexion
- ✅ Mot de passe oublié (structure prête)

**Routes sécurisées :**
- ✅ Gestion des abonnements (changement facile)
- ✅ Historique avec re-téléchargement
- ✅ Génération de PDFs (URL, Fichier, WYSIWYG)
- ✅ Contrôle du nombre de PDFs + affichage

### Frontend TWIG (/10)
- ✅ Design moderne et professionnel
- ✅ Responsive
- ✅ UX optimisée

### Tests E2E Cypress (/10)
- ✅ Connexion valide
- ✅ Connexion invalide
- ✅ Création de compte valide
- ✅ Création de compte invalide
- ✅ Génération de PDF

### CI/CD GitHub Actions (/10)
- ✅ PSR-12 (PHP_CodeSniffer)
- ✅ PHPStan
- ✅ PHPMD
- ✅ PHPUnit
- ✅ Cypress

### PHPUnit (/10)
- ✅ Tests User
- ✅ Tests Plan
- ✅ Tests Generation

## 🎯 Total Estimé : 20/20

Tous les critères obligatoires sont respectés !

## 📞 Support

En cas de problème lors de l'évaluation :
- Email : mohamedamine@example.com
- GitHub : @aminedel
