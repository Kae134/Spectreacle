<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Auth\Repositories;

use Spectreacle\Domain\Auth\Repositories\OtpChallengeRepositoryInterface;

class InMemoryOtpChallengeRepository implements OtpChallengeRepositoryInterface
{
    /** @var array<string,array> */
    private array $store = [];

    public function create(array $data): string
    {
        $id = bin2hex(random_bytes(16));
        $this->store[$id] = $data;
        return $id;
    }

    public function find(string $challengeId): ?array
    {
        return $this->store[$challengeId] ?? null;
    }

    public function update(string $challengeId, array $patch): void
    {
        if (!isset($this->store[$challengeId])) return;
        $this->store[$challengeId] = array_merge($this->store[$challengeId], $patch);
    }

    public function delete(string $challengeId): void
    {
        unset($this->store[$challengeId]);
    }

    public function countRecentForUser(int $userId, string $purpose, int $sinceEpoch): int
    {
        $n = 0;
        foreach ($this->store as $c) {
            if (($c['user_id'] ?? null) === $userId
                && ($c['purpose'] ?? '') === $purpose
                && ($c['created_at'] ?? 0) >= $sinceEpoch) {
                $n++;
            }
        }
        return $n;
    }
}