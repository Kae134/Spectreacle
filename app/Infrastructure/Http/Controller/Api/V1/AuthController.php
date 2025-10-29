<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Api\V1;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Handler\LoginHandler;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Security\JWTManager;

final class AuthController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JWTManager $jwtManager
    ) {
    }
    
    public function login(Request $request): Response
    {
        try {
            $data = $request->isJson() 
                ? $request->getJsonBody()
                : [
                    'username' => $request->getPost('username'),
                    'password' => $request->getPost('password'),
                ];
            
            if (empty($data['username']) || empty($data['password'])) {
                return JsonResponse::error('Username and password are required', 400);
            }
            
            $command = new LoginCommand(
                username: $data['username'],
                password: $data['password']
            );
            
            $handler = new LoginHandler($this->userRepository, $this->jwtManager);
            $response = $handler->handle($command);
            
            // Créer le cookie JWT
            $jsonResponse = JsonResponse::success($response->toArray());
            $jsonResponse->setCookie(
                name: 'jwt_token',
                value: $response->token,
                expires: time() + 300, // 5 minutes
                httpOnly: true
            );
            
            return $jsonResponse;
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred during authentication', 500);
        }
    }
    
    public function logout(Request $request): Response
    {
        return (new Response())
            ->setCookie('jwt_token', '', time() - 3600)
            ->redirect('/');
    }
    
    public function showLoginForm(Request $request): Response
    {
        // Cette méthode sera utilisée pour afficher le formulaire HTML
        return new Response('Login form will be rendered here');
    }
}