<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Api\V1;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Infrastructure\Http\Attribute\IsGranted;
use App\Application\Show\Command\CreateShowCommand;
use App\Application\Show\Query\ListShowsQuery;
use App\Application\Show\Handler\CreateShowHandler;
use App\Application\Show\Handler\ListShowsHandler;
use App\Application\Show\Handler\GetShowHandler;
use App\Application\Show\Query\GetShowQuery;
use App\Domain\Show\Repository\ShowRepositoryInterface;

final class ShowController
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository
    ) {
    }
    
    public function list(Request $request): Response
    {
        try {
            $upcomingOnly = $request->getQuery('upcoming') === 'true';
            
            $query = new ListShowsQuery(upcomingOnly: $upcomingOnly);
            $handler = new ListShowsHandler($this->showRepository);
            $shows = $handler->handle($query);
            
            $data = array_map(fn($show) => $show->toArray(), $shows);
            
            return JsonResponse::success($data);
            
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred while fetching shows', 500);
        }
    }
    
    public function show(Request $request, string $id): Response
    {
        try {
            $query = new GetShowQuery(showId: (int) $id);
            $handler = new GetShowHandler($this->showRepository);
            $response = $handler->handle($query);
            
            return JsonResponse::success($response->toArray());
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred', 500);
        }
    }
    
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        try {
            $data = $request->isJson() 
                ? $request->getJsonBody()
                : [
                    'title' => $request->getPost('title'),
                    'description' => $request->getPost('description'),
                    'date' => $request->getPost('date'),
                    'location' => $request->getPost('location'),
                    'price' => $request->getPost('price'),
                    'available_seats' => $request->getPost('available_seats'),
                    'image_url' => $request->getPost('image_url', ''),
                ];
            
            $command = new CreateShowCommand(
                title: $data['title'] ?? '',
                description: $data['description'] ?? '',
                date: $data['date'] ?? '',
                location: $data['location'] ?? '',
                price: (float) ($data['price'] ?? 0),
                availableSeats: (int) ($data['available_seats'] ?? 0),
                imageUrl: $data['image_url'] ?? ''
            );
            
            $handler = new CreateShowHandler($this->showRepository);
            $response = $handler->handle($command);
            
            return JsonResponse::success($response->toArray(), 201);
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred while creating show', 500);
        }
    }
}