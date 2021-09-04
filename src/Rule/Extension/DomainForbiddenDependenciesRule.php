<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule\Extension;

use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\RuleInterface;
use Kodzila\ArchValidator\Rule\RuleViolation;
use Webmozart\Assert\Assert;

final class DomainForbiddenDependenciesRule implements RuleInterface
{
    public const VIOLATION_FORBIDDEN_DEPENDENCY_IN_DOMAIN_LAYER = 'VIOLATION_FORBIDDEN_DEPENDENCY_IN_DOMAIN_LAYER';

    /**
     * @var string[]
     */
    private array $dddModules;

    /**
     * @var string[]
     */
    private array $whiteListedNamespaces;

    public function __construct(array $dddModules, array $whiteListedNamespaces)
    {
        Assert::allString($dddModules);
        Assert::allString($whiteListedNamespaces);

        $this->dddModules = $dddModules;
        $this->whiteListedNamespaces = $whiteListedNamespaces;
    }

    /**
     * @param ModuleCollection $modules
     * @return RuleViolation[]
     */
    public function check(ModuleCollection $modules): array
    {
        $violations = [];

        $dddModules = $modules->collectWithNames($this->dddModules);

        foreach ($dddModules as $dddModule) {
            $moduleNamespace = $dddModule->getNamespace();
            $domainNamespace = $moduleNamespace . '\Domain';
            $domainClasses = $dddModule->classFromNamespace($domainNamespace);

            foreach ($domainClasses as $domainClass) {
                $outOfModuleUsages = $domainClass->collectUsagesNotStartingWith($moduleNamespace);
                $outOfModuleUsages = $this->filterOutWhitelisted($outOfModuleUsages, $this->whiteListedNamespaces);

                if (\count($outOfModuleUsages) > 0) {
                    $violations[] = new RuleViolation(
                        sprintf(
                            'DDD module %s domain class %s has forbidden dependencies: %s.',
                            $dddModule->getName(),
                            $domainClass->getClassName(),
                            implode(', ', $outOfModuleUsages),
                        ),
                        self::VIOLATION_FORBIDDEN_DEPENDENCY_IN_DOMAIN_LAYER,
                        \get_class($this),
                    );
                }
            }
        }

        return $violations;
    }

    /**
     * @param string[] $usages
     * @param string[] $whitelist
     * @return string[]
     */
    private function filterOutWhitelisted(array $usages, array $whitelist): array
    {
        $result = [];

        foreach ($usages as $usage) {
            foreach ($whitelist as $whitelistEntry) {
                if (strpos($usage, $whitelistEntry) === 0) {
                    continue 2;
                }
            }

            $result[] = $usage;
        }

        return $result;
    }
}
