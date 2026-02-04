describe('PDF Generation Tests', () => {
    beforeEach(() => {
        // Se connecter avant chaque test
        cy.visit('/login');
        cy.get('#username').type('test@example.com');
        cy.get('#password').type('password123');
        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/dashboard');
    });

    it('Test 1 - Génération PDF depuis URL', () => {
        cy.visit('/pdf/generate');

        // Sélectionner le type URL
        cy.get('input[value="url"]').check();

        // Entrer une URL
        cy.get('#pdf_generation_url').type('https://symfony.com');

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier le succès
        cy.contains('PDF a été généré avec succès', { timeout: 15000 }).should('exist');
    });

    it('Test 2 - Vérification de la limite d\'abonnement', () => {
        cy.visit('/pdf/generate');

        // Vérifier que la limite est affichée
        cy.contains('restant').should('exist');
    });

    it('Test 3 - Génération avec URL invalide', () => {
        cy.visit('/pdf/generate');

        // Sélectionner le type URL
        cy.get('input[value="url"]').check();

        // Entrer une URL invalide
        cy.get('#pdf_generation_url').type('not-a-valid-url');

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier l'erreur
        cy.contains('URL valide').should('exist');
    });

    it('Test 4 - Navigation vers l\'historique après génération', () => {
        cy.visit('/pdf/generate');

        cy.get('input[value="url"]').check();
        cy.get('#pdf_generation_url').type('https://example.com');
        cy.get('button[type="submit"]').click();

        // Attendre la génération
        cy.wait(3000);

        // Aller à l'historique
        cy.visit('/history');

        // Vérifier qu'il y a au moins un PDF
        cy.get('table').should('exist');
    });
});
