<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="content", options={ "charset"="utf8mb4", "collate"="utf8mb4_unicode_ci" })
 */
class Content
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $gewijzigd;

    /**
     * @ORM\Column(length=156)
     */
    private $pagina;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * Set gewijzigd
     *
     * @param \DateTime $gewijzigd
     * @return Content
     */
    public function setGewijzigd($gewijzigd)
    {
        $this->gewijzigd = $gewijzigd;

        return $this;
    }

    /**
     * Get gewijzigd
     *
     * @return \DateTime
     */
    public function getGewijzigd()
    {
        return $this->gewijzigd;
    }

    /**
     * Set pagina
     *
     * @param string $pagina
     * @return Content
     */
    public function setPagina($pagina)
    {
        $this->pagina = $pagina;

        return $this;
    }

    /**
     * Get pagina
     *
     * @return string
     */
    public function getPagina()
    {
        return $this->pagina;
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
     * Set content
     *
     * @param string $content
     * @return Content
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
}
