<?php
ob_start();
?>

<div class="error-page">
    <h1>404 - Page non trouvée</h1>
    <p>Désolé, la page que vous recherchez n'existe pas.</p>
    <div class="error-actions">
        <a href="/" class="btn btn-primary">Retour à l'accueil</a>
        <a href="/shows" class="btn btn-secondary">Voir nos spectacles</a>
    </div>
</div>

<style>
.error-page {
    text-align: center;
    padding: 4rem 2rem;
    max-width: 600px;
    margin: 0 auto;
}

.error-page h1 {
    color: #e74c3c;
    margin-bottom: 1rem;
}

.error-actions {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>