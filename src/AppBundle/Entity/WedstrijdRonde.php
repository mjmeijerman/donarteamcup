<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="WedstrijdRondeRepository")
 * @ORM\Table(name="wedstrijd_ronde", options={ "charset"="utf8mb4", "collate"="utf8mb4_unicode_ci" })
 */
class WedstrijdRonde
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dag;

    /**
     * @ORM\Column(type="integer")
     */
    private $ronde;

    /**
     * @ORM\Column(type="integer")
     */
    private $baan;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime $startTijd
     */
    private $startTijd;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime $eindTijd
     */
    private $eindTijd;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="wedstrijdRonde", cascade={"persist"})
     * @var Team[]
     */
    private $teams;

    /**
     * @ORM\ManyToMany(targetEntity="TeamSoort", mappedBy="wedstrijdRondes")
     */
    private $teamSoorten;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxTeams;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $uitslagGepubliceerd = 0;

    public function __construct()
    {
        $this->teams       = new ArrayCollection();
        $this->teamSoorten = new ArrayCollection();
    }

    public function alleTeamsIngedeeldOpBeginToestel()
    {
        foreach ($this->teams as $team) {
            if ($team->getWachtlijst() || $team->isAfgemeld()) {
                continue;
            }

            if (!$team->isIngedeeldOpToestel()) {
                return false;
            }
        }

        return true;
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
     * @param string $dag
     */
    public function setDag($dag)
    {
        $this->dag = $dag;
    }

    public function getDag()
    {
        return $this->dag;
    }

    /**
     * @param \DateTime $datum
     */
    public function setStartTijd(\DateTime $startTijd)
    {
        $this->startTijd = $startTijd;
    }

    /**
     * @return \DateTime
     */
    public function getStartTijd()
    {
        return $this->startTijd;
    }

    /**
     * @param \DateTime $datum
     */
    public function setEindTijd(\DateTime $eindTijd)
    {
        $this->eindTijd = $eindTijd;
    }

    /**
     * @return \DateTime
     */
    public function getEindTijd()
    {
        return $this->eindTijd;
    }

    public function addTeam(Team $team)
    {
        $this->teams[] = $team;
    }

    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);
    }

    /**
     * @return Team[]
     */
    public function getTeams()
    {
        return $this->teams;
    }

    public function addTeamSoort(TeamSoort $teamSoort)
    {
        $this->teamSoorten[] = $teamSoort;
    }

    public function removeTeamSoort(TeamSoort $teamSoort)
    {
        $this->teamSoorten->removeElement($teamSoort);
    }

    /**
     * @return ArrayCollection|TeamSoort[]
     */
    public function getTeamSoorten()
    {
        return $this->teamSoorten;
    }

    /**
     * @return mixed
     */
    public function getMaxTeams()
    {
        return $this->maxTeams;
    }

    /**
     * @param mixed $maxTeams
     */
    public function setMaxTeams($maxTeams): void
    {
        $this->maxTeams = $maxTeams;
    }

    /**
     * @return mixed
     */
    public function getRonde()
    {
        return $this->ronde;
    }

    /**
     * @param mixed $ronde
     */
    public function setRonde($ronde): void
    {
        $this->ronde = $ronde;
    }

    /**
     * @return int
     */
    public function getBaan()
    {
        return $this->baan;
    }

    /**
     * @param int $baan
     */
    public function setBaan($baan): void
    {
        $this->baan = $baan;
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
}
