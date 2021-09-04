<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Rule;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\Extension\DomainDrivenDesignRule;
use PHPUnit\Framework\TestCase;

final class DomainDrivenDesignRuleTest extends TestCase
{
    public function testOnlyDomain(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testDomainDependsOnDomain(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'App\\Core\\Domain\\DomainEntity'
                        ]
                    ),
                    new ModuleClass(
                        'DomainEntity',
                        'App\\Core\\Domain',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testDomainDependsOnApplication(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'App\\Core\\Application\\ApplicationService'
                        ]
                    ),
                    new ModuleClass(
                        'ApplicationService',
                        'App\\Core\\Application',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(1, $violations);
        $this->assertEquals(
            DomainDrivenDesignRule::VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN,
            $violations[0]->getType()
        );
    }

    public function testOnlyDomainAndApplication(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        []
                    ),
                    new ModuleClass(
                        'ApplicationService',
                        'App\\Core\\Application',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testApplicationDependsOnDomain(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        []
                    ),
                    new ModuleClass(
                        'ApplicationService',
                        'App\\Core\\Application',
                        [
                            'App\\Core\\Domain\\DomainService',
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testDomainDependsOnInfrastructure(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'Repository',
                        'App\\Core\\Domain',
                        [
                            'App\\Core\\Infrastructure\\GoogleWebService'
                        ]
                    ),
                    new ModuleClass(
                        'GoogleWebService',
                        'App\\Core\\Infrastructure',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(1, $violations);
        $this->assertEquals(
            DomainDrivenDesignRule::VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN,
            $violations[0]->getType()
        );
    }

    public function testApplicationDependsOnInfrastructure(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'AppService',
                        'App\\Core\\Application',
                        [
                            'App\\Core\\Infrastructure\\GoogleWebService'
                        ]
                    ),
                    new ModuleClass(
                        'GoogleWebService',
                        'App\\Core\\Infrastructure',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(1, $violations);
        $this->assertEquals(
            DomainDrivenDesignRule::VIOLATION_APPLICATION_DEPEND_ON_NOT_DOMAIN_APPLICATION,
            $violations[0]->getType()
        );
    }

    public function testProperDependency(): void
    {
        $rule = new DomainDrivenDesignRule(['Core']);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'Repository',
                        'App\\Core\\Domain',
                        []
                    ),
                    new ModuleClass(
                        'AppService',
                        'App\\Core\\Application',
                        [
                            'App\\Core\\Domain\\Repository'
                        ]
                    ),
                    new ModuleClass(
                        'DoctrineRepository',
                        'App\\Core\\Infrastructure',
                        [
                            'App\\Core\\Domain\\Repository'
                        ]
                    ),
                    new ModuleClass(
                        'MyController',
                        'App\\Core\\Presentation',
                        [
                            'App\\Core\\Application\\AppService'
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }
}
