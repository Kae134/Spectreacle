<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Spectreacle\Infrastructure\Container\Container;
use Spectreacle\Infrastructure\Http\Router\Router;
use Spectreacle\Infrastructure\Http\Controllers\HomeController;
use Spectreacle\Infrastructure\Http\Controllers\AuthController;
use Spectreacle\Infrastructure\Http\Controllers\ProtectedController;
use Spectreacle\Infrastructure\Http\Controllers\ShowController;
use Spectreacle\Infrastructure\Http\Controllers\ReservationController;
use Spectreacle\Infrastructure\Http\Controllers\ProfileController;
use Spectreacle\Infrastructure\Database\FileUserRepository;
use Spectreacle\Infrastructure\Database\InMemoryShowRepository;
use Spectreacle\Infrastructure\Database\FileReservationRepository;
use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Auth\Services\JwtService;
use Spectreacle\Domain\Auth\Services\TotpService;

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

// Configuration JWT
$jwtSecret = $_ENV['JWT_SECRET'] ?? 'super-secret-key-for-development-spectreacle-2024';

// Container de dépendances
$container = new Container();

// Configuration des services
$container->set('user_repository', function() {
    return new FileUserRepository();
});

$container->set('show_repository', function() {
    return new InMemoryShowRepository();
});

$container->set('reservation_repository', function() {
    return new FileReservationRepository();
});

$container->set('jwt_service', function() use ($jwtSecret) {
    return new JwtService($jwtSecret);
});

$container->set('totp_service', function() {
    return new TotpService();
});

$container->set('auth_service', function($container) {
    return new AuthenticationService(
        $container->get('user_repository'),
        $container->get('jwt_service'),
        $container->get('totp_service')
    );
});

$container->set(HomeController::class, function($container) {
    return new HomeController($container->get('auth_service'));
});

$container->set(AuthController::class, function($container) {
    return new AuthController($container->get('auth_service'), $container->get('totp_service'));
});

$container->set(ProtectedController::class, function($container) {
    return new ProtectedController($container->get('auth_service'));
});

$container->set(ShowController::class, function($container) {
    return new ShowController(
        $container->get('show_repository'),
        $container->get('auth_service')
    );
});

$container->set(ReservationController::class, function($container) {
    return new ReservationController(
        $container->get('show_repository'),
        $container->get('reservation_repository'),
        $container->get('auth_service')
    );
});

$container->set(ProfileController::class, function($container) {
    return new ProfileController(
        $container->get('show_repository'),
        $container->get('reservation_repository'),
        $container->get('auth_service')
    );
});


// Configuration du routeur
$router = new Router($container);

// Routes publiques
$router->get('/', HomeController::class, 'index');
$router->get('/login', HomeController::class, 'showLoginForm');
$router->get('/register', HomeController::class, 'showRegisterForm');
$router->get('/totp-setup', HomeController::class, 'showTotpSetup');

// Routes d'authentification
$router->post('/auth/login', AuthController::class, 'login');
$router->post('/auth/register', AuthController::class, 'register');
$router->post('/auth/logout', AuthController::class, 'logout');

// Routes TOTP
$router->post('/auth/totp/setup', AuthController::class, 'setupTotp');
$router->post('/auth/totp/enable', AuthController::class, 'enableTotp');
$router->post('/auth/totp/disable', AuthController::class, 'disableTotp');

// Routes des spectacles
$router->get('/shows', ShowController::class, 'index');
$router->get('/shows/category/{category}', ShowController::class, 'category');
$router->get('/shows/{id}', ShowController::class, 'show');

// Routes de réservation
$router->post('/reservations', ReservationController::class, 'create');

// Routes du profil utilisateur
$router->get('/profile', ProfileController::class, 'index');

// Routes protégées (dashboard pour compatibilité)
$router->get('/dashboard', ProtectedController::class, 'dashboard');


// Routes de santé
$router->get('/health', HomeController::class, 'health');

// Gestion des assets statiques
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    }
}

// Dispatcher la requête
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    http_response_code(500);
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
        echo "<h1>Erreur 500</h1><p>Erreur interne du serveur: " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}