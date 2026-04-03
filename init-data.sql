-- Insert plans if not exist
INSERT IGNORE INTO plan (id, name, description, limit_generation, role, price, active, created_at, stripe_price_id)
VALUES
(1, 'FREE', 'Plan gratuit pour decouvrir notre service. Acces aux outils de base avec 2 generations par jour.', 2, 'ROLE_USER', '0.00', 1, NOW(), NULL),
(2, 'BASIC', 'Pour les utilisateurs reguliers. Acces a HTML, Markdown et Office avec 10 generations par jour.', 10, 'ROLE_USER', '9.90', 1, NOW(), NULL),
(3, 'PREMIUM', 'Acces illimite a tous les outils. Generation illimitee avec support prioritaire.', NULL, 'ROLE_PREMIUM', '45.00', 1, NOW(), NULL);

-- Insert tools if not exist
INSERT IGNORE INTO tool (id, name, slug)
VALUES
(1, 'URL vers PDF', 'url'),
(2, 'Fusion PDF', 'merge'),
(3, 'HTML vers PDF', 'html'),
(4, 'Markdown vers PDF', 'markdown'),
(5, 'Office vers PDF', 'office'),
(6, 'Capture ecran vers PNG', 'screenshot'),
(7, 'WYSIWYG vers PDF', 'wysiwyg');

-- Link tools to plans
INSERT IGNORE INTO plan_tool (plan_id, tool_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7);

-- Insert test user (password: password123, bcrypt hash)
INSERT IGNORE INTO `user` (id, email, roles, password, lastname, firstname, phone, plan_id, created_at, is_verified)
VALUES
(1, 'test@example.com', '[]', '$2y$13$hK1kG1yrSqMfGdJvH7qK4OWmHesVmF6VZOdwL3HMQNXH4Q0WjXmS2', 'Amine', 'Mohamed', '+33612345678', 1, NOW(), 1),
(2, 'admin@example.com', '["ROLE_ADMIN"]', '$2y$13$hK1kG1yrSqMfGdJvH7qK4OWmHesVmF6VZOdwL3HMQNXH4Q0WjXmS2', 'System', 'Admin', NULL, 3, NOW(), 1);
