<?php
ob_start();
?>

<div class="profile-page">
    <div class="profile-header">
        <h1>Mon profil</h1>
        <div class="user-info-card">
            <h2>Informations personnelles</h2>
            <div class="user-details">
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user->getUsername()) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user->getEmail()) ?></p>
                <p><strong>Statut :</strong> 
                    <?php if ($user->isAdmin()): ?>
                        <span class="badge admin">Administrateur</span>
                    <?php else: ?>
                        <span class="badge user">Utilisateur</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="reservations-section">
        <h2>Mes réservations</h2>
        
        <?php if (empty($reservations)): ?>
            <div class="no-reservations">
                <p>Vous n'avez encore aucune réservation.</p>
                <a href="/shows" class="btn btn-primary">Découvrir nos spectacles</a>
            </div>
        <?php else: ?>
            <div class="reservations-list">
                <?php foreach ($reservations as $item): ?>
                    <?php 
                    $reservation = $item['reservation'];
                    $show = $item['show'];
                    ?>
                    <div class="reservation-card">
                        <div class="reservation-header">
                            <h3><?= htmlspecialchars($show->getTitle()) ?></h3>
                            <span class="reservation-status <?= $reservation->getStatus() ?>">
                                <?= ucfirst($reservation->getStatus()) ?>
                            </span>
                        </div>
                        
                        <div class="reservation-details">
                            <div class="detail-row">
                                <span class="label">Date du spectacle :</span>
                                <span class="value"><?= $show->getFormattedDate() ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Lieu :</span>
                                <span class="value"><?= htmlspecialchars($show->getVenue()) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Nombre de places :</span>
                                <span class="value"><?= $reservation->getNumberOfSeats() ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Prix total :</span>
                                <span class="value price"><?= $reservation->getFormattedTotalPrice() ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Réservé le :</span>
                                <span class="value"><?= $reservation->getFormattedReservationDate() ?></span>
                            </div>
                        </div>
                        
                        <div class="reservation-actions">
                            <a href="/shows/<?= $show->getId() ?>" class="btn btn-secondary">Voir le spectacle</a>
                            <?php if ($reservation->isConfirmed() && $show->getDateTime() > new DateTime()): ?>
                                <button onclick="cancelReservation(<?= $reservation->getId() ?>)" class="btn btn-danger">Annuler</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="security-section">
        <h2>Sécurité</h2>
        <div class="security-card">
            <h3>Authentification à deux facteurs (TOTP)</h3>
            <div class="totp-status">
                <?php if ($user->isTotpEnabled()): ?>
                    <div class="totp-enabled">
                        <span class="status-badge enabled">✅ Activée</span>
                        <p>Votre compte est protégé par l'authentification à deux facteurs.</p>
                        <button id="disableTotpBtn" class="btn btn-danger">Désactiver TOTP</button>
                    </div>
                <?php else: ?>
                    <div class="totp-disabled">
                        <span class="status-badge disabled">❌ Désactivée</span>
                        <p>Améliorez la sécurité de votre compte en activant l'authentification à deux facteurs.</p>
                        <a href="/totp-setup" class="btn btn-primary">Activer TOTP</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="profile-actions">
        <h2>Actions</h2>
        <div class="action-buttons">
            <a href="/shows" class="btn btn-primary">Découvrir nos spectacles</a>
        </div>
    </div>
</div>

<script>
async function cancelReservation(reservationId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
        return;
    }
    
    try {
        const response = await fetch(`/reservations/${reservationId}/cancel`, {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showMessage(data.error, 'error');
        }
    } catch (error) {
        showMessage('Erreur lors de l\'annulation', 'error');
    }
}

// Gestion de la désactivation TOTP
document.getElementById('disableTotpBtn')?.addEventListener('click', async function() {
    const code = prompt('Entrez votre code TOTP à 6 chiffres pour désactiver l\'authentification à deux facteurs :');
    
    if (!code || code.length !== 6) {
        alert('Code TOTP invalide');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir désactiver l\'authentification à deux facteurs ?')) {
        return;
    }
    
    try {
        const response = await fetch('/auth/totp/disable', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({ totp_code: code })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('TOTP désactivé avec succès');
            window.location.reload();
        } else {
            alert('Erreur : ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la désactivation');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>