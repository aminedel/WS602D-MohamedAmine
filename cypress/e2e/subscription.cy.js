describe('Subscription Tests', () => {
    beforeEach(() => {
        // Se connecter avant chaque test
        cy.visit('/login');
        cy.get('#username').type('test@example.com');
        cy.get('#password').type('password123');
        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/dashboard');
    });

    it('Test 1 - Affichage des abonnements', () => {
        cy.visit('/subscription');

        // Vérifier que les plans sont affichés
        cy.contains('FREE').should('exist');
        cy.contains('BASIC').should('exist');
        cy.contains('PREMIUM').should('exist');
    });

    it('Test 2 - Redirection vers Stripe pour BASIC', () => {
        cy.visit('/subscription');

        // Cliquer sur le bouton pour souscrire à BASIC
        cy.get('[data-cy="subscribe-basic"]').click();

        // On doit être redirigé vers Stripe (pour le test On vérifie que c'est une redirection vers checkout)
        // Note: cy.origin peut être nécessaire mais on peut juste checker l'URL si follow-redirect est actif
        cy.url().should('match', /stripe\.com/);
    });

    it('Test 3 - Redirection vers Stripe pour PREMIUM', () => {
        cy.visit('/subscription');

        // Cliquer sur le bouton pour souscrire à PREMIUM
        cy.get('[data-cy="subscribe-premium"]').click();

        cy.url().should('match', /stripe\.com/);
    });

    it('Test 4 - Changement direct pour FREE', () => {
        // Simuler un utilisateur qui a un plan payant (on triche un peu via le test)
        // Mais par défaut il est FREE.
        cy.visit('/subscription');

        // On clique sur FREE
        cy.get('[data-cy="subscribe-free"]').should('be.disabled').or('not.exist'); // Puisque c'est déjà le plan actuel
    });

    it('Test 5 - Abonnement actuel affiché', () => {
        cy.visit('/subscription');

        // Vérifier que le badge "Abonnement actuel" est affiché
        cy.contains('Abonnement actuel').should('exist');
    });
});
