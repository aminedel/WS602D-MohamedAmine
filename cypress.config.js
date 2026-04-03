const { defineConfig } = require("cypress");

module.exports = defineConfig({
    e2e: {
        baseUrl: 'http://localhost:8000', // L'adresse de l'hôte mappée vers le conteneur web
        setupNodeEvents(on, config) {
            // implémentation des événements node
        },
    },
});
