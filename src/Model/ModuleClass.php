<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Model;

use Kodzila\ArchValidator\Model\Exception\ModuleLoadException;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

final class ModuleClass
{
    private string $className;
    private string $namespace;
    /**
     * @var string[]
     */
    private array $usedNamespaces;

    public function __construct(string $className, string $namespace, array $usedNamespaces)
    {
        Assert::allString($usedNamespaces);
        $this->className = $className;
        $this->namespace = $namespace;
        $this->usedNamespaces = $usedNamespaces;
    }

    public static function fromSpl(SplFileInfo $file): self
    {
        $content = $file->getContents();

        $className = self::extractClassName($content, $file);
        $namespace = self::extractNamespace($content);

        if ($namespace === null) {
            throw new ModuleLoadException(sprintf(
                'Failed to load ModuleClass from file %s - class has no namespace so it cannot be a ModuleClass.',
                $file->getPath() . '/' . $file->getFilename(),
            ));
        }

        $uses = self::extractUsages($content);

        return new self(
            $className,
            $namespace,
            $uses,
        );
    }

    private static function extractClassName(string &$content, SplFileInfo $file): string
    {
        $matches = [];
        preg_match('/(interface|class|trait)\s(\w+)\s/', $content, $matches);
        Assert::count($matches, 3, sprintf(
            'File %s was supposed to contain a class, interface or trait but has not',
            $file->getFilename(),
        ));
        return $matches[2];
    }

    private static function extractNamespace(string &$content): ?string
    {
        $matches = preg_grep('/namespace\s/', explode("\n", $content));

        if (count($matches) === 0) {
            return null;
        }

        $namespace = array_pop($matches);

        $line = $namespace;
        $line = str_replace('namespace ', '', $line);
        $line = str_replace(';', '', $line);

        return $line;
    }

    /**
     * Usages will be between start of file and start of class.
     * There is a case that trait could be considered as usages.
     *
     * @return string[]
     */
    private static function extractUsages(string &$content): array
    {
        $data = explode('{', $content);
        $content = $data[0];

        $matches = preg_grep('/use\s/', explode("\n", $content));
        $uses = [];

        foreach ($matches as $match) {
            $usage = self::extractUseFromLine($match);

            if ($usage === '') {
                continue;
            }

            $uses[] = $usage;
        }

        return $uses;
    }

    private static function extractUseFromLine(string $line): string
    {
        $line = str_replace('use ', '', $line);
        $line = str_replace(';', '', $line);

        $data = explode(' ', $line);

        if (\count($data) > 0) {
            $line = $data[0];
        }

        $line = trim($line);

        return $line;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string[]
     */
    public function getUsedNamespaces(): array
    {
        return $this->usedNamespaces;
    }

    public function usedNamespaces(): NamespacesCollection
    {
        return new NamespacesCollection($this->usedNamespaces);
    }

    /**
     * @return string[]
     */
    public function collectUsagesNotStartingWith(string $namespace): array
    {
        $result = [];

        foreach ($this->usedNamespaces as $usedNamespace) {
            if (strpos($usedNamespace, $namespace) !== 0) {
                $result[] = $usedNamespace;
            }
        }

        return $result;
    }

    public function usesNamespaceStartingWithButNotWith(string $startsWithNamespace, string $notStartsNamespace): bool
    {
        foreach ($this->usedNamespaces as $usedNamespace) {
            if (
                strpos($usedNamespace, $startsWithNamespace) === 0
                && strpos($usedNamespace, $notStartsNamespace) !== 0
            ) {
                return true;
            }
        }

        return false;
    }
}
