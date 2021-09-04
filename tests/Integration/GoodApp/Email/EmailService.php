<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\GoodApp\Email;

use Kodzila\ArchValidator\Tests\Integration\GoodApp\Core\Domain\Repository\UserRepository;

final class EmailService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function send(): void
    {
        $user = $this->repository->getFirst();
    }

}
