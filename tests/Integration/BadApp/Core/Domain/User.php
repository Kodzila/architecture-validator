<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Domain;

use Kodzila\ArchValidator\Tests\Integration\BadApp\Core\Infrastructure\InMemoryUserRepository;
use Symfony\Component\Finder\Finder;

final class User
{
    public function foo(): void
    {
        new Finder();
        new InMemoryUserRepository();
    }
}
