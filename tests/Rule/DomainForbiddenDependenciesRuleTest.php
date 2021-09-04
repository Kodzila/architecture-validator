<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Rule;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\Extension\DomainForbiddenDependenciesRule;
use Kodzila\ArchValidator\Rule\RuleViolation;
use PHPUnit\Framework\TestCase;

final class DomainForbiddenDependenciesRuleTest extends TestCase
{
    public function testNoDependencies(): void
    {
        $rule = new DomainForbiddenDependenciesRule(['Core'], []);

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

    public function testForbiddenDependency(): void
    {
        $rule = new DomainForbiddenDependenciesRule(['Core'], []);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'Symfony\Component\Finder\Finder',
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertHasViolationType(
            DomainForbiddenDependenciesRule::VIOLATION_FORBIDDEN_DEPENDENCY_IN_DOMAIN_LAYER,
            $violations
        );
    }

    public function testWhitelistedBaseDependency(): void
    {
        $rule = new DomainForbiddenDependenciesRule(['Core'], [
            'Symfony\Component',
        ]);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'Symfony\Component\Finder\Finder',
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testWhitelistedFullDependency(): void
    {
        $rule = new DomainForbiddenDependenciesRule(['Core'], [
            'Symfony\Component\Finder\Finder',
        ]);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'Symfony\Component\Finder\Finder',
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testDependencyFromModule(): void
    {
        $rule = new DomainForbiddenDependenciesRule(['Core'], []);

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'DomainService',
                        'App\\Core\\Domain',
                        [
                            'App\Core\Infrastructure\InfrastructureService',
                        ]
                    ),
                    new ModuleClass(
                        'InfrastructureService',
                        'App\\Core\\Infrastructure',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    /**
     * @param RuleViolation[] $violations
     */
    private function assertHasViolationType(string $type, array $violations): void
    {
        foreach ($violations as $violation) {
            if ($violation->getType() === $type) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail(sprintf(
            'Expected violation with type %s',
            $type
        ));
    }
}
