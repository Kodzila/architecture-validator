<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule;

use Webmozart\Assert\Assert;

final class RuleValidationException extends \Exception
{
    /**
     * @var string[]
     */
    private array $violationTypes = [];

    public static function fromRulesViolations(array $violations): self
    {
        Assert::allIsInstanceOf($violations, RuleViolation::class);

        $groups = self::groupByRule($violations);

        $message = '';
        $types = [];

        foreach ($groups as $ruleClass => $violations) {
            $res = sprintf(
                'Rule %s caused several violations: %s %s',
                $ruleClass,
                PHP_EOL,
                implode(
                    PHP_EOL,
                    array_map(fn(RuleViolation $violation) => '- ' . $violation->getMessage(), $violations),
                ),
            );

            $types = array_merge(
                $types,
                array_map(
                    fn(RuleViolation $violation) => $violation->getType(),
                    $violations,
                ),
            );
            $message .= $res . PHP_EOL . PHP_EOL;
        }

        $exception = new self($message);
        $exception->violationTypes = $types;

        return $exception;
    }

    public static function fromRuleViolations(RuleInterface $rule, array $violations): self
    {
        $res = new self(sprintf(
            'Rule %s caused several violations: %s %s',
            \get_class($rule),
            PHP_EOL,
            implode(PHP_EOL, array_map(fn(RuleViolation $violation) => '- ' . $violation->getMessage(), $violations)),
        ));

        $res->violationTypes = array_map(
            fn(RuleViolation $violation) => $violation->getType(),
            $violations,
        );

        return $res;
    }

    /**
     * @param RuleViolation[] $violations
     *
     * @return array<string, array<RuleViolation>>
     */
    private static function groupByRule(array $violations): array
    {
        $result = [];

        foreach ($violations as $violation) {
            $result[$violation->getRuleClass()][] = $violation;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getViolationTypes(): array
    {
        return $this->violationTypes;
    }
}
