<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sponsor")
 */
class Sponsor
{
    private $temp;
    private $temp2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(length=300)
     */
    protected $locatie;

    /**
     * @Assert\File(
     *      maxSize="5M",
     *      mimeTypes = {"image/gif", "image/jpeg", "image/pjpeg", "image/png"},
     *      mimeTypesMessage = "Please upload a valid image: gif, jpg or png"
     *      )
     */
    private $file;

    /**
     * @Assert\File(
     *      maxSize="5M",
     *      mimeTypes = {"image/gif", "image/jpeg", "image/pjpeg", "image/png"},
     *      mimeTypesMessage = "Please upload a valid image: gif, jpg or png"
     *      )
     */
    private $file2;

    /**
     * @ORM\Column(length=300)
     */
    protected $locatie2;

    /**
     * @ORM\Column(length=300)
     */
    protected $naam;

    /**
     * @ORM\Column(length=300, nullable=true)
     */
    protected $website;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $omschrijving;


    public function getAll()
    {
        $items = [
            'id' => $this->id,
            'locatie' => $this->locatie,
            'naam' => $this->naam,
            'website' => $this->website,
            'omschrijving' => $this->omschrijving,
            'locatie2' => $this->locatie2,
        ];
        return $items;
    }

    public function getAbsolutePath()
    {
        return null === $this->locatie
            ? null
            : $this->getUploadRootDir().'/'.$this->locatie;
    }

    public function getWebPath()
    {
        return null === $this->locatie
            ? null
            : $this->getUploadDir().'/'.$this->locatie;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/sponsors';
    }

    public function getAbsolutePath2()
    {
        return null === $this->locatie
            ? null
            : $this->getUploadRootDir().'/'.$this->locatie2;
    }

    public function getWebPath2()
    {
        return null === $this->locatie
            ? null
            : $this->getUploadDir().'/'.$this->locatie2;
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
     * Set locatie
     *
     * @param string $locatie
     * @return Sponsor
     */
    public function setLocatie($locatie)
    {
        $this->locatie = $locatie;

        return $this;
    }

    /**
     * Get locatie
     *
     * @return string
     */
    public function getLocatie()
    {
        return $this->locatie;
    }

    /**
     * Set locatie
     *
     * @param string $locatie2
     * @return Sponsor
     */
    public function setLocatie2($locatie2)
    {
        $this->locatie2 = $locatie2;

        return $this;
    }

    /**
     * Get locatie2
     *
     * @return string
     */
    public function getLocatie2()
    {
        return $this->locatie2;
    }

    /**
 * Sets file.
 *
 * @param UploadedFile $file
 */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if (isset($this->locatie)) {
            $this->temp = $this->locatie;
            $this->locatie = null;
        } else {
            $this->locatie = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            $filename = sha1(uniqid(mt_rand(), true));
            $this->locatie = $filename.'.'.$this->getFile()->getClientOriginalExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }
        $this->getFile()->move($this->getUploadRootDir(), $this->locatie);
        if (isset($this->temp)) {
            unlink($this->getUploadRootDir().'/'.$this->temp);
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove()
    {
        $this->temp = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if (isset($this->temp)) {
            unlink($this->temp);
        }
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file2
     */
    public function setFile2(UploadedFile $file2 = null)
    {
        $this->file2 = $file2;
        if (isset($this->locatie2)) {
            $this->temp2 = $this->locatie2;
            $this->locatie2 = null;
        } else {
            $this->locatie2 = 'initial';
        }
    }

    /**
     * Get file2.
     *
     * @return UploadedFile
     */
    public function getFile2()
    {
        return $this->file2;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload2()
    {
        if (null !== $this->getFile2()) {
            $filename = sha1(uniqid(mt_rand(), true));
            $this->locatie2 = $filename.'_groot.'.$this->getFile2()->getClientOriginalExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload2()
    {
        if (null === $this->getFile2()) {
            return;
        }
        $this->getFile2()->move($this->getUploadRootDir(), $this->locatie2);
        if (isset($this->temp2)) {
            unlink($this->getUploadRootDir().'/'.$this->temp2);
            $this->temp2 = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove2()
    {
        $this->temp = $this->getAbsolutePath2();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload2()
    {
        if (isset($this->temp2)) {
            unlink($this->temp2);
        }
    }

    /**
     * Set naam
     *
     * @param string $naam
     * @return Sponsor
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
     * Set website
     *
     * @param string $website
     * @return Sponsor
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set omschrijving
     *
     * @param string $omschrijving
     * @return Sponsor
     */
    public function setOmschrijving($omschrijving)
    {
        $this->omschrijving = $omschrijving;

        return $this;
    }

    /**
     * Get omschrijving
     *
     * @return string 
     */
    public function getOmschrijving()
    {
        return $this->omschrijving;
    }
}
