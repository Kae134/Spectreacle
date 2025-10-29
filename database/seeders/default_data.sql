-- Insérer les utilisateurs par défaut
INSERT INTO users (id, username, email, password, role, created_at, updated_at) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'admin', 'admin@App.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ROLE_ADMIN', NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440001', 'user', 'user@App.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ROLE_USER', NOW(), NOW());

-- Note: Le mot de passe hashé correspond à "password" pour les deux comptes

-- Insérer des spectacles par défaut
INSERT INTO shows (title, description, date, location, price, available_seats, image_url, created_at, updated_at) VALUES
('Le Malade Imaginaire', 'Comédie de Molière présentée par la troupe du Théâtre National', '2025-12-15 20:00:00', 'Théâtre Municipal', 25.00, 150, 'https://via.placeholder.com/400x300', NOW(), NOW()),
('Cyrano de Bergerac', 'Une mise en scène moderne de la pièce d\'Edmond Rostand', '2025-12-20 19:30:00', 'Scène Nationale', 30.00, 200, 'https://via.placeholder.com/400x300', NOW(), NOW()),
('Roméo et Juliette', 'Ballet sur la musique de Prokofiev', '2026-01-10 20:30:00', 'Opéra Garnier', 45.00, 300, 'https://via.placeholder.com/400x300', NOW(), NOW());