<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Model;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
    public function testDependencyOnNamespace(): void
    {
        $module = new Module(
            'Core',
            'App\\Core',
            [
                new ModuleClass(
                    'One',
                    'App\\Core',
                    [
                        'Symfony\Component\Finder\Finder',
                    ]
                ),
                new ModuleClass(
                    'Two',
                    'App\\Core',
                    [
                        'App/Sub/SubClass'
                    ]
                )
            ]
        );

        $classes = $module->classesDependingOnNamespace('App/Sub');
        $this->assertCount(1, $classes);

        $classes = $module->classesDependingOnNamespace('App');
        $this->assertCount(1, $classes);

        $classes = $module->classesDependingOnNamespace('ArchValidator');
        $this->assertCount(0, $classes);
    }

    public function testMultipleClassesInNamespace(): void
    {
        $module = new Module(
            'Core',
            'Kodzila\ArchValidator\Tests\Integration\App\Core\Application',
            [
                new ModuleClass(
                    'UserService',
                    'Kodzila\ArchValidator\Tests\Integration\App\Core\Application',
                    [
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\Repository\UserRepository',
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\User',
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Infrastructure\InMemoryUserRepository',
                    ]
                ),
                new ModuleClass(
                    'InMemoryUserRepository',
                    'Kodzila\ArchValidator\Tests\Integration\App\Core\Infrastructure',
                    [
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\Repository\UserRepository',
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\User',
                    ]
                ),
                new ModuleClass(
                    'UserRepository',
                    'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\Repository',
                    [
                        'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain\User',
                    ]
                ),
                new ModuleClass(
                    'User',
                    'Kodzila\ArchValidator\Tests\Integration\App\Core\Domain',
                    []
                ),
            ]
        );

        $result = $module->classesDependingOnNamespace('Kodzila\ArchValidator\Tests\Integration\App\Core');
        $this->assertCount(3, $result);
    }

    public function testModuleNamespaceEndsWithSlashes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Module(
            'Core',
            'App\\Core\\',
            []
        );
    }
}
