<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ToegestaneNiveausRepository")
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
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $calculationMethodSprongMeerkamp;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $calculationMethodSprongToestelPrijs;

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

    public function getCalculationMethodSprongMeerkamp(): ?string
    {
        return $this->calculationMethodSprongMeerkamp;
    }

    public function setCalculationMethodSprongMeerkamp(string $calculationMethodSprongMeerkamp): void
    {
        $this->calculationMethodSprongMeerkamp = $calculationMethodSprongMeerkamp;
    }

    public function getCalculationMethodSprongToestelPrijs(): ?string
    {
        return $this->calculationMethodSprongToestelPrijs;
    }

    public function setCalculationMethodSprongToestelPrijs(string $calculationMethodSprongToestelPrijs): void
    {
        $this->calculationMethodSprongToestelPrijs = $calculationMethodSprongToestelPrijs;
    }
}
