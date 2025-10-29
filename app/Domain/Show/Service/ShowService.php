<?php

declare(strict_types=1);

namespace App\Domain\Show\Service;

use App\Domain\Show\Entity\Show;
use App\Domain\Show\Repository\ShowRepositoryInterface;
use App\Domain\Show\ValueObject\ShowId;
use DomainException;

final class ShowService
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository
    ) {
    }
    
    public function ensureShowExists(ShowId $showId): Show
    {
        $show = $this->showRepository->findById($showId);
        
        if ($show === null) {
            throw new DomainException("Show with ID {$showId->toInt()} not found");
        }
        
        return $show;
    }
    
    public function ensureShowIsBookable(Show $show, int $numberOfSeats = 1): void
    {
        if ($show->isPast()) {
            throw new DomainException('Cannot book seats for past shows');
        }
        
        if (!$show->canBook($numberOfSeats)) {
            throw new DomainException('Not enough seats available for this show');
        }
    }
}