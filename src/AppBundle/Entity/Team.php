<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TeamRepository")
 * @ORM\Table(name="team")
 */
class Team
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="team_name", type="string", length=190, unique=true, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="TeamSoort", inversedBy="teams")
     */
    private $teamSoort;

    /**
     * @ORM\OneToMany(targetEntity="Turnster", mappedBy="team", cascade={"persist"})
     * @var Turnster[]
     */
    private $turnsters;

    /**
     * @ORM\ManyToOne(targetEntity="WedstrijdRonde", inversedBy="teams", cascade={"persist"})
     */
    private $wedstrijdRonde;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="teams")
     */
    private $user;

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
    private $afgemeld;

    public function __construct()
    {
        $this->turnsters = new ArrayCollection();
        $this->afgemeld  = false;
    }

    public function isIngedeeldOpToestel()
    {
        foreach ($this->turnsters as $turnster){
            if (!$turnster->getScores()->getBegintoestel()) {
                return false;
            }
        }

        return true;
    }

    public function isIngedeeldOpBaan()
    {
        foreach ($this->turnsters as $turnster){
            if (!$turnster->getScores()->getBaan()) {
                return false;
            }
        }

        return true;
    }

    public function getBeginToestel()
    {
        foreach ($this->turnsters as $turnster){
            if ($turnster->getScores()->getBegintoestel()) {
                return $turnster->getScores()->getBegintoestel();
            }
        }

        return null;
    }

    public function getBaan()
    {
        foreach ($this->turnsters as $turnster){
            if ($turnster->getScores()->getBaan()) {
                return $turnster->getScores()->getBaan();
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return TeamSoort
     */
    public function getTeamSoort()
    {
        return $this->teamSoort;
    }

    public function setTeamSoort(?TeamSoort $teamSoort): void
    {
        $this->teamSoort = $teamSoort;
    }

    /**
     * @return ArrayCollection|Turnster[]
     */
    public function getTurnsters()
    {
        return $this->turnsters;
    }

    public function addTurnster(Turnster $turnster): void
    {
        $this->turnsters[] = $turnster;
    }

    public function removeTurnster(Turnster $turnster): void
    {
        $this->turnsters->removeElement($turnster);
    }

    public function getWedstrijdRonde(): WedstrijdRonde
    {
        return $this->wedstrijdRonde;
    }

    public function setWedstrijdRonde(WedstrijdRonde $wedstrijdRonde): void
    {
        $this->wedstrijdRonde = $wedstrijdRonde;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @param boolean $wachtlijst
     */
    public function setWachtlijst($wachtlijst)
    {
        $this->wachtlijst = $wachtlijst;
    }

    /**
     * @return boolean
     */
    public function getWachtlijst()
    {
        return $this->wachtlijst;
    }

    /**
     * @return bool
     */
    public function isAfgemeld(): bool
    {
        return $this->afgemeld;
    }

    /**
     * @param bool $afgemeld
     */
    public function setAfgemeld(bool $afgemeld): void
    {
        $this->afgemeld = $afgemeld;
    }
}
