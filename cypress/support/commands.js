// ***********************************************
// Custom commands for Cypress
// ***********************************************

Cypress.Commands.add('login', (email, password) => {
    cy.visit('/login');
    cy.get('#username').type(email);
    cy.get('#password').type(password);
    cy.get('button[type="submit"]').click();
});

Cypress.Commands.add('logout', () => {
    cy.visit('/logout');
});
