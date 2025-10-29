<?php
ob_start();
?>

<div class="shows-page">
    <div class="page-header">
        <h1>Nos spectacles</h1>
        <p>Découvrez notre programmation exceptionnelle</p>
    </div>

    <div class="categories-filter">
        <h2>Filtrer par catégorie</h2>
        <div class="category-buttons">
            <a href="/shows" class="btn btn-secondary">Tous</a>
            <a href="/shows/category/théâtre" class="btn btn-secondary">Théâtre</a>
            <a href="/shows/category/concert" class="btn btn-secondary">Concerts</a>
            <a href="/shows/category/opéra" class="btn btn-secondary">Opéra</a>
        </div>
    </div>

    <div class="shows-grid">
        <?php if (empty($shows)): ?>
            <div class="no-shows">
                <p>Aucun spectacle disponible pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($shows as $show): ?>
                <div class="show-card">
                    <?php if ($show->getImageUrl()): ?>
                        <div class="show-image">
                            <img src="<?= htmlspecialchars($show->getImageUrl()) ?>" alt="<?= htmlspecialchars($show->getTitle()) ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="show-content">
                        <div class="show-header">
                            <h3><?= htmlspecialchars($show->getTitle()) ?></h3>
                            <span class="show-category"><?= ucfirst(htmlspecialchars($show->getCategory())) ?></span>
                        </div>
                        
                        <p class="show-description"><?= htmlspecialchars(substr($show->getDescription(), 0, 150)) ?>...</p>
                        
                        <div class="show-details">
                            <div class="show-date">
                                <strong><?= $show->getFormattedDate() ?></strong>
                            </div>
                            <div class="show-venue">
                                <?= htmlspecialchars($show->getVenue()) ?>
                            </div>
                            <div class="show-seats">
                                <?= $show->getAvailableSeats() ?>/<?= $show->getTotalSeats() ?> places disponibles
                            </div>
                        </div>
                        
                        <div class="show-footer">
                            <div class="show-price">
                                <strong><?= $show->getFormattedPrice() ?></strong>
                            </div>
                            <div class="show-actions">
                                <a href="/shows/<?= $show->getId() ?>" class="btn btn-primary">Voir les détails</a>
                                <?php if ($show->isAvailable()): ?>
                                    <?php if (isset($user) && $user): ?>
                                        <button onclick="reserveShow(<?= $show->getId() ?>)" class="btn btn-secondary">Réserver</button>
                                    <?php else: ?>
                                        <a href="/login" class="btn btn-secondary">Se connecter pour réserver</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="btn btn-disabled">Complet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function reserveShow(showId) {
    window.location.href = `/shows/${showId}#reservation`;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>