<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_soort")
 */
class TeamSoort
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
    private $categorie;

    /**
     * @ORM\Column(length=156)
     */
    private $niveau;

    /**
     * @ORM\Column(type="integer")
     */
    private $uitslagGepubliceerd = 0;

    /**
     * @ORM\OneToMany(targetEntity="ToegestaneNiveaus", mappedBy="teamSoort", cascade={"persist"})
     */
    private $niveaus;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="teamSoort", cascade={"persist"})
     */
    private $teams;

    /**
     * @ORM\ManyToMany(targetEntity="WedstrijdRonde", inversedBy="teamSoorten")
     */
    private $wedstrijdRondes;

    public function __construct()
    {
        $this->niveaus = new ArrayCollection();
        $this->teams   = new ArrayCollection();
        $this->wedstrijdRondes = new ArrayCollection();
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
     * Set categorie
     *
     * @param string $categorie
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
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

    /**
     * Set niveau
     *
     * @param string $niveau
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
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
     * Set uitslagGepubliceerd
     *
     * @param integer $uitslagGepubliceerd
     */
    public function setUitslagGepubliceerd($uitslagGepubliceerd)
    {
        $this->uitslagGepubliceerd = $uitslagGepubliceerd;
    }

    /**
     * Get uitslagGepubliceerd
     *
     * @return integer
     */
    public function getUitslagGepubliceerd()
    {
        return $this->uitslagGepubliceerd;
    }

    public function addNiveau(ToegestaneNiveaus $niveau)
    {
        $this->niveaus[] = $niveau;
    }

    public function removeNiveau(ToegestaneNiveaus $niveau)
    {
        $this->niveaus->removeElement($niveau);
    }

    /**
     * @return ArrayCollection|ToegestaneNiveaus[]
     */
    public function getNiveaus()
    {
        return $this->niveaus;
    }

    public function addTeam(Team $team)
    {
        $this->teams[] = $team;
    }

    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);
    }

    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @return mixed
     */
    public function getWedstrijdRondes()
    {
        return $this->wedstrijdRondes;
    }

    /**
     * @param mixed $wedstrijdRonde
     */
    public function addWedstrijdRonde($wedstrijdRonde): void
    {
        $this->wedstrijdRondes[] = $wedstrijdRonde;
    }

    /**
     * @param mixed $wedstrijdRonde
     */
    public function removeWedstrijdRonde($wedstrijdRonde): void
    {
        $this->wedstrijdRondes->removeElement($wedstrijdRonde);
    }
}
