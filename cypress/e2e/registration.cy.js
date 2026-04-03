describe('Registration Tests', () => {
    beforeEach(() => {
        cy.visit('/register');
    });

    it('Test 1 - Création de compte valide', () => {
        const randomEmail = `user${Date.now()}@example.com`;

        // Remplir le formulaire d'inscription
        cy.get('#registration_form_firstname').type('John');
        cy.get('#registration_form_lastname').type('Doe');
        cy.get('#registration_form_email').type(randomEmail);
        cy.get('#registration_form_phone').type('+33612345678');
        cy.get('#registration_form_plainPassword').type('password123');
        cy.get('#registration_form_agreeTerms').check();

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier la redirection vers le dashboard (car le contrôleur connecte l'utilisateur automatiquement)
        cy.url().should('include', '/dashboard');
        cy.contains('Bienvenue').should('exist');
    });

    it('Test 2 - Création de compte invalide (email existant)', () => {
        // Utiliser un email qui existe déjà
        cy.get('#registration_form_firstname').type('Jane');
        cy.get('#registration_form_lastname').type('Smith');
        cy.get('#registration_form_email').type('test@example.com'); // Email existant
        cy.get('#registration_form_plainPassword').type('password123');
        cy.get('#registration_form_agreeTerms').check();

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier qu'une erreur est affichée
        cy.contains('already an account').should('exist');
    });

    it('Test 3 - Validation du formulaire (champs requis)', () => {
        // Soumettre le formulaire vide
        cy.get('button[type="submit"]').click();

        // Vérifier que le formulaire n'est pas soumis
        cy.url().should('include', '/register');
    });

    it('Test 4 - Mot de passe trop court', () => {
        cy.get('#registration_form_firstname').type('Test');
        cy.get('#registration_form_lastname').type('User');
        cy.get('#registration_form_email').type('test2@example.com');
        cy.get('#registration_form_plainPassword').type('123'); // Trop court
        cy.get('#registration_form_agreeTerms').check();

        cy.get('button[type="submit"]').click();

        // Vérifier l'erreur de validation
        cy.contains('au moins 6 caractères').should('exist');
    });
});
