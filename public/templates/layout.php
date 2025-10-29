<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Spectreacle' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="nav-brand">Spectreacle</a>
            <ul class="nav-menu">
                <li><a href="/">Accueil</a></li>
                <li><a href="/shows">Spectacles</a></li>
                <?php if (isset($user) && $user): ?>
                    <li><a href="/profile">Mon profil</a></li>
                    <?php if ($user->isAdmin()): ?>
                        <li><a href="/admin">Administration</a></li>
                    <?php endif; ?>
                    <li><span>Bonjour, <?= htmlspecialchars($user->getUsername()) ?></span></li>
                    <li><button onclick="logout()" class="btn-logout">Déconnexion</button></li>
                <?php else: ?>
                    <li><a href="/login">Connexion</a></li>
                    <li><a href="/register">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <?= $content ?>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>