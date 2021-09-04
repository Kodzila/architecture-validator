<?php

declare(strict_types=1);

namespace Kodzila\ArchValidator\Tests\Model;

use Kodzila\ArchValidator\Model\Exception\ModuleLoadException;
use Kodzila\ArchValidator\Model\ModuleClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ModuleClassLoadTest extends TestCase
{
    public function testUserProfileType(): void
    {
        $file = $this->loadDataFile('UserProfileType.php');
        $moduleClass = ModuleClass::fromSpl($file);

        $this->assertEquals('UserProfileType', $moduleClass->getClassName());
        $this->assertEquals('Isav\Core\Domain\Entity\Taxonomy', $moduleClass->getNamespace());
        $this->assertUsedNamespaces([
            'Isav\Core\Domain\Extension\UuidEntity',
            'Doctrine\ORM\Mapping'
        ], $moduleClass);
    }

    public function testCourse(): void
    {
        $file = $this->loadDataFile('Course.php');
        $moduleClass = ModuleClass::fromSpl($file);

        $this->assertEquals('Course', $moduleClass->getClassName());
        $this->assertEquals('Isav\Core\Domain\Entity\Content', $moduleClass->getNamespace());
        $this->assertUsedNamespaces([
            'Isav\Core\Domain\Entity\Content\Sub\GeoLocation',
            'Isav\Core\Domain\Entity\Content\Sub\Link',
            'Isav\Core\Domain\Entity\Taxonomy\Country',
            'Isav\Core\Domain\Entity\Taxonomy\CourseFieldOfStudy',
            'Isav\Core\Domain\Entity\Taxonomy\CourseLevel',
            'Isav\Core\Domain\Entity\Taxonomy\Department',
            'Isav\Core\Domain\Entity\Taxonomy\Language',
            'Isav\Core\Domain\Entity\Taxonomy\Organisation',
            'Isav\Core\Domain\Entity\Taxonomy\Tag',
            'Isav\Core\Domain\Entity\Taxonomy\Topic',
            'Isav\Core\Domain\Extension\ContactInfoEntity',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\Common\Collections\Collection',
            'Doctrine\ORM\Mapping',
            'Doctrine\ORM\Mapping\ManyToMany',
            'Doctrine\ORM\Mapping\ManyToOne',
            'Symfony\Component\Validator\Constraints',
        ], $moduleClass);
    }

    public function testLoadingNoNamespacedClass(): void
    {
        $file = $this->loadDataFile('NoNamespaceClass.php');
        $this->expectException(ModuleLoadException::class);
        ModuleClass::fromSpl($file);
    }

    /**
     * Comments (annotations) should not be treated as namespace.
     */
    public function testCommented(): void
    {
        $class = ModuleClass::fromSpl($this->loadDataFile('Commented.php'));
        $namespaces = $class->getUsedNamespaces();
        $this->assertCount(0, $namespaces);
    }

    private function assertUsedNamespaces(array $expected, ModuleClass $moduleClass): void
    {
        $usedNamespaces = $moduleClass->getUsedNamespaces();
        $this->assertCount(\count($expected), $usedNamespaces);

        for ($i = 0; $i < \count($usedNamespaces); $i++) {
            $this->assertEquals($expected[$i], $usedNamespaces[$i]);
        }
    }

    private function loadDataFile(string $name): SplFileInfo
    {
        $finder = new Finder();
        $files = $finder->in(__DIR__.'/Data/')->name($name)->files();
        $this->assertCount(1, $files);
        foreach ($files as $file) {
            return $file;
        }

        throw new \LogicException();
    }

}
