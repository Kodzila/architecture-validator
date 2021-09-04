<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Application;

use Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Domain\Repository\UserRepository;
use Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Domain\User;
use Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Infrastructure\InMemoryUserRepository;
use Kodzila\ArchValidator\Tests\Integration\BadApp\Email\EmailService;

final class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        new InMemoryUserRepository();
        new EmailService($this->repository);
        $this->repository->add(new User());
    }
}
