<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator;

use Kodzila\ArchValidator\Model\Module;
use Kodzila\ArchValidator\Model\ModuleCollection;
use Kodzila\ArchValidator\Rule\RuleInterface;
use Kodzila\ArchValidator\Rule\RuleValidationException;
use Kodzila\ArchValidator\Rule\RuleViolation;
use Webmozart\Assert\Assert;

final class Architecture
{
    private ModuleCollection $moduleCollection;

    private function __construct()
    {
        $this->moduleCollection = new ModuleCollection();
    }

    public static function build(): self
    {
        return new self();
    }

    public function defineModule(string $moduleName, string $moduleNamespace, string $modulePath): self
    {
        $this->moduleCollection->add(Module::fromFileSystem($moduleName, $moduleNamespace, $modulePath));

        return $this;
    }

    public function checkRule(RuleInterface $rule): void
    {
        $violations = $rule->check($this->moduleCollection);
        Assert::allIsInstanceOf($violations, RuleViolation::class);

        if (\count($violations) !== 0) {
            throw RuleValidationException::fromRuleViolations($rule, $violations);
        }
    }

    /**
     * @param RuleInterface[] $rules
     */
    public function checkRules(array $rules): void
    {
        Assert::allIsInstanceOf($rules, RuleInterface::class);

        $violations = [];

        foreach ($rules as $rule) {
            $violations = array_merge(
                $violations,
                $rule->check($this->moduleCollection),
            );
        }

        if (\count($violations) !== 0) {
            throw RuleValidationException::fromRulesViolations($violations);
        }
    }
}
