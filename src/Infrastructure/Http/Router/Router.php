<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Router;

use ReflectionClass;
use ReflectionMethod;
use Spectreacle\Infrastructure\Container\Container;
use Spectreacle\Shared\Attributes\IsGranted;

class Router
{
    private array $routes = [];

    public function __construct(
        private Container $container
    ) {}

    public function get(string $path, string $controller, string $method): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    private function addRoute(string $httpMethod, string $path, string $controller, string $method): void
    {
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $matches = $this->matchRoute($route['path'], $path);
                if ($matches !== null) {
                    $this->executeRoute($route, $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        if ($requestMethod === 'GET' && !str_starts_with($requestUri, '/api/')) {
            echo "<h1>Erreur 404</h1><p>Page non trouvée</p>";
        } else {
            echo json_encode(['error' => 'Route not found']);
        }
    }

    private function matchRoute(string $routePath, string $requestPath): ?array
    {
        // Simple route matching with {param} syntax
        $routeRegex = preg_replace('/\{(\w+)\}/', '(\w+)', $routePath);
        $routeRegex = '#^' . $routeRegex . '$#';
        
        if (preg_match($routeRegex, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            return $matches;
        }
        
        return null;
    }

    private function executeRoute(array $route, array $params = []): void
    {
        $controller = $this->container->get($route['controller']);
        $method = $route['action'];

        // Vérifier les attributs de sécurité
        $this->checkSecurity($route['controller'], $method);

        // Appeler la méthode avec les paramètres
        if (empty($params)) {
            $controller->$method();
        } else {
            $controller->$method(...$params);
        }
    }

    private function checkSecurity(string $controllerClass, string $method): void
    {
        $reflection = new ReflectionClass($controllerClass);
        $methodReflection = new ReflectionMethod($controllerClass, $method);
        
        $attributes = $methodReflection->getAttributes(IsGranted::class);
        
        if (empty($attributes)) {
            return;
        }

        $isGranted = $attributes[0]->newInstance();
        
        if ($isGranted->requireAuth) {
            $authService = $this->container->get('auth_service');
            $token = $this->getTokenFromCookie();
            
            if (!$token || !$authService->validateToken($token)) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }

            if ($isGranted->role) {
                $user = $authService->getUserFromToken($token);
                if (!$user || !$user->hasRole($isGranted->role)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Insufficient permissions']);
                    exit;
                }
            }
        }
    }

    private function getTokenFromCookie(): ?string
    {
        return $_COOKIE['jwt_token'] ?? null;
    }
}