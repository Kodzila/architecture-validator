<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Infrastructure;

use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\Repository\UserRepository;
use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\User;

final class InMemoryUserRepository implements UserRepository
{
    private array $memory = [];

    public function add(User $user): void
    {
        $this->memory[] = $user;
    }

    public function getFirst(): User
    {
        return $this->memory[0];
    }
}
