<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Auth\Repositories;

use DateTimeImmutable;

interface OtpChallengeRepositoryInterface
{
    public function create(array $data): string; // retourne challenge_id
    public function find(string $challengeId): ?array;
    public function update(string $challengeId, array $patch): void;
    public function delete(string $challengeId): void;

    // Pour rate limit
    public function countRecentForUser(int $userId, string $purpose, int $sinceEpoch): int;
}
