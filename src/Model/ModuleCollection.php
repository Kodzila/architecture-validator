<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Model;

use Webmozart\Assert\Assert;

final class ModuleCollection
{
    /**
     * @var Module[]
     */
    private array $modules;

    public function __construct(array $modules = [])
    {
        Assert::allIsInstanceOf($modules, Module::class);
        $this->modules = $modules;
    }

    public function add(Module $module): void
    {
        $this->modules[] = $module;
    }

    public function getByName(string $name): Module
    {
        foreach ($this->modules as $module) {
            if ($module->getName() === $name) {
                return $module;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Module with name %s not found',
            $name,
        ));
    }

    /**
     * @return Module[]
     */
    public function collectWithoutName(string $name): array
    {
        $result = [];

        foreach ($this->modules as $module) {
            if ($module->getName() !== $name) {
                $result[] = $module;
            }
        }

        return $result;
    }

    /**
     * @param string[] $names
     * @return Module[]
     */
    public function collectWithNames(array $names): array
    {
        $result = [];

        foreach ($this->modules as $module) {
            if (\in_array($module->getName(), $names, true)) {
                $result[] = $module;
            }
        }

        return $result;
    }
}
