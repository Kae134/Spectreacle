<?php
ob_start();
?>

<div class="hero">
    <h1>Bienvenue sur Spectreacle</h1>
    <p class="hero-subtitle">Votre plateforme de réservation de spectacles</p>
    
    <?php if (isset($user) && $user): ?>
        <div class="welcome-message">
            <h2>Bonjour, <?= htmlspecialchars($user->getUsername()) ?> !</h2>
            <p>Vous êtes connecté avec succès.</p>
            <div class="user-actions">
                <a href="/dashboard" class="btn btn-primary">Accéder au tableau de bord</a>
                <?php if ($user->isAdmin()): ?>
                    <a href="/admin" class="btn btn-secondary">Panel administrateur</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="guest-actions">
            <p>Connectez-vous pour réserver vos spectacles préférés</p>
            <a href="/login" class="btn btn-primary">Se connecter</a>
        </div>
    <?php endif; ?>
</div>

<div class="features">
    <h2>Nos spectacles</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>Théâtre</h3>
            <p>Découvrez nos pièces de théâtre classiques et contemporaines</p>
        </div>
        <div class="feature-card">
            <h3>Concerts</h3>
            <p>Profitez de concerts de musique classique et moderne</p>
        </div>
        <div class="feature-card">
            <h3>Opéra</h3>
            <p>Laissez-vous emporter par nos représentations d'opéra</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>