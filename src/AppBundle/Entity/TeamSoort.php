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
     * @ORM\OneToMany(targetEntity="ToegestaneNiveaus", mappedBy="teamSoort", cascade={"persist"}, orphanRemoval=TRUE)
     */
    private $niveaus;

    public function __construct()
    {
        $this->niveaus = new ArrayCollection();
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

    public function getNiveaus()
    {
        return $this->niveaus;
    }
}
