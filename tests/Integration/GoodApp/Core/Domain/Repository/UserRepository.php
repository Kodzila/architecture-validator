<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\Repository;

use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\User;

interface UserRepository
{
    public function add(User $user): void;

    public function getFirst(): User;
}
