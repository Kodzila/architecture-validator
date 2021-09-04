<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\BadApp\Email;

use Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Domain\Repository\UserRepository;
use Kodzila\ArchValidator\Tests\Integration\BadApp\File\FileService;

final class EmailService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function send(): void
    {
        new FileService();
        $user = $this->repository->getFirst();
    }

}
