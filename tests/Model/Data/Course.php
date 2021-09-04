<?php

declare(strict_types=1);

namespace Isav\Core\Domain\Entity\Content;

use Isav\Core\Domain\Entity\Content\Sub\GeoLocation;
use Isav\Core\Domain\Entity\Content\Sub\Link;
use Isav\Core\Domain\Entity\Taxonomy\Country;
use Isav\Core\Domain\Entity\Taxonomy\CourseFieldOfStudy;
use Isav\Core\Domain\Entity\Taxonomy\CourseLevel;
use Isav\Core\Domain\Entity\Taxonomy\Department;
use Isav\Core\Domain\Entity\Taxonomy\Language;
use Isav\Core\Domain\Entity\Taxonomy\Organisation;
use Isav\Core\Domain\Entity\Taxonomy\Tag;
use Isav\Core\Domain\Entity\Taxonomy\Topic;
use Isav\Core\Domain\Extension\ContactInfoEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Course extends Content
{
    use ContactInfoEntity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $title;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeImmutable $date;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeImmutable $endDate;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeImmutable $registrationDeadline;

    /**
     * @ManyToOne(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Organisation")
     */
    public Organisation $organisation;

    /**
     * @ManyToMany(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Department")
     */
    public Collection $departments;

    /**
     * @ManyToMany(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Tag")
     */
    public Collection $tags;

    /**
     * @ORM\Column(type="text", length=255)
     */
    public string $shortDescription;

    /**
     * @ManyToOne(targetEntity="Isav\Core\Domain\Entity\Taxonomy\CourseLevel")
     */
    public CourseLevel $courseLevel;

    /**
     * @ManyToOne(targetEntity="Isav\Core\Domain\Entity\Taxonomy\CourseFieldOfStudy")
     */
    public CourseFieldOfStudy $studyField;

    /**
     * @ManyToMany(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Topic")
     */
    public Collection $topics;

    /**
     * @Assert\Valid()
     * @ManyToOne(
     *     targetEntity="Isav\Core\Domain\Entity\Content\Sub\Link",
     *     cascade={"persist", "remove"},
     *     fetch="EAGER"
     * )
     */
    public Link $linkToCourse;

    /**
     * @ManyToMany(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Language")
     */
    public Collection $languages;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $ects;

    /**
     * @ManyToOne(targetEntity="Isav\Core\Domain\Entity\Taxonomy\Country")
     */
    public Country $country;

    /**
     * @ManyToOne(
     *     targetEntity="Isav\Core\Domain\Entity\Content\Sub\GeoLocation",
     *     cascade={"persist", "remove"},
     *     fetch="EAGER"
     * )
     */
    public GeoLocation $destination;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $teachingPlace;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $image;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->languages = new ArrayCollection();
    }

    public function addDepartment(Department $department): void
    {
        $this->departments->add($department);
    }

    public function removeDepartment(Department $department): void
    {
        $this->departments->removeElement($department);
    }

    public function addTag(Tag $department): void
    {
        $this->tags->add($department);
    }

    public function removeTag(Tag $department): void
    {
        $this->tags->removeElement($department);
    }

    public function addTopic(Topic $topic): void
    {
        $this->topics->add($topic);
    }

    public function removeTopic(Topic $topic): void
    {
        $this->topics->removeElement($topic);
    }

    public function addLanguage(Language $language): void
    {
        $this->languages->add($language);
    }

    public function removeLanguage(Language $language): void
    {
        $this->languages->removeElement($language);
    }
}
