<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Spectreacle' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎭</text></svg>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="Spectreacle - Plateforme de réservation de spectacles en ligne">
    <meta name="keywords" content="spectacle, théâtre, réservation, billetterie">
    <meta name="author" content="Spectreacle">
    
    <!-- Social Media Meta Tags -->
    <meta property="og:title" content="<?= $title ?? 'Spectreacle' ?>">
    <meta property="og:description" content="Découvrez et réservez vos spectacles favoris sur Spectreacle">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?? '' ?>">
    
    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#3b82f6">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="/" class="nav-brand">
                <span class="brand-icon">🎭</span>
                <span class="brand-text">Spectreacle</span>
            </a>
            
            <!-- Mobile menu button -->
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu mobile">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="/" class="nav-link"><span class="nav-icon">🏠</span>Accueil</a></li>
                <li><a href="/shows" class="nav-link"><span class="nav-icon">🎪</span>Spectacles</a></li>
                <?php if (isset($user) && $user): ?>
                    <li><a href="/profile" class="nav-link"><span class="nav-icon">👤</span>Mon profil</a></li>
                    <?php if ($user->isAdmin()): ?>
                        <li><a href="/admin" class="nav-link"><span class="nav-icon">⚙️</span>Administration</a></li>
                    <?php endif; ?>
                    <li class="user-greeting">
                        <span class="greeting-text">Bonjour, <strong><?= htmlspecialchars($user->getUsername()) ?></strong></span>
                    </li>
                    <li><button onclick="logout()" class="btn-logout">
                        <span class="logout-icon">🚪</span>
                        Déconnexion
                    </button></li>
                <?php else: ?>
                    <li><a href="/login" class="nav-link"><span class="nav-icon">🔑</span>Connexion</a></li>
                    <li><a href="/register" class="nav-link btn-register"><span class="nav-icon">✨</span>Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <!-- Loading indicator -->
        <div class="loading-indicator" id="loading-indicator">
            <div class="loading-spinner"></div>
        </div>
        
        <!-- Page content -->
        <div class="page-wrapper">
            <?= $content ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">
                        <span class="footer-icon">🎭</span>
                        Spectreacle
                    </h3>
                    <p class="footer-description">
                        Votre plateforme de réservation de spectacles en ligne. 
                        Découvrez et réservez facilement vos événements favoris.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="/">Accueil</a></li>
                        <li><a href="/shows">Spectacles</a></li>
                        <?php if (isset($user) && $user): ?>
                            <li><a href="/profile">Mon profil</a></li>
                        <?php else: ?>
                            <li><a href="/login">Connexion</a></li>
                            <li><a href="/register">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Informations</h4>
                    <ul class="footer-links">
                        <li><a href="#" onclick="showModal('about')">À propos</a></li>
                        <li><a href="#" onclick="showModal('contact')">Contact</a></li>
                        <li><a href="#" onclick="showModal('privacy')">Confidentialité</a></li>
                        <li><a href="#" onclick="showModal('terms')">Conditions</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Spectreacle. Tous droits réservés.</p>
                <p class="footer-made-with">
                    Fait avec ❤️ et <a href="https://claude.ai" target="_blank">Claude</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <button class="back-to-top" id="back-to-top" aria-label="Retour en haut">
        <span class="back-to-top-icon">⬆️</span>
    </button>

    <!-- Toast notification container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Modal container for info pages -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-content" id="modal-content">
            <button class="modal-close" id="modal-close" aria-label="Fermer">✕</button>
            <div class="modal-body" id="modal-body"></div>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>