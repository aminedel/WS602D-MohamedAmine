describe('Authentication Tests', () => {
  beforeEach(() => {
    cy.visit('/');
  });

  it('Test 1 - Connexion valide', () => {
    cy.visit('/login');

    // Entrer les identifiants valides
    cy.get('#username').type('test@example.com');
    cy.get('#password').type('password123');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que l'utilisateur est redirigé vers le dashboard
    cy.url().should('include', '/dashboard');
    cy.contains('Bienvenue').should('exist');
  });

  it('Test 2 - Connexion invalide', () => {
    cy.visit('/login');

    // Entrer des identifiants invalides
    cy.get('#username').type('wrong@example.com');
    cy.get('#password').type('wrongpassword');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que le message d'erreur est affiché
    cy.contains('Invalid credentials').should('exist');
    cy.url().should('include', '/login');
  });
});
