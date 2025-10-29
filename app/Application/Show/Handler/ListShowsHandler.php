<?php

declare(strict_types=1);

namespace App\Application\Show\Handler;

use App\Application\Show\Query\ListShowsQuery;
use App\Application\Show\DTO\ShowResponse;
use App\Domain\Show\Repository\ShowRepositoryInterface;

final class ListShowsHandler
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository
    ) {
    }
    
    public function handle(ListShowsQuery $query): array
    {
        $shows = $query->upcomingOnly
            ? $this->showRepository->findUpcoming()
            : $this->showRepository->findAll();
        
        return array_map(
            fn($show) => ShowResponse::fromShow($show),
            $shows
        );
    }
}