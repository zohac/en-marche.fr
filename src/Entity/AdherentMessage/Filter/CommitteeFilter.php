<?php

namespace AppBundle\Entity\AdherentMessage\Filter;

use AppBundle\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class CommitteeFilter extends AbstractUserFilter
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @Assert\NotBlank
     */
    private $committee;

    public function __construct(Committee $committee = null)
    {
        $this->committee = $committee;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}
