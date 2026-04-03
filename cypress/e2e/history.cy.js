describe('History Tests', () => {
    beforeEach(() => {
        // Se connecter avant chaque test
        cy.visit('/login');
        cy.get('#username').type('test@example.com');
        cy.get('#password').type('password123');
        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/dashboard');
    });

    it('Test 1 - Accès à la page historique', () => {
        cy.visit('/history');

        // Vérifier que la page historique s'affiche
        cy.contains('Historique').should('exist');
    });

    it('Test 2 - Affichage des statistiques', () => {
        cy.visit('/history');

        // Vérifier la présence de l'indicateur "Total de PDFs"
        cy.contains('Total de PDFs').should('exist');
    });

    it('Test 3 - Page historique accessible depuis le menu', () => {
        // Cliquer sur le lien historique dans la sidebar
        cy.get('a[href="/history"]').click();

        // Vérifier qu'on est bien sur la page
        cy.url().should('include', '/history');
    });
});
