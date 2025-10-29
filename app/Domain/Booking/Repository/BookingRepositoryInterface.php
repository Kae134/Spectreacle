<?php

declare(strict_types=1);

namespace App\Domain\Booking\Repository;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\ValueObject\BookingId;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Show\ValueObject\ShowId;

interface BookingRepositoryInterface
{
    public function save(Booking $booking): void;
    
    public function findById(BookingId $id): ?Booking;
    
    public function findByUserId(UserId $userId): array;
    
    public function findByShowId(ShowId $showId): array;
    
    public function findAll(): array;
    
    public function exists(BookingId $id): bool;
    
    public function delete(BookingId $id): void;
    
    public function countByShowId(ShowId $showId): int;
}