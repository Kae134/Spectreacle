<?php
ob_start();
?>

<div class="dashboard">
    <h1>Tableau de bord</h1>
    
    <div class="user-info">
        <h2>Informations utilisateur</h2>
        <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user->getUsername()) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user->getEmail()) ?></p>
        <p><strong>Rôles :</strong> <?= implode(', ', $user->getRoles()) ?></p>
    </div>
    
    <div class="dashboard-actions">
        <h2>Actions rapides</h2>
        <div class="action-buttons">
            <a href="/shows" class="btn btn-primary">Voir les spectacles</a>
            <a href="/profile" class="btn btn-secondary">Mon profil</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>