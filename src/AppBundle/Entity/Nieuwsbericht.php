<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="nieuwsbericht")
 */
class Nieuwsbericht
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(length=156)
     */
    protected $datumtijd;


    /**
     * @ORM\Column(type="integer")
     */
    protected $jaar;

    /**
     * @ORM\Column(length=156)
     */
    protected $titel;

    /**
     * @ORM\Column(type="text")
     */
    protected $bericht;

    public function getAll()
    {
        $items = [
            'id' => $this->id,
            'datumtijd' => $this->datumtijd,
            'jaar' => $this->jaar,
            'titel' => $this->titel,
            'bericht' => $this->bericht,
        ];
        return $items;
    }

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
     * Set datumtijd
     *
     * @param  $datumtijd
     * @return Nieuwsbericht
     */
    public function setDatumtijd($datumtijd)
    {
        $this->datumtijd = $datumtijd;

        return $this;
    }

    /**
     * Get datumtijd
     *
     * @return string
     */
    public function getDatumtijd()
    {
        return $this->datumtijd;
    }

    /**
     * Set jaar
     *
     * @param integer $jaar
     * @return Nieuwsbericht
     */
    public function setJaar($jaar)
    {
        $this->jaar = $jaar;

        return $this;
    }

    /**
     * Get jaar
     *
     * @return integer 
     */
    public function getJaar()
    {
        return $this->jaar;
    }

    /**
     * Set titel
     *
     * @param string $titel
     * @return Nieuwsbericht
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * Get titel
     *
     * @return string 
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * Set bericht
     *
     * @param string $bericht
     * @return Nieuwsbericht
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
}
