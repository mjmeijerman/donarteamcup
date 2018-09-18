<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\JurylidRepository")
 * @ORM\Table(name="jurylid")
 */
class Jurylid
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=190)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="voornaam", type="string", length=255)
     */
    private $voornaam;

    /**
     * @var string
     * @ORM\Column(name="achternaam", type="string", length=255)
     */
    private $achternaam;

    /**
     * @var string
     * @ORM\Column(name="brevet", type="string", length=255)
     */
    private $brevet;

    /**
     * @var string
     * @ORM\Column(name="opmerking", type="text", nullable=true)
     */
    private $opmerking;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="jurylid")
     */
    private $user;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $zaterdag;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $zondag;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $maandag = false;

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
     * Set email
     *
     * @param string $email
     * @return Jurylid
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set voornaam
     *
     * @param string $voornaam
     * @return Jurylid
     */
    public function setVoornaam($voornaam)
    {
        $this->voornaam = trim($voornaam);

        return $this;
    }

    /**
     * Get voornaam
     *
     * @return string 
     */
    public function getVoornaam()
    {
        return $this->voornaam;
    }

    /**
     * Set achternaam
     *
     * @param string $achternaam
     * @return Jurylid
     */
    public function setAchternaam($achternaam)
    {
        $this->achternaam = trim($achternaam);

        return $this;
    }

    /**
     * Get achternaam
     *
     * @return string 
     */
    public function getAchternaam()
    {
        return $this->achternaam;
    }

    /**
     * Set brevet
     *
     * @param string $brevet
     * @return Jurylid
     */
    public function setBrevet($brevet)
    {
        $this->brevet = $brevet;

        return $this;
    }

    /**
     * Get brevet
     *
     * @return string 
     */
    public function getBrevet()
    {
        return $this->brevet;
    }

    /**
     * Set opmerking
     *
     * @param string $opmerking
     * @return Jurylid
     */
    public function setOpmerking($opmerking)
    {
        $this->opmerking = $opmerking;

        return $this;
    }

    /**
     * Get opmerking
     *
     * @return string 
     */
    public function getOpmerking()
    {
        return $this->opmerking;
    }

    /**
     * Set zaterdag
     *
     * @param boolean $zaterdag
     * @return Jurylid
     */
    public function setZaterdag($zaterdag)
    {
        $this->zaterdag = $zaterdag;

        return $this;
    }

    /**
     * Get zaterdag
     *
     * @return boolean 
     */
    public function getZaterdag()
    {
        return $this->zaterdag;
    }

    /**
     * Set zondag
     *
     * @param boolean $zondag
     * @return Jurylid
     */
    public function setZondag($zondag)
    {
        $this->zondag = $zondag;

        return $this;
    }

    /**
     * Get zondag
     *
     * @return boolean 
     */
    public function getZondag()
    {
        return $this->zondag;
    }


    /**
     * @param boolean $maandag
     *
     * @return Jurylid
     */
    public function setMaandag($maandag)
    {
        $this->maandag = $maandag;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMaandag()
    {
        return $this->maandag;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Jurylid
     */
    public function setUser(User $user = null)
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
