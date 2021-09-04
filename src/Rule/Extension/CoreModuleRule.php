<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule\Extension;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\RuleInterface;
use Kodzila\ArchValidator\Rule\RuleViolation;

/**
 * The architecture is as follows:
 * - There is one core (centric) module that should not depend on any other module.
 * - Other modules are considered as gravitating around the core. They cannot depend on eachothers but can depend on
 * core.
 */
final class CoreModuleRule implements RuleInterface
{
    public const VIOLATION_CORE_DEPEND_ON_SUBMODULE = 'VIOLATION_CORE_DEPEND_ON_SUBMODULE';
    public const VIOLATION_SUBMODULE_DEPEND_ON_OTHER_SUBMODULE = 'VIOLATION_SUBMODULE_DEPEND_ON_OTHER_SUBMODULE';

    private string $coreModuleName;

    public function __construct(string $coreModuleName)
    {
        $this->coreModuleName = $coreModuleName;
    }

    /**
     * @inheritDoc
     */
    public function check(ModuleCollection $modules): array
    {
        $coreModule = $modules->getByName($this->coreModuleName);
        $subModules = $modules->collectWithoutName($this->coreModuleName);

        return array_merge(
            $this->checkCoreNotDependOnSubmodule($coreModule, $subModules),
            $this->checkSubModuleNotDependOnOtherSubModules($subModules),
        );
    }

    /**
     * @param Module[] $subModules
     * @return RuleViolation[]
     */
    private function checkCoreNotDependOnSubmodule(Module $coreModule, array $subModules): array
    {
        $result = [];

        foreach ($subModules as $subModule) {
            $classes = $coreModule->classesDependingOnNamespace($subModule->getNamespace());

            if (\count($classes) > 0) {
                $result[] = new RuleViolation(sprintf(
                    'Core module %s cannot depend on submodule %s. Faulty core module classes: %s',
                    $this->coreModuleName,
                    $subModule->getName(),
                    implode(',', array_map(fn(ModuleClass $moduleClass) => $moduleClass->getClassName(), $classes)),
                ), self::VIOLATION_CORE_DEPEND_ON_SUBMODULE, \get_class($this));
            }
        }

        return $result;
    }

    /**
     * @param Module[] $subModules
     * @return RuleViolation[]
     */
    private function checkSubModuleNotDependOnOtherSubModules(array $subModules): array
    {
        $result = [];

        foreach ($subModules as $subModule) {
            foreach ($subModules as $otherSubModule) {
                if ($subModule->getName() === $otherSubModule->getName()) {
                    continue;
                }

                $classes = $subModule->classesDependingOnNamespace($otherSubModule->getNamespace());

                if (\count($classes) > 0) {
                    $result[] = new RuleViolation(sprintf(
                        'Sub module %s cannot depend on other submodule %s. Faulty module classes: %s',
                        $subModule->getName(),
                        $otherSubModule->getName(),
                        implode(',', array_map(fn(ModuleClass $moduleClass) => $moduleClass->getClassName(), $classes)),
                    ), self::VIOLATION_SUBMODULE_DEPEND_ON_OTHER_SUBMODULE, \get_class($this));
                }
            }
        }

        return $result;
    }
}
