<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\Middleware\MiddlewareInterface;
use ReflectionMethod;
use ReflectionAttribute;
use App\Infrastructure\Http\Attribute\IsGranted;

final class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    
    public function addGlobalMiddleware(MiddlewareInterface $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }
    
    public function get(string $path, string|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, string|callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, string|callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete(string $path, string|callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    private function addRoute(
        string $method,
        string $path,
        string|callable $handler,
        array $middleware
    ): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }
    
    public function dispatch(Request $request): Response
    {
        // Appliquer les middlewares globaux
        foreach ($this->globalMiddleware as $middleware) {
            $result = $middleware->handle($request);
            if ($result instanceof Response) {
                return $result;
            }
        }
        
        $requestMethod = $request->getMethod();
        $requestUri = $request->getUri();
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            $pattern = $this->convertPathToRegex($route['path']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                
                // Appliquer les middlewares de la route
                foreach ($route['middleware'] as $middleware) {
                    $result = $middleware->handle($request);
                    if ($result instanceof Response) {
                        return $result;
                    }
                }
                
                return $this->callHandler($route['handler'], $matches, $request);
            }
        }
        
        return new JsonResponse(['error' => 'Route not found'], 404);
    }
    
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function callHandler(string|callable $handler, array $params, Request $request): Response
    {
        // Si c'est une closure/callable, l'appeler directement
        if (is_callable($handler)) {
            array_unshift($params, $request);
            $result = call_user_func_array($handler, $params);
            return $result instanceof Response ? $result : new Response((string) $result);
        }
        
        // Sinon, c'est un string au format "Controller@method"
        [$controller, $method] = explode('@', $handler);
        
        if (!class_exists($controller)) {
            return new JsonResponse(['error' => 'Controller not found'], 500);
        }
        
        $controllerInstance = new $controller();
        
        if (!method_exists($controllerInstance, $method)) {
            return new JsonResponse(['error' => 'Method not found'], 500);
        }
        
        // Vérifier les attributs IsGranted
        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(IsGranted::class);
        
        foreach ($attributes as $attribute) {
            $isGranted = $attribute->newInstance();
            $checkResult = $isGranted->check($request);
            
            if ($checkResult instanceof Response) {
                return $checkResult;
            }
        }
        
        // Ajouter la requête comme premier paramètre
        array_unshift($params, $request);
        
        $result = call_user_func_array([$controllerInstance, $method], $params);
        
        return $result instanceof Response ? $result : new Response((string) $result);
    }
}