<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule;

final class RuleViolation
{
    private string $message;
    private string $type;
    private string $ruleClass;

    public function __construct(string $message, string $type, string $ruleClass)
    {
        $this->message = $message;
        $this->type = $type;
        $this->ruleClass = $ruleClass;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRuleClass(): string
    {
        return $this->ruleClass;
    }
}
