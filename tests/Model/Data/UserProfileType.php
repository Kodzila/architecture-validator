<?php

declare(strict_types=1);

namespace Isav\Core\Domain\Entity\Taxonomy;

use Isav\Core\Domain\Extension\UuidEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class UserProfileType
{
    use UuidEntity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $description;
}
