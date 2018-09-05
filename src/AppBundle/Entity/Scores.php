<?php

namespace AppBundle\Entity;

use AppBundle\AppBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Turnster;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ScoresRepository")
 * @ORM\Table(name="scores")
 */
class Scores
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wedstrijdnummer;

    /**
     * @ORM\OneToOne(targetEntity="Turnster", mappedBy="scores")
     * @var Turnster
     */
    private $turnster;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $wedstrijddag;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $wedstrijdronde;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $baan;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $groep;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $begintoestel;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $dSprong1 = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $eSprong1 = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $nSprong1 = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $dSprong2 = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $eSprong2 = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $nSprong2 = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $getoondSprong = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gepubliceerdSprong = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedSprong;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $dBrug = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $eBrug = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $nBrug = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $getoondBrug = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gepubliceerdBrug = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedBrug;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $dBalk = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $eBalk = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $nBalk = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $getoondBalk = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gepubliceerdBalk = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedBalk;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $dVloer = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $eVloer = 0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3)
     */
    private $nVloer = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $getoondVloer = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gepubliceerdVloer = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedVloer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $geturndVloer = 0;

    /**
     * @return mixed
     */
    public function getGeturndVloer()
    {
        return $this->geturndVloer;
    }

    /**
     * @param mixed $geturndVloer
     */
    public function setGeturndVloer($geturndVloer)
    {
        $this->geturndVloer = $geturndVloer;
    }

    /**
     * @return float|int
     */
    public function getTotaalBrug()
    {
        return ((floatval($this->getDBrug()) + floatval($this->getEBrug()) -
            floatval($this->getNBrug()) > 0) ? floatval($this->getDBrug()) +
            floatval($this->getEBrug()) - floatval($this->getNBrug()) : 0);
    }

    /**
     * @return float|int
     */
    public function getTotaalBalk()
    {
        return ((floatval($this->getDBalk()) + floatval($this->getEBalk()) -
            floatval($this->getNBalk()) > 0) ? floatval($this->getDBalk()) +
            floatval($this->getEBalk()) - floatval($this->getNBalk()) : 0);
    }

    /**
     * @return float|int
     */
    public function getTotaalVloer()
    {
        return ((floatval($this->getDVloer()) + floatval($this->getEVloer()) -
            floatval($this->getNVloer()) > 0) ? floatval($this->getDVloer()) +
            floatval($this->getEVloer()) - floatval($this->getNVloer()) : 0);
    }

    /**
     * @return float|int
     */
    public function getTotaalSprong1()
    {
        return ((floatval($this->getDSprong1()) + floatval($this->getESprong1()) -
            floatval($this->getNSprong1()) > 0) ? floatval($this->getDSprong1()) +
            floatval($this->getESprong1()) - floatval($this->getNSprong1()) : 0);
    }

    /**
     * @return float|int
     */
    public function getTotaalSprong2()
    {
        return ((floatval($this->getDSprong2()) + floatval($this->getESprong2()) -
            floatval($this->getNSprong2()) > 0) ? floatval($this->getDSprong2()) +
            floatval($this->getESprong2()) - floatval($this->getNSprong2()) : 0);
    }

    /**
     * @return float
     */
    public function getTotaalSprong()
    {
        return (($this->getTotaalSprong1() + $this->getTotaalSprong2()) / 2);
    }

    public function getScores()
    {
        $totaalBrug = $this->getTotaalBrug();
        $totaalBalk = $this->getTotaalBalk();
        $totaalVloer = $this->getTotaalVloer();
        $totaalSprong1 = $this->getTotaalSprong1();
        $totaalSprong2 = $this->getTotaalSprong2();
        $totaalSprong = $this->getTotaalSprong();
        $totaal = $totaalSprong + $totaalBrug + $totaalBalk + $totaalVloer;
        return [
            'userId' => $this->getTurnster()->getUser()->getId(),
            'wedstrijdnummer' => $this->getWedstrijdnummer(),
            'naam' => $this->getTurnster()->getVoornaam() . ' ' . $this->getTurnster()->getAchternaam(),
            'vereniging' => $this->getTurnster()->getUser()->getVereniging()->getNaam() . ' ' . $this->getTurnster()
                ->getUser()->getVereniging()->getPlaats(),
            'categorie' => $this->getTurnster()->getCategorie(),
            'niveau' => $this->getTurnster()->getNiveau(),
            'dBrug' => number_format($this->getDBrug(), 2, ",", "."),
            'nBrug' => number_format($this->getNBrug(), 2, ",", "."),
            'totaalBrug' => $totaalBrug,
            'dBalk' => number_format($this->getDBalk(), 2, ",", "."),
            'nBalk' => number_format($this->getNBalk(), 2, ",", "."),
            'totaalBalk' => $totaalBalk,
            'dVloer' => number_format($this->getDVloer(), 2, ",", "."),
            'nVloer' => number_format($this->getNVloer(), 2, ",", "."),
            'totaalVloer' => $totaalVloer,
            'dSprong1' => number_format($this->getDSprong1(), 2, ",", "."),
            'nSprong1' => number_format($this->getNSprong1(), 2, ",", "."),
            'totaalSprong1' => $totaalSprong1,
            'dSprong2' => number_format($this->getDSprong2(), 2, ",", "."),
            'nSprong2' => number_format($this->getNSprong2(), 2, ",", "."),
            'totaalSprong2' => $totaalSprong2,
            'totaalSprong' => $totaalSprong, 3,
            'totaal' => $totaal,
        ];
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
     * Set wedstrijdnummer
     *
     * @param integer $wedstrijdnummer
     * @return Scores
     */
    public function setWedstrijdnummer($wedstrijdnummer)
    {
        $this->wedstrijdnummer = $wedstrijdnummer;

        return $this;
    }

    /**
     * Get wedstrijdnummer
     *
     * @return integer 
     */
    public function getWedstrijdnummer()
    {
        return $this->wedstrijdnummer;
    }

    /**
     * Set turnster
     *
     * @param \AppBundle\Entity\Turnster $turnster
     * @return Scores
     */
    public function setTurnster(\AppBundle\Entity\Turnster $turnster = null)
    {
        $this->turnster = $turnster;

        return $this;
    }

    /**
     * Get turnster
     *
     * @return \AppBundle\Entity\Turnster 
     */
    public function getTurnster()
    {
        return $this->turnster;
    }

    /**
     * Set wedstrijddag
     *
     * @param string $wedstrijddag
     * @return Scores
     */
    public function setWedstrijddag($wedstrijddag)
    {
        $this->wedstrijddag = $wedstrijddag;

        return $this;
    }

    /**
     * Get wedstrijddag
     *
     * @return string 
     */
    public function getWedstrijddag()
    {
        return $this->wedstrijddag;
    }

    /**
     * Set wedstrijdronde
     *
     * @param string $wedstrijdronde
     * @return Scores
     */
    public function setWedstrijdronde($wedstrijdronde)
    {
        $this->wedstrijdronde = $wedstrijdronde;

        return $this;
    }

    /**
     * Get wedstrijdronde
     *
     * @return string 
     */
    public function getWedstrijdronde()
    {
        return $this->wedstrijdronde;
    }

    /**
     * Set baan
     *
     * @param string $baan
     * @return Scores
     */
    public function setBaan($baan)
    {
        $this->baan = $baan;

        return $this;
    }

    /**
     * Get baan
     *
     * @return string 
     */
    public function getBaan()
    {
        return $this->baan;
    }

    /**
     * Set groep
     *
     * @param string $groep
     * @return Scores
     */
    public function setGroep($groep)
    {
        $this->groep = $groep;

        return $this;
    }

    /**
     * Get groep
     *
     * @return string 
     */
    public function getGroep()
    {
        return $this->groep;
    }

    /**
     * Set dSprong1
     *
     * @param string $dSprong1
     * @return Scores
     */
    public function setDSprong1($dSprong1)
    {
        $this->dSprong1 = $dSprong1;

        return $this;
    }

    /**
     * Get dSprong1
     *
     * @return string 
     */
    public function getDSprong1()
    {
        return $this->dSprong1;
    }

    /**
     * Set eSprong1
     *
     * @param string $eSprong1
     * @return Scores
     */
    public function setESprong1($eSprong1)
    {
        $this->eSprong1 = $eSprong1;

        return $this;
    }

    /**
     * Get eSprong1
     *
     * @return string 
     */
    public function getESprong1()
    {
        return $this->eSprong1;
    }

    /**
     * Set nSprong1
     *
     * @param string $nSprong1
     * @return Scores
     */
    public function setNSprong1($nSprong1)
    {
        $this->nSprong1 = $nSprong1;

        return $this;
    }

    /**
     * Get nSprong1
     *
     * @return string 
     */
    public function getNSprong1()
    {
        return $this->nSprong1;
    }

    /**
     * Set dSprong2
     *
     * @param string $dSprong2
     * @return Scores
     */
    public function setDSprong2($dSprong2)
    {
        $this->dSprong2 = $dSprong2;

        return $this;
    }

    /**
     * Get dSprong2
     *
     * @return string 
     */
    public function getDSprong2()
    {
        return $this->dSprong2;
    }

    /**
     * Set eSprong2
     *
     * @param string $eSprong2
     * @return Scores
     */
    public function setESprong2($eSprong2)
    {
        $this->eSprong2 = $eSprong2;

        return $this;
    }

    /**
     * Get eSprong2
     *
     * @return string 
     */
    public function getESprong2()
    {
        return $this->eSprong2;
    }

    /**
     * Set nSprong2
     *
     * @param string $nSprong2
     * @return Scores
     */
    public function setNSprong2($nSprong2)
    {
        $this->nSprong2 = $nSprong2;

        return $this;
    }

    /**
     * Get nSprong2
     *
     * @return string 
     */
    public function getNSprong2()
    {
        return $this->nSprong2;
    }

    /**
     * Set getoondSprong
     *
     * @param boolean $getoondSprong
     * @return Scores
     */
    public function setGetoondSprong($getoondSprong)
    {
        $this->getoondSprong = $getoondSprong;

        return $this;
    }

    /**
     * Get getoondSprong
     *
     * @return boolean 
     */
    public function getGetoondSprong()
    {
        return $this->getoondSprong;
    }

    /**
     * Set gepubliceerdSprong
     *
     * @param boolean $gepubliceerdSprong
     * @return Scores
     */
    public function setGepubliceerdSprong($gepubliceerdSprong)
    {
        $this->gepubliceerdSprong = $gepubliceerdSprong;

        return $this;
    }

    /**
     * Get gepubliceerdSprong
     *
     * @return boolean 
     */
    public function getGepubliceerdSprong()
    {
        return $this->gepubliceerdSprong;
    }

    /**
     * Set updatedSprong
     *
     * @param \DateTime $updatedSprong
     * @return Scores
     */
    public function setUpdatedSprong($updatedSprong)
    {
        $this->updatedSprong = $updatedSprong;

        return $this;
    }

    /**
     * Get updatedSprong
     *
     * @return \DateTime 
     */
    public function getUpdatedSprong()
    {
        return $this->updatedSprong;
    }

    /**
     * Set dBrug
     *
     * @param string $dBrug
     * @return Scores
     */
    public function setDBrug($dBrug)
    {
        $this->dBrug = $dBrug;

        return $this;
    }

    /**
     * Get dBrug
     *
     * @return string 
     */
    public function getDBrug()
    {
        return $this->dBrug;
    }

    /**
     * Set eBrug
     *
     * @param string $eBrug
     * @return Scores
     */
    public function setEBrug($eBrug)
    {
        $this->eBrug = $eBrug;

        return $this;
    }

    /**
     * Get eBrug
     *
     * @return string 
     */
    public function getEBrug()
    {
        return $this->eBrug;
    }

    /**
     * Set nBrug
     *
     * @param string $nBrug
     * @return Scores
     */
    public function setNBrug($nBrug)
    {
        $this->nBrug = $nBrug;

        return $this;
    }

    /**
     * Get nBrug
     *
     * @return string 
     */
    public function getNBrug()
    {
        return $this->nBrug;
    }

    /**
     * Set getoondBrug
     *
     * @param boolean $getoondBrug
     * @return Scores
     */
    public function setGetoondBrug($getoondBrug)
    {
        $this->getoondBrug = $getoondBrug;

        return $this;
    }

    /**
     * Get getoondBrug
     *
     * @return boolean 
     */
    public function getGetoondBrug()
    {
        return $this->getoondBrug;
    }

    /**
     * Set gepubliceerdBrug
     *
     * @param boolean $gepubliceerdBrug
     * @return Scores
     */
    public function setGepubliceerdBrug($gepubliceerdBrug)
    {
        $this->gepubliceerdBrug = $gepubliceerdBrug;

        return $this;
    }

    /**
     * Get gepubliceerdBrug
     *
     * @return boolean 
     */
    public function getGepubliceerdBrug()
    {
        return $this->gepubliceerdBrug;
    }

    /**
     * Set updatedBrug
     *
     * @param \DateTime $updatedBrug
     * @return Scores
     */
    public function setUpdatedBrug($updatedBrug)
    {
        $this->updatedBrug = $updatedBrug;

        return $this;
    }

    /**
     * Get updatedBrug
     *
     * @return \DateTime 
     */
    public function getUpdatedBrug()
    {
        return $this->updatedBrug;
    }

    /**
     * Set dBalk
     *
     * @param string $dBalk
     * @return Scores
     */
    public function setDBalk($dBalk)
    {
        $this->dBalk = $dBalk;

        return $this;
    }

    /**
     * Get dBalk
     *
     * @return string 
     */
    public function getDBalk()
    {
        return $this->dBalk;
    }

    /**
     * Set eBalk
     *
     * @param string $eBalk
     * @return Scores
     */
    public function setEBalk($eBalk)
    {
        $this->eBalk = $eBalk;

        return $this;
    }

    /**
     * Get eBalk
     *
     * @return string 
     */
    public function getEBalk()
    {
        return $this->eBalk;
    }

    /**
     * Set nBalk
     *
     * @param string $nBalk
     * @return Scores
     */
    public function setNBalk($nBalk)
    {
        $this->nBalk = $nBalk;

        return $this;
    }

    /**
     * Get nBalk
     *
     * @return string 
     */
    public function getNBalk()
    {
        return $this->nBalk;
    }

    /**
     * Set getoondBalk
     *
     * @param boolean $getoondBalk
     * @return Scores
     */
    public function setGetoondBalk($getoondBalk)
    {
        $this->getoondBalk = $getoondBalk;

        return $this;
    }

    /**
     * Get getoondBalk
     *
     * @return boolean 
     */
    public function getGetoondBalk()
    {
        return $this->getoondBalk;
    }

    /**
     * Set gepubliceerdBalk
     *
     * @param boolean $gepubliceerdBalk
     * @return Scores
     */
    public function setGepubliceerdBalk($gepubliceerdBalk)
    {
        $this->gepubliceerdBalk = $gepubliceerdBalk;

        return $this;
    }

    /**
     * Get gepubliceerdBalk
     *
     * @return boolean 
     */
    public function getGepubliceerdBalk()
    {
        return $this->gepubliceerdBalk;
    }

    /**
     * Set updatedBalk
     *
     * @param \DateTime $updatedBalk
     * @return Scores
     */
    public function setUpdatedBalk($updatedBalk)
    {
        $this->updatedBalk = $updatedBalk;

        return $this;
    }

    /**
     * Get updatedBalk
     *
     * @return \DateTime 
     */
    public function getUpdatedBalk()
    {
        return $this->updatedBalk;
    }

    /**
     * Set dVloer
     *
     * @param string $dVloer
     * @return Scores
     */
    public function setDVloer($dVloer)
    {
        $this->dVloer = $dVloer;

        return $this;
    }

    /**
     * Get dVloer
     *
     * @return string 
     */
    public function getDVloer()
    {
        return $this->dVloer;
    }

    /**
     * Set eVloer
     *
     * @param string $eVloer
     * @return Scores
     */
    public function setEVloer($eVloer)
    {
        $this->eVloer = $eVloer;

        return $this;
    }

    /**
     * Get eVloer
     *
     * @return string 
     */
    public function getEVloer()
    {
        return $this->eVloer;
    }

    /**
     * Set nVloer
     *
     * @param string $nVloer
     * @return Scores
     */
    public function setNVloer($nVloer)
    {
        $this->nVloer = $nVloer;

        return $this;
    }

    /**
     * Get nVloer
     *
     * @return string 
     */
    public function getNVloer()
    {
        return $this->nVloer;
    }

    /**
     * Set getoondVloer
     *
     * @param boolean $getoondVloer
     * @return Scores
     */
    public function setGetoondVloer($getoondVloer)
    {
        $this->getoondVloer = $getoondVloer;

        return $this;
    }

    /**
     * Get getoondVloer
     *
     * @return boolean 
     */
    public function getGetoondVloer()
    {
        return $this->getoondVloer;
    }

    /**
     * Set gepubliceerdVloer
     *
     * @param boolean $gepubliceerdVloer
     * @return Scores
     */
    public function setGepubliceerdVloer($gepubliceerdVloer)
    {
        $this->gepubliceerdVloer = $gepubliceerdVloer;

        return $this;
    }

    /**
     * Get gepubliceerdVloer
     *
     * @return boolean 
     */
    public function getGepubliceerdVloer()
    {
        return $this->gepubliceerdVloer;
    }

    /**
     * Set updatedVloer
     *
     * @param \DateTime $updatedVloer
     * @return Scores
     */
    public function setUpdatedVloer($updatedVloer)
    {
        $this->updatedVloer = $updatedVloer;

        return $this;
    }

    /**
     * Get updatedVloer
     *
     * @return \DateTime 
     */
    public function getUpdatedVloer()
    {
        return $this->updatedVloer;
    }

    /**
     * Set begintoestel
     *
     * @param string $begintoestel
     * @return Scores
     */
    public function setBegintoestel($begintoestel)
    {
        $this->begintoestel = $begintoestel;

        return $this;
    }

    /**
     * Get begintoestel
     *
     * @return string 
     */
    public function getBegintoestel()
    {
        return $this->begintoestel;
    }
}
