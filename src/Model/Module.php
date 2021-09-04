<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Model;

use Symfony\Component\Finder\Finder;

final class Module
{
    private string $name;
    private string $namespace;

    /**
     * @var ModuleClass[]
     */
    private array $moduleClasses;

    public function __construct(string $name, string $namespace, array $moduleClasses = [])
    {
        self::assertNotEndsWithSlashes($namespace);
        $this->name = $name;
        $this->namespace = $namespace;
        $this->moduleClasses = $moduleClasses;
    }

    public static function fromFileSystem(string $name, string $namespace, string $path): self
    {
        $moduleClasses = self::resolveModuleClasses($path);
        return new self(
            $name,
            $namespace,
            $moduleClasses,
        );
    }

    private static function assertNotEndsWithSlashes(string $value): void
    {
        if (self::endsWith($value, '\\')) {
            throw new \InvalidArgumentException(sprintf(
                'Namespace %s cannot ends with backslashes',
                $value,
            ));
        }

        if (self::endsWith($value, '/')) {
            throw new \InvalidArgumentException(sprintf(
                'Namespace %s cannot ends with slashes',
                $value,
            ));
        }
    }

    private static function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * @return ModuleClass[]
     */
    private static function resolveModuleClasses(string $path): array
    {
        $finder = new Finder();
        $phpFiles = $finder->in($path)->name('*.php');

        $moduleClasses = [];

        foreach ($phpFiles as $phpFile) {
            $moduleClasses[] = ModuleClass::fromSpl($phpFile);
        }

        return $moduleClasses;
    }

    /**
     * @return ModuleClass[]
     */
    public function getClasses(): array
    {
        return $this->moduleClasses;
    }

    /**
     * @return ModuleClass[]
     */
    public function classesDependingOnNamespace(string $namespace): array
    {
        $result = [];

        foreach ($this->moduleClasses as $moduleClass) {
            foreach ($moduleClass->getUsedNamespaces() as $usedNamespace) {
                if (strpos($usedNamespace, $namespace) === 0) {
                    $result[] = $moduleClass;
                    continue 2;
                }
            }
        }

        return $result;
    }

    /**
     * @return ModuleClass[]
     */
    public function classFromNamespace(string $namespace): array
    {
        $result = [];

        foreach ($this->moduleClasses as $moduleClass) {
            if (strpos($moduleClass->getNamespace(), $namespace) === 0) {
                $result[] = $moduleClass;
            }
        }

        return $result;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
