<?php

declare(strict_types=1);

namespace App\Application\Show\Query;

final class ListShowsQuery
{
    public function __construct(
        public readonly bool $upcomingOnly = false
    ) {
    }
}