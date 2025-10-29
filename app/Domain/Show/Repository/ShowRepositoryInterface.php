<?php

declare(strict_types=1);

namespace App\Domain\Show\Repository;

use App\Domain\Show\Entity\Show;
use App\Domain\Show\ValueObject\ShowId;

interface ShowRepositoryInterface
{
    public function save(Show $show): void;
    
    public function findById(ShowId $id): ?Show;
    
    public function findAll(): array;
    
    public function findUpcoming(): array;
    
    public function exists(ShowId $id): bool;
    
    public function delete(ShowId $id): void;
    
    public function count(): int;
}