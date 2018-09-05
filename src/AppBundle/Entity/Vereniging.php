<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="vereniging")
 */
class Vereniging
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(length=256)
     */
    private $naam;

    /**
     * @ORM\Column(length=256)
     */
    private $plaats;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="vereniging", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $user;

    public function getAll()
    {
        return [
            'id' => $this->id,
            'naam' => $this->naam,
            'plaats' => $this->plaats,
        ];
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set naam
     *
     * @param string $naam
     * @return Vereniging
     */
    public function setNaam($naam)
    {
        $this->naam = trim(strtoupper($naam));

        return $this;
    }

    /**
     * Get naam
     *
     * @return string 
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set plaats
     *
     * @param string $plaats
     * @return Vereniging
     */
    public function setPlaats($plaats)
    {
        $this->plaats = trim(strtoupper($plaats));

        return $this;
    }

    /**
     * Get plaats
     *
     * @return string 
     */
    public function getPlaats()
    {
        return $this->plaats;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     * @return Vereniging
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUser()
    {
        return $this->user;
    }
}
