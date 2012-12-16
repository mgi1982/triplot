<?php

namespace Triplot\TriplotBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Triplot\TriplotBundle\Entity\Fotos
 */
class Fotos
{
    /**
     * @var integer $id
     */
    private $id;


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
     * @var string $latitude
     */
    private $latitude;

    /**
     * @var string $longitude
     */
    private $longitude;

    /**
     * @var string $date
     */
    private $date;

    /**
     * @var timestamp $timestamp
     */
    private $timestamp;


    /**
     * Set latitude
     *
     * @param string $latitude
     * @return Fotos
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return Fotos
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return Fotos
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set timestamp
     *
     * @param timestamp $timestamp
     * @return Fotos
     */
    public function setTimestamp(\timestamp $timestamp)
    {
        $this->timestamp = $timestamp;
    
        return $this;
    }

    /**
     * Get timestamp
     *
     * @return timestamp 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}