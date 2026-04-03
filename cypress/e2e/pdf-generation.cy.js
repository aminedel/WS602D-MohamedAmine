describe('PDF Generation Tests', () => {
    beforeEach(() => {
        // Login before each test
        cy.visit('/login');
        cy.get('#username').type('test@example.com');
        cy.get('#password').type('password123');
        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/dashboard');
    });

    it('Test 1 - Access URL to PDF tool', () => {
        cy.visit('/convert/url');

        // Verify the conversion page loads
        cy.contains('URL').should('exist');

        // Verify quota is displayed
        cy.contains('generation').should('exist');
    });

    it('Test 2 - Submit URL for PDF generation', () => {
        cy.visit('/convert/url');

        // Enter a URL
        cy.get('#url').type('https://example.com');

        // Submit the form
        cy.get('button[type="submit"]').click();
    });

    it('Test 3 - Access denied for restricted tools', () => {
        // The free plan should not have access to screenshot
        cy.request({
            url: '/convert/screenshot',
            failOnStatusCode: false,
            followRedirect: false
        }).then((response) => {
            expect(response.status).to.be.oneOf([403, 302]);
        });
    });

    it('Test 4 - Navigate to history after generation', () => {
        cy.visit('/history');
        // Verify history page loads
        cy.contains('Historique').should('exist');
    });
});
