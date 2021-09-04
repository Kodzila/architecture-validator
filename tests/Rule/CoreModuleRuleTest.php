<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Rule;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleClass;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\Extension\CoreModuleRule;
use PHPUnit\Framework\TestCase;

final class CoreModuleRuleTest extends TestCase
{
    public function testSingleClass(): void
    {
        $rule = new CoreModuleRule('Core');

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'CoreClass',
                        'App\\Core',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(0, $violations);
    }

    public function testCoreDependOnOtherModule(): void
    {
        $rule = new CoreModuleRule('Core');

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'CoreClass',
                        'App\\Core',
                        [
                            'App\\Sub\\SubClass'
                        ]
                    ),
                ],
            ),
            new Module(
                'Sub',
                'App\\Sub',
                [
                    new ModuleClass(
                        'SubClass',
                        'App\\Sub',
                        []
                    ),
                ],
            ),
        ]));

        $this->assertCount(1, $violations);
        $this->assertEquals(CoreModuleRule::VIOLATION_CORE_DEPEND_ON_SUBMODULE, $violations[0]->getType());
    }

    public function testSubmoduleDependOnOtherSubmodule(): void
    {
        $rule = new CoreModuleRule('Core');

        $violations = $rule->check(new ModuleCollection([
            new Module(
                'Core',
                'App\\Core',
                [
                    new ModuleClass(
                        'CoreClass',
                        'App\\Core',
                        [
                            'App\\Sub\\SubClass'
                        ]
                    ),
                ],
            ),
            new Module(
                'Sub1',
                'App\\Sub1',
                [
                    new ModuleClass(
                        'SubClass',
                        'App\\Sub1',
                        []
                    ),
                ],
            ),
            new Module(
                'Sub2',
                'App\\Sub2',
                [
                    new ModuleClass(
                        'SubClass',
                        'App\\Sub2',
                        [
                            'App\\Sub1\\SubClass'
                        ]
                    ),
                ],
            ),
        ]));

        $this->assertCount(1, $violations);
        $this->assertEquals(CoreModuleRule::VIOLATION_SUBMODULE_DEPEND_ON_OTHER_SUBMODULE, $violations[0]->getType());
    }
}
