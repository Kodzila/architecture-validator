<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Rule;

use Kodzila\ArchValidator\Model\ModuleCollection;

interface RuleInterface
{
    /**
     * @param ModuleCollection $modules
     * @return RuleViolation[]
     */
    public function check(ModuleCollection $modules): array;
}
