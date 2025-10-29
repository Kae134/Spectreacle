<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Api\V1;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Infrastructure\Http\Attribute\IsGranted;
use App\Application\User\Command\CreateUserCommand;
use App\Application\User\Query\GetUserQuery;
use App\Application\User\Handler\CreateUserHandler;
use App\Application\User\Handler\GetUserHandler;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserService;

final class UserController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserService $userService
    ) {
    }
    
    public function create(Request $request): Response
    {
        try {
            $data = $request->isJson() 
                ? $request->getJsonBody()
                : [
                    'username' => $request->getPost('username'),
                    'email' => $request->getPost('email'),
                    'password' => $request->getPost('password'),
                    'role' => $request->getPost('role'),
                ];
            
            $command = new CreateUserCommand(
                username: $data['username'] ?? '',
                email: $data['email'] ?? '',
                password: $data['password'] ?? '',
                role: $data['role'] ?? null
            );
            
            $handler = new CreateUserHandler($this->userRepository, $this->userService);
            $response = $handler->handle($command);
            
            return JsonResponse::success($response->toArray(), 201);
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 400);
        } catch (\InvalidArgumentException $e) {
            return JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred while creating user', 500);
        }
    }
    
    #[IsGranted('ROLE_USER')]
    public function show(Request $request, string $id): Response
    {
        try {
            $query = new GetUserQuery(userId: $id);
            $handler = new GetUserHandler($this->userRepository);
            $response = $handler->handle($query);
            
            return JsonResponse::success($response->toArray());
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred', 500);
        }
    }
    
    #[IsGranted('ROLE_USER')]
    public function me(Request $request): Response
    {
        $user = $_SESSION['user'] ?? null;
        
        if ($user === null) {
            return JsonResponse::error('User not authenticated', 401);
        }
        
        try {
            $query = new GetUserQuery(userId: $user['user_id']);
            $handler = new GetUserHandler($this->userRepository);
            $response = $handler->handle($query);
            
            return JsonResponse::success($response->toArray());
            
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred', 500);
        }
    }
}
