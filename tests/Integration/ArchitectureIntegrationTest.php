<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests;

use Kodzila\ArchValidator\Architecture;
use Kodzila\ArchValidator\Rule\Extension\CoreModuleRule;
use Kodzila\ArchValidator\Rule\Extension\DomainDrivenDesignRule;
use Kodzila\ArchValidator\Rule\Extension\DomainForbiddenDependenciesRule;
use Kodzila\ArchValidator\Rule\RuleValidationException;
use PHPUnit\Framework\TestCase;

final class ArchitectureIntegrationTest extends TestCase
{
    public function testGoodApp(): void
    {
        $architecture = Architecture::build()
            ->defineModule(
                'Core',
                'Kodzila\ArchValidator\Tests\Integration\GoodApp\Core',
                'tests/Integration/GoodApp/Core/'
            )
            ->defineModule(
                'Email',
                'Kodzila\ArchValidator\Tests\Integration\GoodApp\Email',
                'tests/Integration/GoodApp/Email/'
            )
        ;

        $architecture->checkRules([
            new DomainDrivenDesignRule(['Core']),
            new CoreModuleRule('Core')
        ]);
        $architecture->checkRule(new DomainDrivenDesignRule(['Core']));
        $architecture->checkRule(new CoreModuleRule('Core'));
        $this->assertTrue(true);
    }

    public function testBadApp(): void
    {
        $architecture = Architecture::build()
            ->defineModule(
                'Core',
                'Kodzila\ArchValidator\Tests\Integration\BadApp\Core',
                'tests/Integration/BadApp/Core/'
            )
            ->defineModule(
                'Email',
                'Kodzila\ArchValidator\Tests\Integration\BadApp\Email',
                'tests/Integration/BadApp/Email/'
            )
            ->defineModule(
                'File',
                'Kodzila\ArchValidator\Tests\Integration\BadApp\File',
                'tests/Integration/BadApp/File/'
            )
        ;

        $this->assertRuleValidationException(fn () => $architecture->checkRules([
            new DomainDrivenDesignRule(['Core']),
            new CoreModuleRule('Core'),
            new DomainForbiddenDependenciesRule(['Core'], [])
        ]), [
            DomainDrivenDesignRule::VIOLATION_DOMAIN_DEPEND_ON_NOT_DOMAIN,
            DomainDrivenDesignRule::VIOLATION_APPLICATION_DEPEND_ON_NOT_DOMAIN_APPLICATION,
            CoreModuleRule::VIOLATION_CORE_DEPEND_ON_SUBMODULE,
            CoreModuleRule::VIOLATION_SUBMODULE_DEPEND_ON_OTHER_SUBMODULE,
            DomainForbiddenDependenciesRule::VIOLATION_FORBIDDEN_DEPENDENCY_IN_DOMAIN_LAYER,
        ]);
    }

    private function assertRuleValidationException(callable $action, array $expectedViolationTypes): void
    {
        $caught = null;

        try {
            $action();
        }
        catch (RuleValidationException $exception) {
            $caught = $exception;
        }

        $this->assertNotNull($caught);

        $this->assertCount(\count($expectedViolationTypes), $caught->getViolationTypes());
        foreach ($expectedViolationTypes as $expectedViolationType) {
            if (!\in_array($expectedViolationType, $caught->getViolationTypes(), true)) {
                $this->fail(sprintf(
                    'Expected violation type %s to be present in reported violation types %s',
                    $expectedViolationType,
                    implode(',', $caught->getViolationTypes())
                ));
            }
        }
    }

}
