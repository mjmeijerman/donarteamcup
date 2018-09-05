<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InstellingenRepository")
 * @ORM\Table(name="instellingen")
 */
class Instellingen
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(length=156)
     */
    private $instelling;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datum;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aantal;

    /**
     * @ORM\Column(type="datetime")
     */
    private $gewijzigd;

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
     * Set instelling
     *
     * @param string $instelling
     * @return Instellingen
     */
    public function setInstelling($instelling)
    {
        $this->instelling = $instelling;

        return $this;
    }

    /**
     * Get instelling
     *
     * @return string
     */
    public function getInstelling()
    {
        return $this->instelling;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     * @return Instellingen
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;

        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * Set aantal
     *
     * @param integer $aantal
     * @return Instellingen
     */
    public function setAantal($aantal)
    {
        $this->aantal = $aantal;

        return $this;
    }

    /**
     * Get aantal
     *
     * @return integer
     */
    public function getAantal()
    {
        return $this->aantal;
    }

    /**
     * Set gewijzigd
     *
     * @param \DateTime $gewijzigd
     * @return Instellingen
     */
    public function setGewijzigd($gewijzigd)
    {
        $this->gewijzigd = $gewijzigd;

        return $this;
    }

    /**
     * Get gewijzigd
     *
     * @return \DateTime
     */
    public function getGewijzigd()
    {
        return $this->gewijzigd;
    }
}
