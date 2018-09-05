<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="send_mail")
 */
class SendMail
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datum;

    /**
     * @ORM\Column(type="text")
     */
    protected $bericht;

    /**
     * @ORM\Column(length=300)
     */
    protected $aan;

    /**
     * @ORM\Column(length=300)
     */
    protected $van;

    /**
     * @ORM\Column(length=300)
     */
    protected $onderwerp;

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
     * Set datum
     *
     * @param \DateTime $datum
     * @return SendMail
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
     * Set bericht
     *
     * @param string $bericht
     * @return SendMail
     */
    public function setBericht($bericht)
    {
        $this->bericht = $bericht;

        return $this;
    }

    /**
     * Get bericht
     *
     * @return string 
     */
    public function getBericht()
    {
        return $this->bericht;
    }

    /**
     * Set aan
     *
     * @param string $aan
     * @return SendMail
     */
    public function setAan($aan)
    {
        $this->aan = $aan;

        return $this;
    }

    /**
     * Get aan
     *
     * @return string 
     */
    public function getAan()
    {
        return $this->aan;
    }

    /**
     * Set van
     *
     * @param string $van
     * @return SendMail
     */
    public function setVan($van)
    {
        $this->van = $van;

        return $this;
    }

    /**
     * Get van
     *
     * @return string 
     */
    public function getVan()
    {
        return $this->van;
    }

    /**
     * Set onderwerp
     *
     * @param string $onderwerp
     * @return SendMail
     */
    public function setOnderwerp($onderwerp)
    {
        $this->onderwerp = $onderwerp;

        return $this;
    }

    /**
     * Get onderwerp
     *
     * @return string 
     */
    public function getOnderwerp()
    {
        return $this->onderwerp;
    }
}
