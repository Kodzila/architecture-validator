<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\GoodApp\Application;

use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\Repository\UserRepository;
use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\User;

final class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        $this->repository->add(new User());
    }
}
