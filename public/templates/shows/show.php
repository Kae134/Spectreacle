<?php
ob_start();
?>

<div class="show-detail">
    <div class="show-header">
        <?php if ($show->getImageUrl()): ?>
            <div class="show-image-large">
                <img src="<?= htmlspecialchars($show->getImageUrl()) ?>" alt="<?= htmlspecialchars($show->getTitle()) ?>">
            </div>
        <?php endif; ?>
        
        <div class="show-info">
            <h1><?= htmlspecialchars($show->getTitle()) ?></h1>
            <span class="show-category-badge"><?= ucfirst(htmlspecialchars($show->getCategory())) ?></span>
            
            <div class="show-meta">
                <div class="meta-item">
                    <strong>Date :</strong> <?= $show->getFormattedDate() ?>
                </div>
                <div class="meta-item">
                    <strong>Lieu :</strong> <?= htmlspecialchars($show->getVenue()) ?>
                </div>
                <div class="meta-item">
                    <strong>Prix :</strong> <?= $show->getFormattedPrice() ?>
                </div>
                <div class="meta-item">
                    <strong>Places disponibles :</strong> <?= $show->getAvailableSeats() ?>/<?= $show->getTotalSeats() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="show-description">
        <h2>Description</h2>
        <p><?= nl2br(htmlspecialchars($show->getDescription())) ?></p>
    </div>

    <?php if (isset($user) && $user && $show->isAvailable()): ?>
        <div class="reservation-section" id="reservation">
            <h2>Réservation</h2>
            <div class="reservation-form">
                <form id="reservationForm">
                    <input type="hidden" id="showId" value="<?= $show->getId() ?>">
                    <div class="form-group">
                        <label for="numberOfSeats">Nombre de places :</label>
                        <select id="numberOfSeats" name="numberOfSeats" required>
                            <?php for ($i = 1; $i <= min(8, $show->getAvailableSeats()); $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> place<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="price-calculation">
                        <p>Prix total : <span id="totalPrice"><?= $show->getFormattedPrice() ?></span></p>
                    </div>
                    <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                </form>
                <div id="reservationMessage" class="message"></div>
            </div>
        </div>
    <?php elseif (!isset($user) || !$user): ?>
        <div class="auth-required">
            <h2>Réservation</h2>
            <p>Vous devez être connecté pour effectuer une réservation.</p>
            <a href="/login" class="btn btn-primary">Se connecter</a>
            <a href="/register" class="btn btn-secondary">S'inscrire</a>
        </div>
    <?php elseif (!$show->isAvailable()): ?>
        <div class="sold-out">
            <h2>Spectacle complet</h2>
            <p>Désolé, ce spectacle n'a plus de places disponibles.</p>
        </div>
    <?php endif; ?>

    <div class="navigation">
        <a href="/shows" class="btn btn-secondary">← Retour à la liste</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const numberOfSeatsSelect = document.getElementById('numberOfSeats');
    const totalPriceSpan = document.getElementById('totalPrice');
    const reservationForm = document.getElementById('reservationForm');
    const pricePerSeat = <?= $show->getPrice() ?>;

    if (numberOfSeatsSelect && totalPriceSpan) {
        numberOfSeatsSelect.addEventListener('change', function() {
            const numberOfSeats = parseInt(this.value);
            const totalPrice = numberOfSeats * pricePerSeat;
            totalPriceSpan.textContent = totalPrice.toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
        });
    }

    if (reservationForm) {
        reservationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const showId = document.getElementById('showId').value;
            const numberOfSeats = document.getElementById('numberOfSeats').value;
            const messageDiv = document.getElementById('reservationMessage');
            
            try {
                const response = await fetch('/reservations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ showId: parseInt(showId), numberOfSeats: parseInt(numberOfSeats) })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = data.message;
                    setTimeout(() => {
                        window.location.href = '/profile';
                    }, 2000);
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = data.error;
                }
            } catch (error) {
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Erreur lors de la réservation';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>