<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule\Extension;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Model\NamespacesCollection;
use Kodzila\ArchValidator\Rule\RuleInterface;
use Kodzila\ArchValidator\Rule\RuleViolation;
use Webmozart\Assert\Assert;

final class DomainDrivenDesignRule implements RuleInterface
{
    public const VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN = 'VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN';
    public const VIOLATION_APPLICATION_DEPEND_ON_NOT_DOMAIN_APPLICATION
        = 'VIOLATION_APPLICATION_DEPEND_ON_NOT_DOMAIN_APPLICATION';

    /**
     * @var string[]
     */
    private array $dddModules;

    public function __construct(array $dddModules)
    {
        Assert::allString($dddModules);
        $this->dddModules = $dddModules;
    }

    /**
     * @return RuleViolation[]
     */
    public function check(ModuleCollection $modules): array
    {
        $dddModules = $modules->collectWithNames($this->dddModules);

        $violations = [];

        foreach ($dddModules as $dddModule) {
            $violations = array_merge(
                $violations,
                $this->checkDomainNotDependOnOthers($dddModule),
                $this->checkApplicationNotDependOnOthersThanDomain($dddModule),
            );
        }

        return $violations;
    }

    /**
     * @return RuleViolation[]
     */
    private function checkDomainNotDependOnOthers(Module $dddModule): array
    {
        $domainNamespace = $dddModule->getNamespace() . '\Domain';
        $moduleDomainClasses = $dddModule->classesDependingOnNamespace($dddModule->getNamespace());
        $moduleDomainClasses = array_filter(
            $moduleDomainClasses,
            fn (ModuleClass $moduleClass) => strpos($moduleClass->getNamespace(), $domainNamespace) === 0,
        );

        $result = [];

        foreach ($moduleDomainClasses as $moduleDomainClass) {
            $forbiddenDependencies = $moduleDomainClass
                ->usedNamespaces()
                ->startingWith($dddModule->getNamespace())
                ->notStartingWith($domainNamespace)
            ;

            if ($forbiddenDependencies->count() > 0) {
                $result[] = new RuleViolation(
                    sprintf(
                        'Module %s domain class %s cannot depend on other module classes other than domain. Given: %s',
                        $dddModule->getName(),
                        $moduleDomainClass->getClassName(),
                        $forbiddenDependencies->toString(),
                    ),
                    self::VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN,
                    \get_class($this),
                );
            }
        }

        return $result;
    }

    /**
     * @return RuleViolation[]
     */
    private function checkApplicationNotDependOnOthersThanDomain(Module $dddModule): array
    {
        $domainNamespace = $dddModule->getNamespace() . '\Domain';
        $applicationNamespace = $dddModule->getNamespace() . '\Application';
        $moduleApplicationClasses = $dddModule->classesDependingOnNamespace($dddModule->getNamespace());
        $moduleApplicationClasses = array_filter(
            $moduleApplicationClasses,
            fn (ModuleClass $moduleClass) => strpos($moduleClass->getNamespace(), $applicationNamespace) === 0,
        );

        $result = [];

        foreach ($moduleApplicationClasses as $moduleApplicationClass) {
            $forbiddenDependencies = $moduleApplicationClass
                ->usedNamespaces()
                ->startingWith($dddModule->getNamespace())
                ->notStartingWith($domainNamespace)
                ->notStartingWith($applicationNamespace)
            ;

            if ($forbiddenDependencies->count() > 0) {
                $result[] = $this->buildApplicationRuleViolation(
                    $dddModule,
                    $moduleApplicationClass,
                    $forbiddenDependencies,
                );
            }
        }

        return $result;
    }

    private function buildApplicationRuleViolation(
        Module $dddModule,
        ModuleClass $moduleApplicationClass,
        NamespacesCollection $forbiddenDependencies
    ): RuleViolation {
        $string = 'Module %s application class %s cannot depend on other module classes other than domain or ' .
            'application. Given: %s';

        $message = sprintf(
            $string,
            $dddModule->getName(),
            $moduleApplicationClass->getClassName(),
            $forbiddenDependencies->toString(),
        );

        return new RuleViolation(
            $message,
            self::VIOLATION_APPLICATION_DEPEND_ON_NOT_DOMAIN_APPLICATION,
            \get_class($this),
        );
    }
}
