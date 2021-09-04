<?php

declare(strict_types=1);

namespace Isav\Core\Domain\Entity\Content;

/**
 * This is a struct made only to represent custom tooltips for map on frontend.
 * You SHOULD NOT use it anywhere in the codebase.
 */
final class Commented
{
    public string $zoom;
    public string $search;
    public string $routes;
    public string $areas;
    public string $markers;
    public string $iceSetting;
}
