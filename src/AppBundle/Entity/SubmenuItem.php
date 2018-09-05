<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="submenuitems")
 */
class SubmenuItem
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
    private $naam;

    /**
     * @ORM\Column(type="integer")
     */
    private $positie;

    /**
     * @ORM\ManyToOne(targetEntity="HoofdmenuItem", inversedBy="submenuItems")
     * @ORM\JoinColumn(name="hoofdmenuItem_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $hoofdmenuItem;

    public function getAll()
    {
        $submenuItem = [
            'id' => $this->id,
            'naam' => $this->naam,
        ];
        return $submenuItem;
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
     * @return SubmenuItem
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;

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
     * Set positie
     *
     * @param integer $positie
     * @return SubmenuItem
     */
    public function setPositie($positie)
    {
        $this->positie = $positie;

        return $this;
    }

    /**
     * Get positie
     *
     * @return integer 
     */
    public function getPositie()
    {
        return $this->positie;
    }

    /**
     * Set hoofmenuItem
     *
     * @param \AppBundle\Entity\HoofdmenuItem $hoofdmenuItem
     * @return SubmenuItem
     */
    public function setHoofdmenuItem(\AppBundle\Entity\HoofdmenuItem $hoofdmenuItem)
    {
        $this->hoofdmenuItem = $hoofdmenuItem;

        return $this;
    }

    /**
     * Get hoofdmenuItem
     *
     * @return \AppBundle\Entity\HoofdmenuItem 
     */
    public function getHoofdmenuItem()
    {
        return $this->hoofdmenuItem;
    }
}
