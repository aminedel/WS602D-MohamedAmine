describe('PDF Factory Workflow', () => {
    it('Should fail login with wrong credentials', () => {
        cy.visit('/login');
        cy.get('input[name="_username"]').type('wrong@email.com');
        cy.get('input[name="_password"]').type('wrongpassword');
        cy.get('button[type="submit"]').click();
        cy.get('.alert-danger').should('be.visible');
    });

    it('Should register a new user', () => {
        cy.visit('/register');
        const randomEmail = `test${Date.now()}@example.com`;
        cy.get('input[name="registration_form[firstname]"]').type('Test');
        cy.get('input[name="registration_form[lastname]"]').type('User');
        cy.get('input[name="registration_form[phone]"]').type('0600000000');
        cy.get('input[name="registration_form[email]"]').type(randomEmail);
        cy.get('input[name="registration_form[plainPassword]"]').type('password123');
        cy.get('input[name="registration_form[agreeTerms]"]').check();
        cy.get('button[type="submit"]').click();

        // Vérifier la redirection ou le message flash
        cy.get('.alert-success').should('contain', 'Account created');
    });

    it('Should handle pdf generation queueing after login', () => {
        cy.visit('/login');
        // On suppose qu'un utilisateur de test de type "user@example.com" existe via les fixtures
        // et possède le plan PREMIUM
        cy.get('input[name="_username"]').type('user@example.com');
        cy.get('input[name="_password"]').type('password');
        cy.get('button[type="submit"]').click();

        // Accès hypothétique à la page de rendu URL
        cy.visit('/convert/url');
        cy.get('input[name="url"]').type('https://example.com');
        cy.get('button[type="submit"]').click();

        // Doit avoir été en queue
        cy.get('.alert-success').should('contain', 'file d\'attente');
    });
});
