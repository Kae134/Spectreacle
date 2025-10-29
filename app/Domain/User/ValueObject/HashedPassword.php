<?php 

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final class HashedPassword
{
    private function __construct(private readonly string $hash)
    {
        if (empty($hash)) {
            throw new InvalidArgumentException('Password hash cannot be empty');
        }
    }
    
    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters');
        }
        
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        return new self($hash);
    }
    
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }
    
    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }
    
    public function toString(): string
    {
        return $this->hash;
    }
    
    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, PASSWORD_DEFAULT);
    }
}