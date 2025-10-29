<?php

declare(strict_types=1);

namespace App\Application\Show\Query;

final class GetShowQuery
{
    public function __construct(
        public readonly int $showId
    ) {
    }
}