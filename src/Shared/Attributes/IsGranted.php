<?php

declare(strict_types=1);

namespace Spectreacle\Shared\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class IsGranted
{
    public function __construct(
        public readonly ?string $role = null,
        public readonly bool $requireAuth = true
    ) {}
}