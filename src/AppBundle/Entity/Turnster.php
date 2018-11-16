<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TurnsterRepository")
 * @ORM\Table(name="turnster")
 */
class Turnster
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="voornaam", type="string", length=255)
     */
    private $voornaam = "leeg";

    /**
     * @var string
     * @ORM\Column(name="achternaam", type="string", length=255)
     */
    private $achternaam = "leeg";

    /**
     * @ORM\Column(name="geboortajaar", type="integer")
     */
    private $geboortejaar = 0;

    /**
     * @var string
     * @ORM\Column(name="niveau", type="string", length=12)
     */
    private $niveau = "leeg";

    /**
     * @var string
     * @ORM\Column(name="categorie", type="string", length=12)
     */
    private $categorie = "leeg";

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="turnster")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $afgemeld = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $wachtlijst;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $ingevuld = false;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var string
     * @ORM\Column(name="opmerking", type="text", nullable=true)
     */
    private $opmerking;

    /**
     * @ORM\OneToOne(targetEntity="Vloermuziek", inversedBy="turnster", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="vloermuziek_id", referencedColumnName="id", nullable=true)
     **/
    private $vloermuziek;

    /**
     * @ORM\OneToOne(targetEntity="Scores", inversedBy="turnster", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="score_id", referencedColumnName="id", nullable=true)
     **/
    private $scores;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="turnsters")
     * @ORM\JoinColumn(name="team", referencedColumnName="id", nullable=true)
     */
    private $team;

    public function getUitslagenLijst()
    {
        $totaalBrug = (floatval($this->getScores()->getTotaalBrug()));
        $totaalBalk = (floatval($this->getScores()->getTotaalBalk()));
        $totaalVloer = (floatval($this->getScores()->getTotaalVloer()));
        $totaalSprong1 = (floatval($this->getScores()->getTotaalSprong1()));
        $totaalSprong2 = (floatval($this->getScores()->getTotaalSprong2()));
        $totaalSprong = (floatval($this->getScores()->getTotaalSprong()));
        $totaal = floatval($totaalSprong + $totaalBrug + $totaalBalk + $totaalVloer);
        return array(
            'id' => $this->getId(),
            'userId' => $this->getUser()->getId(),
            'wedstrijdnummer' => $this->getScores()->getWedstrijdnummer(),
            'naam' => $this->voornaam . ' ' . $this->achternaam,
            'vereniging' => $this->getUser()->getVereniging()->getNaam() . ' ' . $this->getUser()->getVereniging()
                    ->getPlaats(),
            'categorie' => $this->getCategorie(),
            'niveau' => $this->getNiveau(),
            'dBrug' => number_format($this->getScores()->getDBrug(), 2, ",", "."),
            'nBrug' => number_format($this->getScores()->getNBrug(), 2, ",", "."),
            'totaalBrug' => $totaalBrug,
            'dBalk' => number_format($this->getScores()->getDBalk(), 2, ",", "."),
            'nBalk' => number_format($this->getScores()->getNBalk(), 2, ",", "."),
            'totaalBalk' => $totaalBalk,
            'dVloer' => number_format($this->getScores()->getDVloer(), 2, ",", "."),
            'nVloer' => number_format($this->getScores()->getNVloer(), 2, ",", "."),
            'totaalVloer' => $totaalVloer,
            'dSprong1' => number_format($this->getScores()->getDSprong1(), 2, ",", "."),
            'nSprong1' => number_format($this->getScores()->getNSprong1(), 2, ",", "."),
            'totaalSprong1' => $totaalSprong1,
            'dSprong2' => number_format($this->getScores()->getDSprong2(), 2, ",", "."),
            'nSprong2' => number_format($this->getScores()->getNSprong2(), 2, ",", "."),
            'totaalSprong2' => $totaalSprong2,
            'totaalSprong' => $totaalSprong,
            'totaal' => $totaal,
        );
    }

    public function isKeuze()
    {
        return (
            strtolower($this->categorie === 'jeugd 1') ||
            strtolower($this->categorie === 'junior') ||
            strtolower($this->categorie === 'senior')
        );
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set voornaam
     *
     * @param string $voornaam
     * @return Turnster
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
     * @return Turnster
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
     * Set geboortejaar
     *
     * @param integer $geboortejaar
     * @return Turnster
     */
    public function setGeboortejaar($geboortejaar)
    {
        $this->geboortejaar = $geboortejaar;

        return $this;
    }

    /**
     * Get geboortejaar
     *
     * @return integer 
     */
    public function getGeboortejaar()
    {
        return $this->geboortejaar;
    }

    /**
     * Set niveau
     *
     * @param string $niveau
     * @return Turnster
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau
     *
     * @return string 
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set wachtlijst
     *
     * @param boolean $wachtlijst
     * @return Turnster
     */
    public function setWachtlijst($wachtlijst)
    {
        $this->wachtlijst = $wachtlijst;

        return $this;
    }

    /**
     * Get wachtlijst
     *
     * @return boolean 
     */
    public function getWachtlijst()
    {
        return $this->wachtlijst;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Turnster
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     * @return Turnster
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime 
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set opmerking
     *
     * @param string $opmerking
     * @return Turnster
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
     * Set user
     *
     * @param User $user
     * @return Turnster
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set vloermuziek
     *
     * @param Vloermuziek $vloermuziek
     * @return Turnster
     */
    public function setVloermuziek(Vloermuziek $vloermuziek = null)
    {
        $this->vloermuziek = $vloermuziek;

        return $this;
    }

    /**
     * Get vloermuziek
     *
     * @return Vloermuziek
     */
    public function getVloermuziek()
    {
        return $this->vloermuziek;
    }

    /**
     * Set scores
     *
     * @param Scores $scores
     * @return Turnster
     */
    public function setScores(Scores $scores = null)
    {
        $this->scores = $scores;

        return $this;
    }

    /**
     * Get scores
     *
     * @return \AppBundle\Entity\Scores
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * Set afgemeld
     *
     * @param boolean $afgemeld
     * @return Turnster
     */
    public function setAfgemeld($afgemeld)
    {
        $this->afgemeld = $afgemeld;

        return $this;
    }

    /**
     * Get afgemeld
     *
     * @return boolean 
     */
    public function getAfgemeld()
    {
        return $this->afgemeld;
    }

    /**
     * Set ingevuld
     *
     * @param boolean $ingevuld
     * @return Turnster
     */
    public function setIngevuld($ingevuld)
    {
        $this->ingevuld = $ingevuld;

        return $this;
    }

    /**
     * Get ingevuld
     *
     * @return boolean 
     */
    public function getIngevuld()
    {
        return $this->ingevuld;
    }

    /**
     * Set categorie
     *
     * @param string $categorie
     * @return Turnster
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return string 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setTeam(?Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}
