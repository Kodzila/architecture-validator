<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Model;

use Webmozart\Assert\Assert;

final class NamespacesCollection
{
    /**
     * @var string[]
     */
    private array $namespaces;

    /**
     * @param string[] $namespaces
     */
    public function __construct(array $namespaces)
    {
        Assert::allString($namespaces);
        $this->namespaces = $namespaces;
    }

    public function notStartingWith(string $start): self
    {
        $result = [];

        foreach ($this->namespaces as $namespace) {
            if (strpos($namespace, $start) !== 0) {
                $result[] = $namespace;
            }
        }

        return new self($result);
    }

    public function startingWith(string $start): self
    {
        $result = [];

        foreach ($this->namespaces as $namespace) {
            if (strpos($namespace, $start) === 0) {
                $result[] = $namespace;
            }
        }

        return new self($result);
    }

    public function count(): int
    {
        return count($this->namespaces);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->namespaces;
    }

    public function toString(): string
    {
        return implode(', ', $this->namespaces);
    }
}
