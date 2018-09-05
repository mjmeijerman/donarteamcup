<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="betaling", options={ "charset"="utf8mb4", "collate"="utf8mb4_unicode_ci" })
 */
class Betaling
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $bedrag;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime $datumBetaald
     */
    private $datumBetaald;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="betaling")
     *
     */
    private $user;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set bedrag
     *
     * @param integer $bedrag
     * @return Betaling
     */
    public function setBedrag($bedrag)
    {
        $this->bedrag = $bedrag;

        return $this;
    }

    /**
     * Get bedrag
     *
     * @return integer 
     */
    public function getBedrag()
    {
        return $this->bedrag;
    }

    /**
     * Set datumBetaald
     *
     * @param \DateTime $datumBetaald
     * @return Betaling
     */
    public function setDatumBetaald($datumBetaald)
    {
        $this->datumBetaald = $datumBetaald;

        return $this;
    }

    /**
     * Get datumBetaald
     *
     * @return \DateTime 
     */
    public function getDatumBetaald()
    {
        return $this->datumBetaald;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Betaling
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
