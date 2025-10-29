<?php

declare(strict_types=1);

namespace App\Application\Show\Handler;

use App\Application\Show\Command\CreateShowCommand;
use App\Application\Show\DTO\ShowResponse;
use App\Domain\Show\Entity\Show;
use App\Domain\Show\Repository\ShowRepositoryInterface;
use App\Domain\Show\ValueObject\ShowDate;
use App\Domain\Show\ValueObject\Price;
use App\Domain\Show\ValueObject\SeatsAvailability;

final class CreateShowHandler
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository
    ) {
    }
    
    public function handle(CreateShowCommand $command): ShowResponse
    {
        $show = Show::create(
            title: $command->title,
            description: $command->description,
            date: ShowDate::fromString($command->date),
            location: $command->location,
            price: Price::fromFloat($command->price),
            availableSeats: SeatsAvailability::fromInt($command->availableSeats),
            imageUrl: $command->imageUrl
        );
        
        $this->showRepository->save($show);
        
        return ShowResponse::fromShow($show);
    }
}