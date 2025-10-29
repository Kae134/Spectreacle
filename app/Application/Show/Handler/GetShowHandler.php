<?php

declare(strict_types=1);

namespace App\Application\Show\Handler;

use App\Application\Show\Query\GetShowQuery;
use App\Application\Show\DTO\ShowResponse;
use App\Domain\Show\Repository\ShowRepositoryInterface;
use App\Domain\Show\ValueObject\ShowId;
use DomainException;

final class GetShowHandler
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository
    ) {
    }
    
    public function handle(GetShowQuery $query): ShowResponse
    {
        $showId = ShowId::fromInt($query->showId);
        $show = $this->showRepository->findById($showId);
        
        if ($show === null) {
            throw new DomainException('Show not found');
        }
        
        return ShowResponse::fromShow($show);
    }
}