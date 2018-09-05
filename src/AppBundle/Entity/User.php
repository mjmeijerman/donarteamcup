<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=190, unique=true)
     */
    private $username;

    /**
     * @ORM\COLUMN(type="string", length=60)
     */
    private $role;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=190)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="voornaam", type="string", length=255)
     */
    private $voornaam;

    /**
     * @var string
     * @ORM\Column(name="achternaam", type="string", length=255)
     */
    private $achternaam;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="verantwoordelijkheid", type="string", length=255, nullable=true)
     */
    private $verantwoordelijkheid;

    /**
     * @var string
     *
     * @ORM\Column(name="telefoonnummer", type="string", length=255, nullable=true)
     */
    private $telefoonnummer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="factuur_nummer", type="string", length=255, nullable=true)
     */
    private $factuurNummer;

    /**
     * @ORM\ManyToOne(targetEntity="Vereniging", inversedBy="user")
     *
     */
    private $vereniging;

    /**
     * @ORM\OneToMany(targetEntity="Jurylid", mappedBy="user", cascade={"persist", "remove"})
     */
    private $jurylid;

    /**
     * @ORM\OneToMany(targetEntity="Turnster", mappedBy="user", cascade={"persist", "remove"})
     */
    private $turnster;

    /**
     * @ORM\OneToMany(targetEntity="Betaling", mappedBy="user", cascade={"persist", "remove"})
     */
    private $betaling;

    public function getAll()
    {
        $user = [
            'id' => $this->id,
            'voornaam' => $this->voornaam,
            'achternaam' => $this->achternaam,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role,
            'verantwoordelijkheid' => ''
        ];

        if ($this->verantwoordelijkheid) {
            $user['verantwoordelijkheid'] = $this->verantwoordelijkheid;
        }
        return $user;
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * Set roles
     *
     * @param string $role
     * @return User
     */
    public function setRoles($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return \Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return array($this->role);
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    public function getUsername()
    {
        return $this->username;
    }
    
    public function getPassword()
    {
        return $this->password;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
            ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = trim($username);

        return $this;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = trim($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set voornaam
     *
     * @param string $voornaam
     * @return User
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
     * @return User
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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set verantwoordelijkheid
     *
     * @param string $verantwoordelijkheid
     * @return User
     */
    public function setVerantwoordelijkheid($verantwoordelijkheid)
    {
        $this->verantwoordelijkheid = $verantwoordelijkheid;

        return $this;
    }

    /**
     * Get verantwoordelijkheid
     *
     * @return string
     */
    public function getVerantwoordelijkheid()
    {
        return $this->verantwoordelijkheid;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jurylid = new ArrayCollection();
        $this->turnster = new ArrayCollection();
        $this->betaling = new ArrayCollection();
    }

    /**
     * Set telefoonnummer
     *
     * @param string $telefoonnummer
     * @return User
     */
    public function setTelefoonnummer($telefoonnummer)
    {
        $this->telefoonnummer = $telefoonnummer;

        return $this;
    }

    /**
     * Get telefoonnummer
     *
     * @return string 
     */
    public function getTelefoonnummer()
    {
        return $this->telefoonnummer;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set factuurNummer
     *
     * @param string $factuurNummer
     * @return User
     */
    public function setFactuurNummer($factuurNummer)
    {
        $this->factuurNummer = $factuurNummer;

        return $this;
    }

    /**
     * Get factuurNummer
     *
     * @return string 
     */
    public function getFactuurNummer()
    {
        return $this->factuurNummer;
    }

    /**
     * Set vereniging
     *
     * @param \AppBundle\Entity\Vereniging $vereniging
     * @return User
     */
    public function setVereniging(\AppBundle\Entity\Vereniging $vereniging = null)
    {
        $this->vereniging = $vereniging;

        return $this;
    }

    /**
     * Get vereniging
     *
     * @return \AppBundle\Entity\Vereniging 
     */
    public function getVereniging()
    {
        return $this->vereniging;
    }

    /**
     * Add jurylid
     *
     * @param \AppBundle\Entity\Jurylid $jurylid
     * @return User
     */
    public function addJurylid(\AppBundle\Entity\Jurylid $jurylid)
    {
        $this->jurylid[] = $jurylid;

        return $this;
    }

    /**
     * Remove jurylid
     *
     * @param \AppBundle\Entity\Jurylid $jurylid
     */
    public function removeJurylid(\AppBundle\Entity\Jurylid $jurylid)
    {
        $this->jurylid->removeElement($jurylid);
    }

    /**
     * Get jurylid
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJurylid()
    {
        return $this->jurylid;
    }

    /**
     * Add turnster
     *
     * @param \AppBundle\Entity\Turnster $turnster
     * @return User
     */
    public function addTurnster(\AppBundle\Entity\Turnster $turnster)
    {
        $this->turnster[] = $turnster;

        return $this;
    }

    /**
     * Remove turnster
     *
     * @param \AppBundle\Entity\Turnster $turnster
     */
    public function removeTurnster(\AppBundle\Entity\Turnster $turnster)
    {
        $this->turnster->removeElement($turnster);
    }

    /**
     * Get turnster
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTurnster()
    {
        return $this->turnster;
    }

    /**
     * Add betaling
     *
     * @param \AppBundle\Entity\Betaling $betaling
     * @return User
     */
    public function addBetaling(\AppBundle\Entity\Betaling $betaling)
    {
        $this->betaling[] = $betaling;

        return $this;
    }

    /**
     * Remove betaling
     *
     * @param \AppBundle\Entity\Betaling $betaling
     */
    public function removeBetaling(\AppBundle\Entity\Betaling $betaling)
    {
        $this->betaling->removeElement($betaling);
    }

    /**
     * Get betaling
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBetaling()
    {
        return $this->betaling;
    }
}
