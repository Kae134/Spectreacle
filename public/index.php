<?php
// public/index.php
declare(strict_types=1);

use App\Infrastructure\Http\Router;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Middleware\CorsMiddleware;
use App\Infrastructure\Http\Controller\Api\V1\AuthController;
use App\Infrastructure\Http\Controller\Api\V1\UserController;
use App\Infrastructure\Http\Controller\Api\V1\ShowController;
use App\Infrastructure\Http\Controller\Api\V1\BookingController;
use App\Infrastructure\Persistence\PDO\UserRepository;
use App\Infrastructure\Persistence\PDO\ShowRepository;
use App\Infrastructure\Persistence\PDO\BookingRepository;
use App\Domain\User\Service\UserService;
use App\Domain\Show\Service\ShowService;
use App\Domain\Show\ValueObject\ShowId;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Booking\Service\BookingService;
use App\Infrastructure\Security\JWTManager;

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Session
session_start();

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Container simple (Dependency Injection)
$userRepository = new UserRepository();
$showRepository = new ShowRepository();
$bookingRepository = new BookingRepository();

$userService = new UserService($userRepository);
$showService = new ShowService($showRepository);
$bookingService = new BookingService(
    $bookingRepository,
    $showRepository,
    $userRepository,
    $showService
);

$jwtManager = new JWTManager();

$authController = new AuthController($userRepository, $jwtManager);
$userController = new UserController($userRepository, $userService);
$showController = new ShowController($showRepository);
$bookingController = new BookingController($bookingService);

// Router
$router = new Router();

// Middleware global
$router->addGlobalMiddleware(new CorsMiddleware());

// Routes API V1
// Auth routes
$router->post('/api/v1/auth/login', function($req) use ($authController) {
    return $authController->login($req);
});
$router->get('/api/v1/auth/logout', function($req) use ($authController) {
    return $authController->logout($req);
});

// User routes
$router->post('/api/v1/users', function($req) use ($userController) {
    return $userController->create($req);
});
$router->get('/api/v1/users/me', function($req) use ($userController) {
    return $userController->me($req);
});
$router->get('/api/v1/users/{id}', function($req, $id) use ($userController) {
    return $userController->show($req, $id);
});

// Show routes
$router->get('/api/v1/shows', function($req) use ($showController) {
    return $showController->list($req);
});
$router->get('/api/v1/shows/{id}', function($req, $id) use ($showController) {
    return $showController->show($req, $id);
});
$router->post('/api/v1/shows', function($req) use ($showController) {
    return $showController->create($req);
});

// Booking routes
$router->post('/api/v1/bookings', function($req) use ($bookingController) {
    return $bookingController->create($req);
});
$router->get('/api/v1/bookings/my', function($req) use ($bookingController) {
    return $bookingController->myBookings($req);
});

// Routes Web (pages HTML)
$router->get('/', function($req) {
    // Page d'accueil
    $user = $_SESSION['user'] ?? null;
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/home.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

$router->get('/login', function($req) use ($authController) {
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/login.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

$router->post('/login', function($req) use ($authController) {
    return $authController->login($req);
});

$router->get('/logout', function($req) use ($authController) {
    return $authController->logout($req);
});

$router->get('/shows', function($req) use ($showRepository) {
    $shows = $showRepository->findAll();
    $user = $_SESSION['user'] ?? null;
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/shows/list.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

$router->get('/shows/{id}', function($req, $id) use ($showRepository) {
    $show = $showRepository->findById(ShowId::fromInt((int)$id));
    $user = $_SESSION['user'] ?? null;
    
    if ($show === null) {
        return new Response('Show not found', 404);
    }
    
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/shows/detail.php';
    $content = ob_get_clean();
    return new Response($content);
});

$router->get('/booking/{showId}', function($req, $showId) use ($bookingService, $showRepository) {
    $user = $_SESSION['user'] ?? null;
    
    if ($user === null) {
        return (new \App\Infrastructure\Http\Response())->redirect('/login');
    }
    
    try {
        $booking = $bookingService->createBooking(
            \App\Domain\User\ValueObject\UserId::fromString($user['user_id']),
            \App\Domain\Show\ValueObject\ShowId::fromInt((int)$showId)
        );
        
        $show = $showRepository->findById(\App\Domain\Show\ValueObject\ShowId::fromInt((int)$showId));
        $success = true;
        
        ob_start();
        require __DIR__ . '/../src/Infrastructure/View/templates/booking/confirm.php';
        $content = ob_get_clean();
        return new \App\Infrastructure\Http\Response($content);
        
    } catch (\Exception $e) {
        $success = false;
        $message = $e->getMessage();
        
        ob_start();
        require __DIR__ . '/../src/Infrastructure/View/templates/booking/confirm.php';
        $content = ob_get_clean();
        return new \App\Infrastructure\Http\Response($content);
    }
});

$router->get('/profile', function($req) use ($bookingService, $showRepository) {
    $user = $_SESSION['user'] ?? null;
    
    if ($user === null) {
        return (new \App\Infrastructure\Http\Response())->redirect('/login');
    }
    
    $bookings = $bookingService->getUserBookingsWithShows(
        \App\Domain\User\ValueObject\UserId::fromString($user['user_id'])
    );
    
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/profile/index.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

$router->get('/admin/shows/add', function($req) {
    $user = $_SESSION['user'] ?? null;
    
    if ($user === null || $user['role'] !== 'ROLE_ADMIN') {
        return (new \App\Infrastructure\Http\Response())->setStatusCode(403)->setContent('Access forbidden');
    }
    
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/admin/add_show.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

$router->post('/admin/shows/add', function($req) use ($showController) {
    $user = $_SESSION['user'] ?? null;
    
    if ($user === null || $user['role'] !== 'ROLE_ADMIN') {
        return (new \App\Infrastructure\Http\Response())->setStatusCode(403)->setContent('Access forbidden');
    }
    
    $response = $showController->create($req);
    
    // Rediriger vers la liste des spectacles après création
    if ($response instanceof \App\Infrastructure\Http\JsonResponse) {
        return (new \App\Infrastructure\Http\Response())->redirect('/shows');
    }
    
    return $response;
});

// Page protégée pour tester JWT (Exercice 2)
$router->get('/protected', function($req) {
    $user = $_SESSION['user'] ?? null;
    
    if ($user === null) {
        return (new \App\Infrastructure\Http\Response())->redirect('/login');
    }
    
    ob_start();
    require __DIR__ . '/../src/Infrastructure/View/templates/protected.php';
    $content = ob_get_clean();
    return new \App\Infrastructure\Http\Response($content);
});

// Dispatch
$request = Request::createFromGlobals();
$response = $router->dispatch($request);
$response->send();