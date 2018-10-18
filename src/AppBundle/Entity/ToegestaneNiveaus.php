<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="toegestane_niveaus")
 */
class ToegestaneNiveaus
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
     * @ORM\ManyToOne(targetEntity="TeamSoort", inversedBy="niveaus")
     * @ORM\JoinColumn(name="team_soort", referencedColumnName="id", nullable=true)
     */
    private $teamSoort;

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
     * @return ToegestaneNiveaus
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

    /**
     * Set niveau
     *
     * @param string $niveau
     * @return ToegestaneNiveaus
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
     * Set uitslagGepubliceerd
     *
     * @param integer $uitslagGepubliceerd
     * @return ToegestaneNiveaus
     */
    public function setUitslagGepubliceerd($uitslagGepubliceerd)
    {
        $this->uitslagGepubliceerd = $uitslagGepubliceerd;

        return $this;
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

    public function getTeamSoort()
    {
        return $this->teamSoort;
    }

    public function setTeamSoort(?TeamSoort $teamSoort)
    {
        $this->teamSoort = $teamSoort;
    }
}
